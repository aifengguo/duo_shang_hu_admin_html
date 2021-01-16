<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/12
 */

namespace app\models\store;

use app\models\store\StoreCategory as CategoryModel;
use app\models\store\StoreProductAttrValue as StoreProductAttrValueModel;
use app\models\system\SystemUserLevel;
use app\models\user\UserLevel;
use crmeb\basic\BaseModel;
use crmeb\services\workerman\ChannelService;
use crmeb\traits\ModelTrait;
use crmeb\services\GroupDataService;
use think\facade\Config;

/**
 * TODO 商品Model
 * Class StoreProduct
 * @package app\models\store
 */
class StoreProduct extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product';

    use  ModelTrait;

//    protected function getSliderImageAttr($value)
//    {
//        $sliderImage = json_decode($value, true) ?: [];
//        foreach ($sliderImage as &$item) {
//            $item = str_replace('\\', '/', $item);
//        }
//        return $sliderImage;
//    }

    protected function getImageAttr($value)
    {
        return str_replace('\\', '/', $value);
    }

    public static function getValidProduct($productId, $field = 'add_time,browse,cate_id,code_path,cost,ficti,give_integral,id,image,is_sub,is_bargain,is_benefit,is_best,is_del,is_hot,is_new,is_postage,is_seckill,is_show,keyword,mer_id,mer_use,ot_price,postage,price,sales,slider_image,sort,stock,store_info,store_name,unit_name,vip_price,spec_type,IFNULL(sales,0) + IFNULL(ficti,0) as fsales,video_link')
    {
        $Product = self::where('is_del', 0)->where('is_show', 1)->where('id', $productId)->field($field)->find();
        if ($Product) return $Product->toArray();
        else return false;
    }

    public static function getGoodList($limit = 18, $field = '*')
    {
        $list = self::validWhere()->where('is_good', 1)->order('sort desc,id desc')->limit($limit)->field($field)->select();
        $list = count($list) ? $list->toArray() : [];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list[$k]['activity'] = self::activity($v['id']);
                $list[$k]['checkCoupon'] = self::checkCoupon($v['id']);
            }
        }
        return $list;
    }

    public static function validWhere()
    {
        return self::where('is_del', 0)->where('is_show', 1)->where('mer_id', 0);
    }

    public static function getProductList($data, $uid)
    {
        $sId = $data['sid'];
        $cId = $data['cid'];
        $keyword = $data['keyword'];
        $priceOrder = $data['priceOrder'];
        $salesOrder = $data['salesOrder'];
        $news = $data['news'];
        $page = $data['page'];
        $limit = $data['limit'];
        $type = $data['type']; // 某些模板需要购物车数量 1 = 需要查询，0 = 不需要
        $model = self::validWhere();
        if ($sId) {
            $model->whereIn('id', function ($query) use ($sId) {
                $query->name('store_product_cate')->where('cate_id', $sId)->field('product_id')->select();
            });
        } elseif ($cId) {
            $model->whereIn('id', function ($query) use ($cId) {
                $query->name('store_product_cate')->whereIn('cate_id', function ($q) use ($cId) {
                    $q->name('store_category')->where('pid', $cId)->field('id')->select();
                })->field('product_id')->select();
            });
        }
        if (!empty($keyword)) $model->where('keyword|store_name', 'LIKE', htmlspecialchars("%$keyword%"));
        if ($news != 0) $model->where('is_new', 1);
        $baseOrder = '';
        if ($priceOrder) $baseOrder = $priceOrder == 'desc' ? 'price DESC' : 'price ASC';
//        if($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//真实销量
        if ($salesOrder) $baseOrder = $salesOrder == 'desc' ? 'sales DESC' : 'sales ASC';//虚拟销量
        if ($baseOrder) $baseOrder .= ', ';
        $model->order($baseOrder . 'sort DESC, add_time DESC');
        $list = $model->page((int)$page, (int)$limit)->field('id,store_name,cate_id,image,IFNULL(sales,0) + IFNULL(ficti,0) as sales,price,stock')->select()->each(function ($item) use ($uid, $type) {
            if ($type) {
                $item['is_att'] = StoreProductAttrValueModel::where('product_id', $item['id'])->count() ? true : false;
                if ($uid) $item['cart_num'] = StoreCart::where('is_pay', 0)->where('is_del', 0)->where('is_new', 0)->where('type', 'product')->where('product_id', $item['id'])->where('uid', $uid)->value('cart_num');
                else $item['cart_num'] = 0;
                if (is_null($item['cart_num'])) $item['cart_num'] = 0;
            }
        });
        $list = count($list) ? $list->toArray() : [];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list[$k]['activity'] = self::activity($v['id']);
                $list[$k]['checkCoupon'] = self::checkCoupon($v['id']);
            }
        }
        return self::setLevelPrice($list, $uid);
    }

    /*
     * 分类搜索
     * @param string $value
     * @return array
     * */
    public static function getSearchStorePage($keyword, $page, $limit, $uid, $cutApart = [' ', ',', '-'])
    {
        $model = self::validWhere();
        $keyword = trim($keyword);
        if (strlen($keyword)) {
            $cut = false;
            foreach ($cutApart as $val) {
                if (strstr($keyword, $val) !== false) {
                    $cut = $val;
                    break;
                }
            }
            if ($cut !== false) {
                $keywordArray = explode($cut, $keyword);
                $sql = [];
                foreach ($keywordArray as $item) {
                    $sql[] = '(`store_name` LIKE "%' . $item . '%"  OR `keyword` LIKE "%' . $item . '%")';
                }
                $model = $model->where(implode(' OR ', $sql));
            } else {
                $model = $model->where('store_name|keyword', 'LIKE', "%$keyword%");
            }
        }
        $list = $model->field('id,store_name,cate_id,image,ficti as sales,price,stock')->page($page, $limit)->select();
        return self::setLevelPrice($list, $uid);
    }

    /**
     * 新品商品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getNewProduct($field = '*', $limit = 0, $uid = 0, bool $bool = true, $page = 0, $limits = 0)
    {
        if (!$limit && !$bool) return [];
        $model = self::where('is_new', 1)->where('is_del', 0)->where('mer_id', 0)
            ->where('stock', '>', 0)->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($limit) $model->limit($limit);
        if ($page) $model->page((int)$page, (int)$limits);
        $list = $model->select();
        $list = count($list) ? $list->toArray() : [];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list[$k]['activity'] = self::activity($v['id']);
                $list[$k]['checkCoupon'] = self::checkCoupon($v['id']);
            }
        }
        return self::setLevelPrice($list, $uid);
    }

    /**
     * 热卖商品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getHotProduct($field = '*', $limit = 0, $uid = 0, $page = 0, $limits = 0)
    {
        $model = self::where('is_hot', 1)->where('is_del', 0)->where('mer_id', 0)
            ->where('stock', '>', 0)->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($limit) $model->limit($limit);
        if ($page) $model->page((int)$page, (int)$limits);
        $list = $model->select();
        $list = count($list) ? $list->toArray() : [];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list[$k]['activity'] = self::activity($v['id']);
                $list[$k]['checkCoupon'] = self::checkCoupon($v['id']);
            }
        }
        return self::setLevelPrice($list, $uid);
    }

    /**
     * 热卖商品
     * @param string $field
     * @param int $page
     * @param int $limit
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getHotProductLoading($field = '*', $page = 0, $limit = 0)
    {
        if (!$limit) return [];
        $model = self::where('is_hot', 1)->where('is_del', 0)->where('mer_id', 0)
            ->where('stock', '>', 0)->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($page) $model->page($page, $limit);
        $list = $model->select();
        if (is_object($list)) return $list->toArray();
        return $list;
    }

    /**
     * 精品商品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBestProduct($field = '*', $limit = 0, $uid = 0, bool $bool = true, $page = 0, $limits = 0)
    {
        if (!$limit && !$bool) return [];
        $model = self::where('is_best', 1)->where('is_del', 0)->where('mer_id', 0)
            ->where('stock', '>', 0)->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($limit) $model->limit($limit);
        if ($page) $model->page((int)$page, (int)$limits);
        $list = $model->select();
        $list = count($list) ? $list->toArray() : [];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list[$k]['activity'] = self::activity($v['id']);
                $list[$k]['checkCoupon'] = self::checkCoupon($v['id']);
            }
        }
        return self::setLevelPrice($list, $uid);
    }

    /**
     * 设置会员价格
     * @param object | array $list 商品列表
     * @param int $uid 用户uid
     * @return array
     * */
    public static function setLevelPrice($list, $uid, $isSingle = false)
    {
        if (is_object($list)) $list = count($list) ? $list->toArray() : [];
        if (!sys_config('vip_open')) {
            if (is_array($list)) return $list;
            return $isSingle ? $list : 0;
        }
        $levelId = UserLevel::getUserLevel($uid);
        if ($levelId) {
            $discount = UserLevel::getUserLevelInfo($levelId, 'discount');
            $discount = bcsub(1, bcdiv($discount, 100, 2), 2);
        } else {
            $discount = SystemUserLevel::getLevelDiscount();
            $discount = bcsub(1, bcdiv($discount, 100, 2), 2);
        }
        //如果不是数组直接执行减去会员优惠金额
        if (!is_array($list))
            //不是会员原价返回
            if ($levelId)
                //如果$isSingle==true 返回优惠后的总金额，否则返回优惠的金额
                return $isSingle ? bcsub($list, bcmul($discount, $list, 2), 2) : bcmul($discount, $list, 2);
            else
                return $isSingle ? $list : 0;
        //当$list为数组时$isSingle==true为一维数组 ，否则为二维
        if ($isSingle)
            $list['vip_price'] = isset($list['price']) ? bcsub($list['price'], bcmul($discount, $list['price'], 2), 2) : 0;
        else
            foreach ($list as &$item) {
                $item['vip_price'] = isset($item['price']) ? bcsub($item['price'], bcmul($discount, $item['price'], 2), 2) : 0;
            }
        return $list;
    }


    /**
     * 优惠商品
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBenefitProduct($field = '*', $limit = 0, $page = 0, $limits = 0)
    {
        if (!$limits) {
            return [];
        }
        $model = self::where('is_benefit', 1)
            ->where('is_del', 0)->where('mer_id', 0)->where('stock', '>', 0)
            ->where('is_show', 1)->field($field)
            ->order('sort DESC, id DESC');
        if ($limit) $model->limit($limit);
        if ($page) $model->page((int)$page, (int)$limits);
        $data = $model->select();
        if (count($data) > 0) {
            foreach ($data as $k => $v) {
                $data[$k]['activity'] = self::activity($v['id']);
                $data[$k]['checkCoupon'] = self::checkCoupon($v['id']);
            }
        }
        return $data;
    }

    public static function cateIdBySimilarityProduct($cateId, $field = '*', $limit = 0)
    {
        $pid = StoreCategory::cateIdByPid($cateId) ?: $cateId;
        $cateList = StoreCategory::pidByCategory($pid, 'id') ?: [];
        $cid = [$pid];
        foreach ($cateList as $cate) {
            $cid[] = $cate['id'];
        }
        $model = self::where('cate_id', 'IN', $cid)->where('is_show', 1)->where('is_del', 0)
            ->field($field)->order('sort DESC,id DESC');
        if ($limit) $model->limit($limit);
        return $model->select();
    }

    public static function isValidProduct($productId)
    {
        return self::be(['id' => $productId, 'is_del' => 0, 'is_show' => 1]) > 0;
    }

    public static function getProductStock($productId, $uniqueId = '')
    {
        return $uniqueId == '' ?
            self::where('id', $productId)->value('stock') ?: 0
            : StoreProductAttr::uniqueByStock($uniqueId);
    }

    public static function decProductStock($num, $productId, $unique = '')
    {
        if ($unique) {
            $res = false !== StoreProductAttrValueModel::decProductAttrStock($productId, $unique, $num, 0);
            $res = $res && self::where('id', $productId)->dec('stock', $num)->inc('sales', $num)->update();
        } else {
            $res = false !== self::where('id', $productId)->dec('stock', $num)->inc('sales', $num)->update();
        }
        if ($res) {
            $stock = self::where('id', $productId)->value('stock');
            $replenishment_num = sys_config('store_stock') ?? 0;//库存预警界限
            if ($replenishment_num >= $stock) {
                try {
                    ChannelService::instance()->send('STORE_STOCK', ['id' => $productId]);
                } catch (\Exception $e) {
                }
            }
        }
        return $res;
    }

    /*
     * 减少销量,增加库存
     * @param int $num 增加库存数量
     * @param int $productId 商品id
     * @param string $unique 属性唯一值
     * @return boolean
     * */
    public static function incProductStock($num, $productId, $unique = '')
    {
        $product = self::where('id', $productId)->field(['sales', 'stock'])->find();
        if (!$product) return true;
        if ($product->sales > 0) $product->sales = bcsub($product->sales, $num, 0);
        if ($product->sales < 0) $product->sales = 0;
        if ($unique) {
            $res = false !== StoreProductAttrValueModel::incProductAttrStock($productId, $unique, $num);
            //没有修改销量则直接返回
            if ($product->sales == 0) return true;
            $res = $res && $product->save();
        } else {
            $product->stock = bcadd($product->stock, $num, 0);
            $res = false !== $product->save();
        }
        return $res;
    }

    /**
     * 获取商品分销佣金最低和最高
     * @param $storeInfo
     * @param $productValue
     * @return int|string
     */
    public static function getPacketPrice($storeInfo, $productValue)
    {
        $store_brokerage_ratio = sys_config('store_brokerage_ratio');
        $store_brokerage_ratio = bcdiv($store_brokerage_ratio, 100, 2);
        if (isset($storeInfo['is_sub']) && $storeInfo['is_sub'] == 1) {
            $Maxkey = self::getArrayMax($productValue, 'brokerage');
            $Minkey = self::getArrayMin($productValue, 'brokerage');
            $maxPrice = bcadd(isset($productValue[$Maxkey]) ? $productValue[$Maxkey]['brokerage'] : 0, 0, 0);
            $minPrice = bcadd(isset($productValue[$Minkey]) ? $productValue[$Minkey]['brokerage'] : 0, 0, 0);
        } else {
            $Maxkey = self::getArrayMax($productValue, 'price');
            $Minkey = self::getArrayMin($productValue, 'price');
            $maxPrice = bcmul($store_brokerage_ratio, bcadd(isset($productValue[$Maxkey]) ? $productValue[$Maxkey]['price'] : 0, 0, 0), 0);
            $minPrice = bcmul($store_brokerage_ratio, bcadd(isset($productValue[$Minkey]) ? $productValue[$Minkey]['price'] : 0, 0, 0), 0);
        }
        if ($minPrice == 0 && $maxPrice == 0)
            return 0;
        else if ($minPrice == 0 && $maxPrice)
            return $maxPrice;
        else if ($maxPrice == 0 && $minPrice)
            return $minPrice;
        else if ($maxPrice == $minPrice && $minPrice)
            return $maxPrice;
        else
            return $minPrice . '~' . $maxPrice;
    }

    /**
     * 获取二维数组中最大的值
     * @param $arr
     * @param $field
     * @return int|string
     */
    public static function getArrayMax($arr, $field)
    {
        $temp = [];
        foreach ($arr as $k => $v) {
            $temp[] = $v[$field];
        }
        if (!count($temp)) return 0;
        $maxNumber = max($temp);
        foreach ($arr as $k => $v) {
            if ($maxNumber == $v[$field]) return $k;
        }
        return 0;
    }

    /**
     * 获取二维数组中最小的值
     * @param $arr
     * @param $field
     * @return int|string
     */
    public static function getArrayMin($arr, $field)
    {
        $temp = [];
        foreach ($arr as $k => $v) {
            $temp[] = $v[$field];
        }
        if (!count($temp)) return 0;
        $minNumber = min($temp);
        foreach ($arr as $k => $v) {
            if ($minNumber == $v[$field]) return $k;
        }
        return 0;
    }

    /**
     * 商品名称 图片
     * @param array $productIds
     * @return array
     */
    public static function getProductStoreNameOrImage(array $productIds)
    {
        return self::whereIn('id', $productIds)->column('store_name,image', 'id');
    }

    /**
     * TODO 获取某个字段值
     * @param $id
     * @param string $field
     * @return mixed
     */
    public static function getProductField($id, $field = 'store_name')
    {
        if (is_array($id))
            return self::where('id', 'in', $id)->field($field)->select();
        else
            return self::where('id', $id)->value($field);
    }

    /*
     * 获取商品列表
     * @param $where array
     * @return array
     *
     */
    public static function ProductList($where)
    {
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        $model = self::getModelObject($where)->field([
            '*',
            '(SELECT count(*) FROM `' . $prefix . 'store_product_relation` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = \'collect\') as collect',
            '(SELECT count(*) FROM `' . $prefix . 'store_product_relation` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = \'like\') as likes',
            '(SELECT SUM(stock) FROM `' . $prefix . 'store_product_attr_value` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = 0) as stork',
            '(SELECT SUM(sales) FROM `' . $prefix . 'store_product_attr_value` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = 0) as sales',
            '(SELECT count(*) FROM `' . $prefix . 'store_visit` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `product_type` = \'product\') as visitor',
        ]);
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item) {
            $cateName = CategoryModel::alias('c')->join('StoreCategory b', 'b.id = c.pid')->where('c.id', 'IN', $item['cate_id'])->column('c.cate_name as two,b.cate_name as one', 'c.id');
            $item['cate_name'] = [];
            foreach ($cateName as $k => $v) {
                $item['cate_name'][] = $v['one'] . '/' . $v['two'];
            }
            $item['cate_name'] = is_array($item['cate_name']) ? implode(',', $item['cate_name']) : '';
            $item['stock_attr'] = $item['stock'] > 0 ? true : false;//库存
        }
        unset($item);
        $count = self::getModelObject($where)->count();
        return ['count' => $count, 'list' => $data];
    }

    /*
    * 获取导出产品数据
    * @param $where array
    * @return array
    */
    public static function exportData($where)
    {
        $model = self::getModelObject($where);
        if ($where['excel'] == 0) $model = $model->page((int)$where['page'], (int)$where['limit']);
        $data = ($data = $model->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item) {
            $cateName = CategoryModel::where('id', 'IN', $item['cate_id'])->column('cate_name', 'id');
            $item['cate_name'] = is_array($cateName) ? implode(',', $cateName) : '';
            $item['collect'] = StoreProductRelation::where('product_id', $item['id'])->where('type', 'collect')->count();//收藏
            $item['like'] = StoreProductRelation::where('product_id', $item['id'])->where('type', 'like')->count();//点赞
            $item['stock'] = self::getStock($item['id']) > 0 ? self::getStock($item['id']) : $item['stock'];//库存
            $item['stock_attr'] = self::getStock($item['id']) > 0 ? true : false;//库存
            $item['sales_attr'] = self::getSales($item['id']);//属性销量
            $item['visitor'] = StoreVisit::where('product_id', $item['id'])->where('product_type', 'product')->count();
        }
        unset($item);
        return $data;
    }

    /**
     * 获取连表MOdel
     * @param $model
     * @return object
     */
    public static function getModelObject($where = [])
    {
        $model = new self();
        if (!empty($where)) {
            $type = $where['type'] ?? 0;
            switch ((int)$type) {
                case 1:
                    $model = $model->where(['is_show' => 1, 'is_del' => 0]);
                    break;
                case 2:
                    $model = $model->where(['is_show' => 0, 'is_del' => 0]);
                    break;
                case 3:
                    $model = $model->where(['is_del' => 0]);
                    break;
                case 4:
                    $model = $model->where(['is_del' => 0])->whereIn('id', function ($query) {
                        $query->name('store_product_attr_value')->where('stock', 0)->where('type', 0)->field('product_id')->select();
                    })->where(function ($query) {
                        $query->whereOr('stock', 0);
                    });
                    break;
                case 5:
                    $store_stock = sys_config('store_stock');
                    if ($store_stock < 0) $store_stock = 2;
                    $model = $model->where(['is_show' => 1, 'is_del' => 0])->where('stock', '<=', $store_stock)->where('stock', '>', 0);
                    break;
                case 6:
                    $model = $model->where(['is_del' => 1]);
                    break;
            };
            if (isset($where['store_name']) && $where['store_name'] != '') {
                $model = $model->where('store_name|keyword|id', 'LIKE', "%$where[store_name]%");
            }
            if (isset($where['cate_id']) && trim($where['cate_id']) != '') {
                $model = $model->whereIn('id', function ($query) use ($where) {
                    $query->name('store_product_cate')->where('cate_id', $where['cate_id'])->field('product_id')->select();
                });
            }
            if (isset($where['order']) && $where['order'] != '') {
                $model = $model->order(self::setOrder($where['order']));
            } else {
                $model = $model->order('sort desc,id desc');
            }
        }
        return $model;
    }

    /**
     * 获取连表查询条件
     * @param $type
     * @return array
     */
    public static function setData($type)
    {
        switch ((int)$type) {
            case 1:
                $data = ['p.is_show' => 1, 'p.is_del' => 0];
                break;
            case 2:
                $data = ['p.is_show' => 0, 'p.is_del' => 0];
                break;
            case 3:
                $data = ['p.is_del' => 0];
                break;
            case 4:
                $data = ['p.is_show' => 1, 'p.is_del' => 0, 'p.stock' => 0];
                break;
            case 5:
                $min = sys_config('store_stock');
                $min = $min ? $min : 2;
                $data = ['p.is_show' => 1, 'p.is_del' => 0, 'p.stock' => ['<=', $min]];
                break;
            case 6:
                $data = ['p.is_del' => 1];
                break;
        };
        return isset($data) ? $data : [];
    }

    /** 如果有子分类查询子分类获取拼接查询sql
     * @param $cateid
     * @return string
     */
    protected static function getPidSql($cateid)
    {
        $sql = self::getCateSql($cateid);
        $ids = StoreCategory::where('pid', $cateid)->column('id', 'id');
        //查询如果有子分类获取子分类查询sql语句
        if ($ids) foreach ($ids as $v) $sql .= " OR " . self::getcatesql($v);
        return $sql;
    }

    /**根据cateid查询商品 拼sql语句
     * @param $cateid
     * @return string
     */
    protected static function getCateSql($cateid)
    {
        $lcateid = $cateid . ',%';//匹配最前面的cateid
        $ccatid = '%,' . $cateid . ',%';//匹配中间的cateid
        $ratidid = '%,' . $cateid;//匹配后面的cateid
        return " `cate_id` LIKE '$lcateid' OR `cate_id` LIKE '$ccatid' OR `cate_id` LIKE '$ratidid' OR `cate_id`=$cateid";
    }

    //获取库存数量
    public static function getStock($productId, $type = 0)
    {
        return StoreProductAttrValue::where(['product_id' => $productId])->where('type', $type)->sum('stock');
    }

    //获取总销量
    public static function getSales($productId)
    {
        return StoreProductAttrValue::where(['product_id' => $productId])->sum('sales');
    }

    //获取列表
    public static function getList($where)
    {
        $model = new self;
        if ($where['cate_id']) $model = $model->whereIn('cate_id', $where['cate_id']);
        if ($where['store_name']) $model = $model->where('store_name', 'LIKE', "%" . $where['store_name'] . "%");
        $count = $model->where('is_show', 1)->where('is_del', 0)->count();
        $list = $model->where('is_show', 1)->where('is_del', 0)->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                $cate_name = CategoryModel::whereIn('id', $item['cate_id'])->column('cate_name');
                $item['cate_name'] = implode(',', array_values($cate_name));
                $item['give_integral'] = floatval($item['give_integral']);
                $item['price'] = floatval($item['price']);
                $item['vip_price'] = floatval($item['vip_price']);
                $item['ot_price'] = floatval($item['ot_price']);
                $item['postage'] = floatval($item['postage']);
                $item['cost'] = floatval($item['cost']);
                $item['slider_image'] = json_decode($item['slider_image'], true) ?: [];
                $item['description'] = StoreDescription::getDescription($item['id'], 0);
            });
        return compact('count', 'list');
    }

    /**
     * 添加活动商品时获取商品属性
     * @param $id
     * @param int $type 0正常商品1秒杀2砍价3拼团，获取列表格式不同
     * @return array
     */
    public static function getAttrs($id, $type = 0)
    {
        $spec = StoreProductAttrValue::activityRules($id, $type);
        $info['items'] = $spec['attr'];
        $info['attrs'] = $spec['value'];
        $info['header'] = $spec['header'];
        return compact('info');
    }

    /**
     * 获取商品在此时段活动优先类型
     */
    public static function activity($id, $status = true)
    {
        $activity = self::where('id', $id)->value('activity');
        if (!$activity) $activity = '1,2,3';//如果老商品没有活动顺序，默认活动顺序，秒杀-砍价-拼团
        $activity = explode(',', $activity);
        $activityId = [];
        $time = 0;
        $seckillId = StoreSeckill::where('is_del', 0)->where('status', 1)->where('start_time', '<=', time())->where('stop_time', '>=', time() - 86400)->where('product_id', $id)->field('id,time_id')->select();
        if ($seckillId) {
            foreach ($seckillId as $v) {
                $timeInfo = GroupDataService::getDataNumber((int)$v['time_id']);
                if ($timeInfo && isset($timeInfo['time']) && isset($timeInfo['continued'])) {
                    if (date('H') >= $timeInfo['time'] && date('H') < ($timeInfo['time'] + $timeInfo['continued'])) {
                        $activityId[1] = $v['id'];
                        $time = strtotime(date("Y-m-d"), time()) + 3600 * ($timeInfo['time'] + $timeInfo['continued']);
                    }
                }
            }
        }
        $bargainId = StoreBargain::where('is_del', 0)->where('status', 1)->where('start_time', '<=', time())->where('stop_time', '>=', time())->where('product_id', $id)->value('id');
        if ($bargainId) $activityId[2] = $bargainId;
        $combinationId = StoreCombination::where('is_del', 0)->where('is_show', 1)->where('start_time', '<=', time())->where('stop_time', '>=', time())->where('product_id', $id)->value('id');
        if ($combinationId) $activityId[3] = $combinationId;
        $data = [];
        foreach ($activity as $k => $v) {
            if (array_key_exists($v, $activityId)) {
                if ($status) {
                    $data['type'] = $v;
                    $data['id'] = $activityId[$v];
                    if ($v == 1) $data['time'] = $time;
                    break;
                } else {
                    $arr['type'] = $v;
                    $arr['id'] = $activityId[$v];
                    if ($v == 1) $arr['time'] = $time;
                    $data[] = $arr;
                }
            }
        }
        return $data;
    }

    /**
     * 判断商品是否有商品优惠券
     * @param $id
     * @return bool
     */
    public static function checkCoupon($id)
    {
        $res = StoreCoupon::alias('c')->join('store_coupon_issue i', 'i.cid=c.id')
            ->whereFindinSet('c.product_id', $id)
            ->where('c.is_del', 0)
            ->where('c.type', 2)
            ->where('i.status', 1)
            ->where('i.is_del', 0)
            ->where(function ($query) {
                $query->where('is_permanent', 1)->whereOr('remain_count', '>', 0);
            })->find();
        if ($res) return true;
        return false;
    }

    /**
     * 获取产品返佣金额
     * @param array $cartId
     * @param bool $type true = 一级返佣, fasle = 二级返佣
     * @return int|string
     */
    public static function getProductBrokerage(array $cartId, bool $type = true)
    {
        $cartInfo = StoreOrderCartInfo::whereIn('cart_id', $cartId)->column('cart_info');
        $oneBrokerage = 0;//一级返佣金额
        $twoBrokerage = 0;//二级返佣金额
        $sumProductPrice = 0;//非指定返佣商品总金额
        foreach ($cartInfo as $value) {
            $product = json_decode($value, true);
            $cartNum = $product['cart_num'] ?? 0;
            if (isset($product['productInfo'])) {
                $productInfo = $product['productInfo'];
                //指定返佣金额
                if (isset($productInfo['is_sub']) && $productInfo['is_sub'] == 1) {
                    $oneBrokerage = bcadd($oneBrokerage, bcmul($cartNum, $productInfo['attrInfo']['brokerage'] ?? 0, 2), 2);
                    $twoBrokerage = bcadd($twoBrokerage, bcmul($cartNum, $productInfo['attrInfo']['brokerage_two'] ?? 0, 2), 2);
                } else {
                    //比例返佣
                    if (isset($productInfo['attrInfo'])) {
                        $sumProductPrice = bcadd($sumProductPrice, bcmul($cartNum, $productInfo['attrInfo']['price'] ?? 0, 2), 2);
                    } else {
                        $sumProductPrice = bcadd($sumProductPrice, bcmul($cartNum, $productInfo['price'] ?? 0, 2), 2);
                    }
                }
            }
        }
        if ($type) {
            //获取后台一级返佣比例
            $storeBrokerageRatio = sys_config('store_brokerage_ratio');
            //一级返佣比例 小于等于零时直接返回 不返佣
            if ($storeBrokerageRatio <= 0) {
                return $oneBrokerage;
            }
            //计算获取一级返佣比例
            $brokerageRatio = bcdiv($storeBrokerageRatio, 100, 2);
            $brokeragePrice = bcmul($sumProductPrice, $brokerageRatio, 2);
            //固定返佣 + 比例返佣 = 一级总返佣金额
            return bcadd($oneBrokerage, $brokeragePrice, 2);
        } else {
            //获取二级返佣比例
            $storeBrokerageTwo = sys_config('store_brokerage_two');
            //二级返佣比例小于等于0 直接返回
            if ($storeBrokerageTwo <= 0) {
                return $twoBrokerage;
            }
            //计算获取二级返佣比例
            $brokerageRatio = bcdiv($storeBrokerageTwo, 100, 2);
            $brokeragePrice = bcmul($sumProductPrice, $brokerageRatio, 2);
            //固定返佣 + 比例返佣 = 二级总返佣金额
            return bcadd($twoBrokerage, $brokeragePrice, 2);
        }

    }

}