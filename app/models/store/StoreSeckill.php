<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace app\models\store;

use app\models\system\SystemGroupData;
use crmeb\basic\BaseModel;
use crmeb\services\GroupDataService;
use crmeb\traits\ModelTrait;

/**
 * TODO 秒杀商品Model
 * Class StoreSeckill
 * @package app\models\store
 */
class StoreSeckill extends BaseModel
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
    protected $name = 'store_seckill';

    use ModelTrait;

    protected function getAddTimeAttr($value)
    {
        if ($value) return date('Y-m-d H:i:s', $value);
        return '';
    }

    protected function getImagesAttr($value)
    {
        return json_decode($value, true) ?: [];
    }

    public static function getSeckillCount()
    {
        $seckillTime = sys_data('routine_seckill_time') ?: [];//秒杀时间段
        $timeInfo = ['time' => 0, 'continued' => 0];
        foreach ($seckillTime as $key => $value) {
            $currentHour = date('H');
            $activityEndHour = bcadd((int)$value['time'], (int)$value['continued'], 0);
            if ($currentHour >= (int)$value['time'] && $currentHour < $activityEndHour && $activityEndHour < 24) {
                $timeInfo = $value;
                break;
            }
        }
        if ($timeInfo['time'] == 0) return 0;
        $activityEndHour = bcadd((int)$timeInfo['time'], (int)$timeInfo['continued'], 0);
        $startTime = bcadd(strtotime(date('Y-m-d')), bcmul($timeInfo['time'], 3600, 0));
        $stopTime = bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
        return self::where('is_del', 0)->where('status', 1)->where('start_time', '<=', $startTime)->where('stop_time', '>=', $stopTime)->count();
    }

    /*
     * 获取秒杀列表
     *
     * */
    public static function seckillList($time, $page = 0, $limit = 20)
    {
        if ($page) $list = StoreSeckill::alias('n')->join('store_product c', 'c.id=n.product_id')->where('c.is_show', 1)->where('c.is_del', 0)->where('n.is_del', 0)->where('n.status', 1)->where('n.start_time', '<=', time())->where('n.stop_time', '>=', time() - 86400)->where('n.time_id', $time)->field('n.*')->order('n.sort desc')->page($page, $limit)->select();
        else $list = StoreSeckill::alias('n')->join('store_product c', 'c.id=n.product_id')->where('c.is_show', 1)->where('c.is_del', 0)->where('n.is_del', 0)->where('n.status', 1)->where('n.start_time', '<=', time())->where('n.stop_time', '>=', time() - 86400)->where('n.time_id', $time)->field('n.*')->order('sort desc')->select();
        if ($list) return $list->hidden(['cost', 'add_time', 'is_del'])->toArray();
        return [];
    }

    /**
     * 获取所有秒杀商品
     * @param string $field
     * @return array
     */
    public static function getListAll($offset = 0, $limit = 10, $field = 'id,product_id,image,title,price,ot_price,start_time,stop_time,stock,sales')
    {
        $time = time();
        $model = self::where('is_del', 0)->where('status', 1)->where('stock', '>', 0)->field($field)
            ->where('start_time', '<', $time)->where('stop_time', '>', $time)->order('sort DESC,add_time DESC');
        $model = $model->limit($offset, $limit);
        $list = $model->select();
        if ($list) return $list->toArray();
        else return [];
    }

    /**
     * 获取热门推荐的秒杀商品
     * @param int $limit
     * @param string $field
     * @return array
     */
    public static function getHotList($limit = 0, $field = 'id,product_id,image,title,price,ot_price,start_time,stop_time,stock')
    {
        $time = time();
        $model = self::where('is_hot', 1)->where('is_del', 0)->where('status', 1)->where('stock', '>', 0)->field($field)
            ->where('start_time', '<', $time)->where('stop_time', '>', $time)->order('sort DESC,add_time DESC');
        if ($limit) $model->limit($limit);
        $list = $model->select();
        if ($list) return $list->toArray();
        else return [];
    }

    /**
     * 获取一条秒杀商品
     * @param $id
     * @param string $field
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getValidProduct($id, $field = '*')
    {
        $time = time();
        $info = self::alias('n')->join('store_product c', 'c.id=n.product_id')->where('n.id', $id)->where('c.is_show', 1)->where('c.is_del', 0)->where('n.is_del', 0)->where('n.status', 1)->where('n.start_time', '<', $time)->where('n.stop_time', '>', $time - 86400)->field('n.*,SUM(c.sales+c.ficti) as total')->find();
        if ($info['id']) {
            return $info;
        } else {
            return [];
        }
    }

    public static function initFailSeckill()
    {
        self::where('is_hot', 1)->where('is_del', 0)->where('status', '<>', 1)->where('stop_time', '<', time())->update(['status' => '-1']);
    }

    public static function idBySimilaritySeckill($id, $limit = 4, $field = '*')
    {
        $time = time();
        $list = [];
        $productId = self::where('id', $id)->value('product_id');
        if ($productId) {
            $list = array_merge($list, self::where('product_id', $productId)->where('id', '<>', $id)
                ->where('is_del', 0)->where('status', 1)->where('stock', '>', 0)
                ->field($field)->where('start_time', '<', $time)->where('stop_time', '>', $time)
                ->order('sort DESC,add_time DESC')->limit($limit)->select()->toArray());
        }
        $limit = $limit - count($list);
        if ($limit) {
            $list = array_merge($list, self::getHotList($limit, $field));
        }

        return $list;
    }

    /** 获取秒杀商品库存
     * @param $id
     * @return mixed
     */
    public static function getProductStock($id)
    {
        return self::where('id', $id)->value('stock');
    }

    /**
     * 获取字段值
     * @param $id
     * @param string $field
     * @return mixed
     */
    public static function getProductField($id, $field = 'title')
    {
        return self::where('id', $id)->value($field);
    }

    /**
     * 修改秒杀库存
     * @param int $num
     * @param int $seckillId
     * @return bool
     */
    public static function decSeckillStock($num = 0, $seckillId = 0, $unique = '')
    {
        $product_id = self::where('id', $seckillId)->value('product_id');
        if ($unique) {
            $res = false !== StoreProductAttrValue::decProductAttrStock($seckillId, $unique, $num, 1);
            $res = $res && self::where('id', $seckillId)->dec('stock', $num)->inc('sales', $num)->update();
            $sku = StoreProductAttrValue::where('product_id', $seckillId)->where('unique', $unique)->where('type', 1)->value('suk');
            $res = $res && StoreProductAttrValue::where('product_id', $product_id)->where('suk', $sku)->where('type', 0)->dec('stock', $num)->inc('sales', $num)->update();
        } else {
            $res = false !== self::where('id', $seckillId)->dec('stock', $num)->inc('sales', $num)->update();
        }
        $res = $res && StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();
        return $res;
    }

    /**
     * 增加库存较少销量
     * @param int $num
     * @param int $seckillId
     * @return bool
     */
    public static function incSeckillStock($num = 0, $seckillId = 0)
    {
        $seckill = self::where('id', $seckillId)->field(['stock', 'sales'])->find();
        if (!$seckill) return true;
        if ($seckill->sales > 0) $seckill->sales = bcsub($seckill->sales, $num, 0);
        if ($seckill->sales < 0) $seckill->sales = 0;
        $seckill->stock = bcadd($seckill->stock, $num, 0);
        return $seckill->save();
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        $model = $model->alias('s');
        if ($where['status'] != '') $model = $model->where('s.status', $where['status']);
        if ($where['store_name'] != '') $model = $model->where('s.title|s.id', 'LIKE', "%$where[store_name]%");
        $model = $model->page(bcmul($where['page'], $where['limit'], 0), $where['limit']);
        $model = $model->order('s.id desc');
        $model = $model->where('s.is_del', 0);
        return self::page($model, function ($item) {
            $item['store_name'] = StoreProduct::where('id', $item['product_id'])->value('store_name');
            if ($item['status']) {
                if ($item['start_time'] > time())
                    $item['start_name'] = '活动未开始';
                else if (bcadd($item['stop_time'], 86400) < time())
                    $item['start_name'] = '活动已结束';
                else if (bcadd($item['stop_time'], 86400) > time() && $item['start_time'] < time()) {
                    $config = SystemGroupData::get($item['time_id']);
                    if ($config) {
                        $arr = json_decode($config->value, true);
                        $now_hour = date('H', time());
                        $start_hour = $arr['time']['value'];
                        $continued = $arr['continued']['value'];
                        $end_hour = $start_hour + $continued;
                        if ($start_hour > $now_hour) {
                            $item['start_name'] = '活动未开始';
                        } elseif ($end_hour < $now_hour) {
                            $item['start_name'] = '活动已结束';
                        } else {
                            $item['start_name'] = '正在进行中';
                        }
                    } else {
                        $item['start_name'] = '正在进行中';
                    }
                }
            } else $item['start_name'] = '关闭';

        }, $where, $where['limit']);
    }

    /**
     * 秒杀数据
     * @param $where
     */
    public static function exportData($where)
    {
        $model = new self;
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        if ($where['store_name'] != '') $model = $model->where('title|id', 'LIKE', "%$where[store_name]%");
        $list = $model->order('id desc')->where('is_del', 0)->select();
        count($list) && $list = $list->toArray();
        foreach ($list as &$item) {
            $item['store_name'] = StoreProduct::where('id', $item['product_id'])->value('store_name');
        }
        return $list;
    }
    /**
     * 详情
     */
    public static function getOne($id)
    {
        $info = self::get($id);
        if ($info) {
            if ($info['start_time'])
                $start_time = date('Y-m-d', $info['start_time']);

            if ($info['stop_time'])
                $stop_time = date('Y-m-d', $info['stop_time']);
            if (isset($start_time) && isset($stop_time))
                $info['section_time'] = [$start_time, $stop_time];
            else
                $info['section_time'] = [];
            unset($info['start_time'], $info['stop_time']);
        }
        $info['give_integral'] = intval($info['give_integral']);
        $info['price'] = floatval($info['price']);
        $info['ot_price'] = floatval($info['ot_price']);
        $info['postage'] = floatval($info['postage']);
        $info['cost'] = floatval($info['cost']);
        $info['weight'] = floatval($info['weight']);
        $info['volume'] = floatval($info['volume']);
        $info['description'] = StoreDescription::getDescription($id, 1);
        $info['attrs'] = self::attr_list($id);
        return compact('info');
    }

    public static function attr_list($id)
    {
        $productId = self::where('id', $id)->value('product_id');
        $seckillResult = StoreProductAttrResult::where('product_id', $id)->where('type', 1)->value('result');
        $items = json_decode($seckillResult, true)['attr'];
        $productAttr = self::get_attr($items, $productId, 0);
        $seckillAttr = self::get_attr($items, $id, 1);
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
        $header[] = ['title' => '秒杀价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
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

    /**
     * 获取秒杀是否已结束
     * @param $seckill_id
     * @return bool
     */
    public static function isSeckillEnd($seckill_id)
    {
        $time_id = self::where('id', $seckill_id)->value('time_id');
        //秒杀时间段
        $seckillTime = sys_data('routine_seckill_time') ?? [];
        $seckillTimeIndex = 0;
        $activityTime = [];
        if (count($seckillTime)) {
            foreach ($seckillTime as $key => &$value) {
                $currentHour = date('H');
                $activityEndHour = bcadd((int)$value['time'], (int)$value['continued'], 0);
                if ($activityEndHour > 24) {
                    $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'] . ':00' : '0' . (int)$value['time'] . ':00';
                    $value['state'] = '即将开始';
                    $value['status'] = 2;
                    $value['stop'] = (int)bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                } else {
                    if ($currentHour >= (int)$value['time'] && $currentHour < $activityEndHour) {
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'] . ':00' : '0' . (int)$value['time'] . ':00';
                        $value['state'] = '抢购中';
                        $value['stop'] = (int)bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                        $value['status'] = 1;
                        if (!$seckillTimeIndex) $seckillTimeIndex = $key;
                    } else if ($currentHour < (int)$value['time']) {
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'] . ':00' : '0' . (int)$value['time'] . ':00';
                        $value['state'] = '即将开始';
                        $value['status'] = 2;
                        $value['stop'] = (int)bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                    } else if ($currentHour >= $activityEndHour) {
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'] . ':00' : '0' . (int)$value['time'] . ':00';
                        $value['state'] = '已结束';
                        $value['status'] = 0;
                        $value['stop'] = (int)bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                    }
                }

                if ($value['id'] == $time_id) {
                    $activityTime = $value;
                    break;
                }
            }
        }
        if (time() < $activityTime['stop'])
            return true;
        else
            return false;
    }
}