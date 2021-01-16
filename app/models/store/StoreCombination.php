<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\models\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 拼团商品Model
 * Class StoreCombination
 * @package app\models\store
 */
class StoreCombination extends BaseModel
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
    protected $name = 'store_combination';

    use ModelTrait;

    protected function getAddTimeAttr($value)
    {
        if ($value) return date('Y-m-d H:i:s', $value);
        return '';
    }

    /**
     * @param $where
     * @return array
     */
    public static function get_list($length = 10)
    {
        if ($post = input('post.')) {
            $where = $post['where'];
            $model = new self();
            $model = $model->alias('c');
            $model = $model->join('StoreProduct s', 's.id=c.product_id');
            $model = $model->where('c.is_show', 1)->where('c.is_del', 0)->where('c.start_time', '<', time())->where('c.stop_time', '>', time());
            if (!empty($where['search'])) {
                $model = $model->where('c.title', 'like', "%{$where['search']}%");
                $model = $model->whereOr('s.keyword', 'like', "{$where['search']}%");
            }
            $model = $model->field('c.*,s.price as product_price');
            if ($where['key']) {
                if ($where['sales'] == 1) {
                    $model = $model->order('c.sales desc');
                } else if ($where['sales'] == 2) {
                    $model = $model->order('c.sales asc');
                }
                if ($where['price'] == 1) {
                    $model = $model->order('c.price desc');
                } else if ($where['price'] == 2) {
                    $model = $model->order('c.price asc');
                }
                if ($where['people'] == 1) {
                    $model = $model->order('c.people asc');
                }
                if ($where['default'] == 1) {
                    $model = $model->order('c.sort desc,c.id desc');
                }
            } else {
                $model = $model->order('c.sort desc,c.id desc');
            }
            $page = is_string($where['page']) ? (int)$where['page'] + 1 : $where['page'] + 1;
            $list = $model->page($page, $length)->select()->toArray();
            return ['list' => $list, 'page' => $page];
        }
    }

    /**
     * 获取拼团数据
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public static function getAll($page = 0, $limit = 20)
    {
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s', 's.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price');
        $model = $model->order('c.sort desc,c.id desc');
        $model = $model->where('c.is_show', 1);
        $model = $model->where('c.is_del', 0);
        $model = $model->where('c.start_time', '<', time());
        $model = $model->where('c.stop_time', '>', time());
        if ($page) $model = $model->page($page, $limit);
        return $model->select()->each(function ($item) {
            $item['image'] = set_file_url($item['image']);
            $item['price'] = floatval($item['price']);
            $item['product_price'] = floatval($item['product_price']);
        });
    }

    /**
     * 获取是否有拼团商品
     * */
    public static function getPinkIsOpen()
    {
        return self::alias('c')->join('StoreProduct s', 's.id=c.product_id')->where('c.is_show', 1)->where('c.is_del', 0)
            ->where('c.start_time', '<', time())->where('c.stop_time', '>', time())->count();
    }

    /**
     * 获取一条拼团数据
     * @param $id
     * @return mixed
     */
    public static function getCombinationOne($id)
    {
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s', 's.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price,SUM(s.sales+s.ficti) as total');
        $model = $model->where('c.is_show', 1);
        $model = $model->where('c.is_del', 0);
        $model = $model->where('c.id', $id);
        $model = $model->where('c.start_time', '<', time());
        $model = $model->where('c.stop_time', '>', time() - 86400);
        $info = $model->find();
        if ($info['id']) {
            return $info;
        } else {
            return [];
        }
    }

    /**
     * 获取推荐的拼团商品
     * @return mixed
     */
    public static function getCombinationHost($limit = 0)
    {
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s', 's.id=c.product_id');
        $model = $model->field('c.id,c.image,c.price,c.sales,c.title,c.people,s.price as product_price');
        $model = $model->where('c.is_del', 0);
        $model = $model->where('c.is_host', 1);
        $model = $model->where('c.start_time', '<', time());
        $model = $model->where('c.stop_time', '>', time());
        if ($limit) $model = $model->limit($limit);
        return $model->select();
    }

    /**
     * 修改销量和库存
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function decCombinationStock($num, $CombinationId, $unique)
    {
        $product_id = self::where('id', $CombinationId)->value('product_id');
        if ($unique) {
            $res = false !== StoreProductAttrValue::decProductAttrStock($CombinationId, $unique, $num, 3);
            $res = $res && self::where('id', $CombinationId)->dec('stock', $num)->inc('sales', $num)->update();
            $sku = StoreProductAttrValue::where('product_id', $CombinationId)->where('unique', $unique)->where('type', 3)->value('suk');
            $res = $res && StoreProductAttrValue::where('product_id', $product_id)->where('suk', $sku)->where('type', 0)->dec('stock', $num)->inc('sales', $num)->update();
        } else {
            $res = false !== self::where('id', $CombinationId)->dec('stock', $num)->inc('sales', $num)->update();
        }
        $res = $res && StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();
        return $res;
    }

    /**
     * 增加库存,减少销量
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function incCombinationStock($num, $CombinationId)
    {
        $combination = self::where('id', $CombinationId)->field(['stock', 'sales'])->find();
        if (!$combination) return true;
        if ($combination->sales > 0) $combination->sales = bcsub($combination->sales, $num, 0);
        if ($combination->sales < 0) $combination->sales = 0;
        $combination->stock = bcadd($combination->stock, $num, 0);
        return $combination->save();
    }

    /**
     * 判断库存是否足够
     * @param $id
     * @param $cart_num
     * @return int|mixed
     */
    public static function getCombinationStock($id, $cart_num)
    {
        $stock = self::where('id', $id)->value('stock');
        return $stock > $cart_num ? $stock : 0;
    }

    /**
     * 获取字段值
     * @param $id
     * @param $field
     * @return mixed
     */
    public static function getCombinationField($id, $field = 'title')
    {
        return self::where('id', $id)->value($field);
    }

    /**
     * 获取商品状态
     * @param $id
     * @return mixed
     */
    public static function isValidCombination($id)
    {
        $model = new self();
        $model = $model->where('id', $id);
        $model = $model->where('is_del', 0);
        $model = $model->where('is_show', 1);
        return $model->count();
    }

    /**
     * 增加浏览量
     * @param int $id
     * @return bool
     */
    public static function editIncBrowse($id = 0)
    {
        if (!$id) return false;
        $browse = self::where('id', $id)->value('browse');
        $browse = bcadd($browse, 1, 0);
        self::edit(['browse' => $browse], $id);
    }

    /**
     * 获取查看拼团统计
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getStatistics()
    {
        $statistics = array();
        $statistics['browseCount'] = self::value('sum(browse) as browse');//总展现量
        $statistics['browseCount'] = $statistics['browseCount'] ? $statistics['browseCount'] : 0;
        $statistics['visitCount'] = StoreVisit::where('product_type', 'combination')->count();//访客人数
        $statistics['partakeCount'] = StorePink::getCountPeopleAll();//参与人数
        $statistics['pinkCount'] = StorePink::getCountPeoplePink();//成团数量
        $res = [
            ['col' => 6, 'count' => $statistics['browseCount'], 'name' => '总展现量(次)', 'className' => 'md-trending-up'],
            ['col' => 6, 'count' => $statistics['visitCount'], 'name' => '访客人数(人)', 'className' => 'md-stats'],
            ['col' => 6, 'count' => $statistics['partakeCount'], 'name' => '参与人数(人)', 'className' => 'ios-speedometer-outline'],
            ['col' => 6, 'count' => $statistics['pinkCount'], 'name' => '成团数量(个)', 'className' => 'md-rose'],
        ];
        return compact('res');
    }

    /**
     * 拼团列表
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = self::setWhere($where);
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                $item['count_people_all'] = StorePink::getCountPeopleAll($item['id']);//参与人数
                $item['count_people_pink'] = StorePink::getCountPeoplePink($item['id']);//成团人数
                $item['count_people_browse'] = StoreVisit::getVisitPeople($item['id']);//访问人数
            });
        return compact('count', 'list');
    }

    /**
     * 设置拼团 where 条件
     * @param $where
     * @param null $model
     * @return mixed
     */
    public static function setWhere($where, $model = null)
    {
        $model = $model === null ? new self() : $model;
        $model = $model->alias('c');
        $model = $model->field('c.*,p.store_name,p.price as ot_price');
        $model = $model->join('StoreProduct p', 'p.id=c.product_id', 'LEFT');
        if (isset($where['is_show']) && $where['is_show'] != '') $model = $model->where('c.is_show', $where['is_show']);
        if (isset($where['store_name']) && $where['store_name'] != '') $model = $model->where('p.store_name|p.id|c.id|c.title', 'LIKE', "%$where[store_name]%");
        return $model->order('c.id desc')->where('c.is_del', 0);
    }
    /**
     *查出导出数据
     * @param $where
     */
    public static function exportData($where)
    {
        $list = self::setWhere($where)->select();
        count($list) && $list = $list->toArray();
        foreach ($list as &$item) {
            $item['count_people_all'] = StorePink::getCountPeopleAll($item['id']);//参与人数
            $item['count_people_pink'] = StorePink::getCountPeoplePink($item['id']);//成团人数
            $item['count_people_browse'] = StoreVisit::getVisitPeople($item['id']);//访问人数
            $item['_stop_time'] = date('Y/m/d H:i:s', $item['stop_time']);
        }
        return $list;
    }

    /**
     * 详情
     * @param $id
     * @return mixed
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
        $info['price'] = floatval($info['price']);
        $info['postage'] = floatval($info['postage']);
        $info['weight'] = floatval($info['weight']);
        $info['volume'] = floatval($info['volume']);
        $info['description'] = StoreDescription::getDescription($id, 3);
        $info['attrs'] = self::attr_list($id);
        return $info;
    }

    public static function attr_list($id)
    {
        $productId = self::where('id', $id)->value('product_id');
        $combinationResult = StoreProductAttrResult::where('product_id', $id)->where('type', 3)->value('result');
        $items = json_decode($combinationResult, true)['attr'];
        $productAttr = self::get_attr($items, $productId, 0);
        $seckillAttr = self::get_attr($items, $id, 3);
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
        $header[] = ['title' => '拼团价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
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
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['quota'] = $sukValue[$suk]['quota'] ? intval($sukValue[$suk]['quota']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
                $valueNew[$count]['_checked'] = $type != 0 ? true : false;
                $count++;
            }
        }
        return $valueNew;
    }
}