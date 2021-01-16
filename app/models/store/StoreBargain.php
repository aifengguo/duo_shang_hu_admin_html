<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace app\models\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 砍价商品Model
 * Class StoreBargain
 * @package app\models\store
 */
class StoreBargain extends BaseModel
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
    protected $name = 'store_bargain';

    use ModelTrait;

    protected function getAddTimeAttr($value)
    {
        if ($value) return date('Y-m-d H:i:s', $value);
        return '';
    }

    /**
     * 正在开启的砍价活动
     * @param int $status
     * @return StoreBargain
     */
    public static function validWhere($status = 1)
    {
        return self::where('is_del', 0)->where('status', $status)->where('start_time', '<', time())->where('stop_time', '>', time());
    }

    /**
     * 判断砍价商品是否开启
     * @param int $bargainId
     * @return int|string
     */
    public static function validBargain($bargainId = 0)
    {
        $model = self::validWhere();
        return $bargainId ? $model->where('id', $bargainId)->count('id') : $model->count('id');
    }

    /**
     * TODO 获取正在开启的砍价商品编号
     * @return array
     */
    public static function validBargainNumber()
    {
        return self::validWhere()->column('id');
    }

    /**
     * 获取正在进行中的砍价商品
     * @param int $page
     * @param int $limit
     * @param string $field
     * @return array
     */
    public static function getList($page = 0, $limit = 20, $field = 'id,product_id,title,price,min_price,image')
    {
        $model = self::validWhere()->field($field);
        if ($page) $model = $model->page($page, $limit);
        $list = $model->select()->each(function ($item) {
            $item['people'] = count(StoreBargainUser::getUserIdList($item['id']));
            $item['price'] = floatval($item['price']);
        });
        return $list ? $list->toArray() : [];
    }

    /**
     * TODO 获取一条正在进行中的砍价商品
     * @param int $bargainId $bargainId 砍价商品编号
     * @param string $field
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getBargainTerm($bargainId = 0, $field = 'id,product_id,bargain_num,num,unit_name,image,title,price,min_price,image,start_time,stop_time,rule,info')
    {
        if (!$bargainId) return [];
        $model = self::validWhere();
        $bargain = $model->field($field)->where('id', $bargainId)->find();
        if ($bargain) return $bargain->toArray();
        else return [];
    }

    /**
     * 获取一条砍价商品
     * @param int $bargainId
     * @param string $field
     * @return array
     */
    public static function getBargain($bargainId = 0, $field = 'id,product_id,title,price,min_price,image')
    {
        if (!$bargainId) return [];
        $model = new self();
        $bargain = $model->field($field)->where('id', $bargainId)->find();
        if ($bargain) return $bargain->toArray();
        else return [];
    }

    /**
     * 获取最高价和最低价
     * @param int $bargainId
     * @return array
     */
    public static function getBargainMaxMinPrice($bargainId = 0)
    {
        if (!$bargainId) return [];
        return self::where('id', $bargainId)->field('bargain_min_price,bargain_max_price')->find()->toArray();
    }

    /**
     * 获取砍价次数
     * @param int $bargainId
     * @return mixed
     */
    public static function getBargainNum($bargainId = 0)
    {
        return self::where('id', $bargainId)->value('bargain_num');
    }

    /**
     * 判断当前砍价是否活动进行中
     * @param int $bargainId
     * @return bool
     */
    public static function setBargainStatus($bargainId = 0)
    {
        $model = self::validWhere();
        $count = $model->where('id', $bargainId)->count();
        if ($count) return true;
        else return false;
    }

    /**
     * 获取库存
     * @param int $bargainId
     * @return mixed
     */
    public static function getBargainStock($bargainId = 0)
    {
        return self::where('id', $bargainId)->value('stock');
    }

    /**
     * 获取字段值
     * @param $bargainId
     * @param string $field
     * @return mixed
     */
    public static function getBargainField($bargainId, $field = 'title')
    {
        return self::where('id', $bargainId)->value($field);
    }

    /**
     * 修改销量和库存
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function decBargainStock($num, $bargainId, $unique)
    {
        $product_id = self::where('id',$bargainId)->value('product_id');
        if ($unique) {
            $res = false !== StoreProductAttrValue::decProductAttrStock($bargainId, $unique, $num, 2);
            $res = $res && self::where('id', $bargainId)->dec('stock', $num)->inc('sales', $num)->update();
            $sku = StoreProductAttrValue::where('product_id',$bargainId)->where('unique',$unique)->where('type',2)->value('suk');
            $res = $res && StoreProductAttrValue::where('product_id',$product_id)->where('suk',$sku)->where('type',0)->dec('stock',$num)->inc('sales',$num)->update();
        } else {
            $res = false !== self::where('id', $bargainId)->dec('stock', $num)->inc('sales', $num)->update();
        }
        $res = $res && StoreProduct::where('id',$product_id)->dec('stock', $num)->inc('sales', $num)->update();
        return $res;
    }

    /**
     * TODO 增加库存减销量
     * @param $num
     * @param $bargainId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function IncBargainStock($num, $bargainId)
    {
        $bargain = self::where('id', $bargainId)->field(['stock', 'sales'])->find();
        if (!$bargain) return true;
        if ($bargain->sales > 0) $bargain->sales = bcsub($bargain->sales, $num, 0);
        if ($bargain->sales < 0) $bargain->sales = 0;
        $bargain->stock = bcadd($bargain->stock, $num, 0);
        return $bargain->save();
    }

    /**
     * TODO 获取所有砍价商品的浏览量
     * @return mixed
     */
    public static function getBargainLook()
    {
        return self::sum('look');
    }

    /**
     * TODO 获取正在开启的砍价活动
     * @return int|string
     */
    public static function getListCount()
    {
        return self::validWhere()->count();
    }

    /**
     * TODO 获取所有砍价商品的分享量
     * @return mixed
     */
    public static function getBargainShare()
    {
        return self::sum('share');
    }

    /**
     * TODO 添加砍价商品分享次数
     * @param int $id
     * @return StoreBargain|bool
     */
    public static function addBargainShare($id = 0)
    {
        if (!$id) return false;
        return self::where('id', $id)->inc('share', 1)->update();
    }

    /**
     * TODO 添加砍价商品浏览次数
     * @param int $id $id 砍价商品编号
     * @return StoreBargain|bool
     */
    public static function addBargainLook($id = 0)
    {
        if (!$id) return false;
        return self::where('id', $id)->inc('look', 1)->update();
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        $model = self::isWhere($where, $model);
        $model = $model->order('id desc');
        $model = $model->where('is_del', 0);
        $model = self::getModelTime($where, $model, "add_time");
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                if ($item['status']) {
                    if ($item['start_time'] > time())
                        $item['start_name'] = '活动未开始';
                    else if ($item['stop_time'] < time())
                        $item['start_name'] = '活动已结束';
                    else if ($item['stop_time'] > time() && $item['start_time'] < time())
                        $item['start_name'] = '正在进行中';
                }
                $item['count_people_all'] = StoreBargainUser::getCountPeopleAll($item['id']);//参与人数
                $item['count_people_help'] = StoreBargainUserHelp::getCountPeopleHelp($item['id']);//帮忙砍价人数
                $item['count_people_success'] = StoreBargainUser::getCountPeopleAll($item['id'], 3);//砍价成功人数
            });

        return compact('count', 'list');
    }
    /**
     * 查出导出数据
     * @param $where
     * @return array
     */
    public static function exportData($where)
    {
        $model = new self;
        $model = self::isWhere($where, $model);
        $model = $model->order('id desc');
        $model = $model->where('is_del', 0);
        $model = self::getModelTime($where, $model, "add_time");
        $list = $model->select()->toArray();
        return $list;
    }
    public static function isWhere($where = array(), $model = self::class)
    {
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        if ($where['store_name'] != '') $model = $model->where('id|title', 'LIKE', "%$where[store_name]%");
//        if($where['data'] != '') $model = $model->whereTime('add_time', 'between', explode('-',$where['data']));
        return $model;
    }

    /**
     * 详情
     */
    public static function getOne($id)
    {
        $info = self::get($id);
        if ($info) {
            if ($info['start_time'])
                $start_time = date('Y-m-d H:i:s', $info['start_time']);

            if ($info['stop_time'])
                $stop_time = date('Y-m-d H:i:s', $info['stop_time']);
            if (isset($start_time) && isset($stop_time))
                $info['section_time'] = [$start_time, $stop_time];
            else
                $info['section_time'] = [];
            unset($info['start_time'], $info['stop_time']);
        }
        if ($info['images'])
            $info['images'] = json_decode($info['images'], true);
        else
            $info['images'] = [];
        $info['give_integral'] = intval($info['give_integral']);
        $info['price'] = floatval($info['price']);
        $info['postage'] = floatval($info['postage']);
        $info['cost'] = floatval($info['cost']);
        $info['bargain_max_price'] = floatval($info['bargain_max_price']);
        $info['bargain_min_price'] = floatval($info['bargain_min_price']);
        $info['min_price'] = floatval($info['min_price']);
        $info['weight'] = floatval($info['weight']);
        $info['volume'] = floatval($info['volume']);
        $info['description'] = StoreDescription::getDescription($id, 2);
        $info['attrs'] = self::attr_list($id);
        return $info;
    }

    public static function attr_list($id)
    {
        $bargainInfo = self::where('id', $id)->find();
        $bargainResult = StoreProductAttrResult::where('product_id', $id)->where('type', 2)->value('result');
        $items = json_decode($bargainResult, true)['attr'];
        $productAttr = self::get_attr($items, $bargainInfo['product_id'], 0);
        $seckillAttr = self::get_attr($items, $id, 2);
        foreach ($productAttr as $pk => $pv) {
            foreach ($seckillAttr as &$sv) {
                if ($pv['detail'] == $sv['detail']) {
                    $productAttr[$pk] = $sv;
                }
            }
            $productAttr[$pk]['detail'] = json_decode($productAttr[$pk]['detail']);
        }
        $attrs['items'] = $items;
        $attrs['value'] = $productAttr;
        foreach ($items as $key => $item) {
            $header[] = ['title' => $item['value'], 'key' => 'value' . ($key + 1), 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '砍价起始金额', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '砍价最低价', 'slot' => 'min_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'slot' => 'quota', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $attrs['header'] = $header;
        return $attrs;
    }

    public static function get_attr($attr, $id, $type)
    {
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        if ($type == 2) {
            $min_price = self::where('id', $id)->value('min_price');
        } else {
            $min_price = 0;
        }
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $sukValue = StoreProductAttrValue::where('product_id', $id)->where('type', $type)->where('suk', $suk)->column('bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota', 'suk');
            if (count($sukValue)) {
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                }
                $valueNew[$count]['detail'] = json_encode($detail);
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['min_price'] = $min_price ? floatval($min_price) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['quota'] = $sukValue[$suk]['quota'] ? intval($sukValue[$suk]['quota']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
                $valueNew[$count]['opt'] = $type != 0 ? true : false;
                $count++;
            }
        }
        return $valueNew;
    }
}