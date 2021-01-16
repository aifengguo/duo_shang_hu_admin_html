<?php
/**
 * @author: lofate
 * @day: 2019/12/21
 */

namespace app\models\store;

use crmeb\basic\BaseModel;
use crmeb\services\workerman\ChannelService;
use crmeb\traits\ModelTrait;

class StoreProductAttrValue extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr_value';

    use ModelTrait;

    protected $insert = ['unique'];

    protected function setSukAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setUniqueAttr($value, $data)
    {

        if (is_array($data['suk'])) $data['suk'] = $this->setSukAttr($data['suk']);
        return self::uniqueId($data['product_id'] . $data['suk'] . uniqid(true));
    }

    /*
     * 减少销量增加库存
     * */
    public static function incProductAttrStock($productId, $unique, $num)
    {
        $productAttr = self::where('unique', $unique)->where('product_id', $productId)->field('stock,sales')->find();
        if (!$productAttr) return true;
        if ($productAttr->sales > 0) $productAttr->sales = bcsub($productAttr->sales, $num, 0);
        if ($productAttr->sales < 0) $productAttr->sales = 0;
        $productAttr->stock = bcadd($productAttr->stock, $num, 0);
        return $productAttr->save();
    }

    public static function decProductAttrStock($productId, $unique, $num, $type = 0)
    {
        if ($type == 0) {
            $res = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)
                ->dec('stock', $num)->inc('sales', $num)->update();
        } else {
            $res = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)
                ->dec('stock', $num)->dec('quota', $num)->inc('sales', $num)->update();
        }

        if ($res) {
            $stock = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)->value('stock');
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

    /**
     * 获取属性参数
     * @param $productId
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getStoreProductAttrResult($productId)
    {
        $productAttr = StoreProductAttr::getProductAttr($productId);
        if (!$productAttr) return [];
        $attr = [];
        foreach ($productAttr as $key => $value) {
            $attr[$key]['value'] = $value['attr_name'];
            $attr[$key]['detailValue'] = '';
            $attr[$key]['attrHidden'] = true;
            $attr[$key]['detail'] = $value['attr_values'];
        }
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $sukValue = self::where('product_id', $productId)->where('suk', $suk)->column('bar_code,cost,price,ot_price,stock,image as pic', 'suk');
            if (!count($sukValue)) {
                unset($value[$key]);
            } else {
                foreach (array_keys($detail) as $k => $title) {
                    $header[$k]['title'] = $title;
                    $header[$k]['align'] = 'center';
                    $header[$k]['minWidth'] = 130;
                }
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                    $header[$k]['key'] = 'value' . ($k + 1);
                }
                $valueNew[$count]['detail'] = $detail;
//                $valueNew[$count]['unit_name'] = $product['unit_name'];
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'];
                $valueNew[$count]['price'] = floatval($sukValue[$suk]['price']);
                $valueNew[$count]['cost'] = floatval($sukValue[$suk]['cost']);
                $valueNew[$count]['ot_price'] = floatval($sukValue[$suk]['ot_price']);
                $valueNew[$count]['stock'] = intval($sukValue[$suk]['stock']);
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'];
//                $valueNew[$count]['check'] = false;
                $count++;
            }
        }
//        $header[] = ['title'=>'商品单位','key'=>'unit_name'];
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '售价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '成本价', 'slot' => 'cost', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '原价', 'slot' => 'ot_price', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '库存', 'slot' => 'stock', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '商品编号', 'slot' => 'bar_code', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '操作', 'slot' => 'action', 'align' => 'center', 'minWidth' => 70];
        return ['attr' => $attr, 'value' => $valueNew, 'header' => $header];
    }

    public static function uniqueId($key)
    {
        return substr(md5($key), 12, 8);
    }

    public static function clearProductAttrValue($productId, $type = 0)
    {
        return self::where('product_id', $productId)->where('type', $type)->delete();
    }

    public static function activityRules($productId, $type = 0)
    {
        $productAttr = StoreProductAttr::getProductAttr($productId);
        if (!$productAttr) return [];
        $attr = [];
        foreach ($productAttr as $key => $value) {
            $attr[$key]['value'] = $value['attr_name'];
            $attr[$key]['detailValue'] = '';
            $attr[$key]['attrHidden'] = true;
            $attr[$key]['detail'] = $value['attr_values'];
        }
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $sukValue = self::where('product_id', $productId)->where('suk', $suk)->where('type', 0)->column('bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota', 'suk');
            if (!count($sukValue)) {
                unset($value[$key]);
            } else {
                foreach (array_keys($detail) as $k => $title) {
                    $header[$k]['title'] = $title;
                    $header[$k]['align'] = 'center';
                    $header[$k]['minWidth'] = 80;
                }
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                    $header[$k]['key'] = 'value' . ($k + 1);
                }
                $valueNew[$count]['detail'] = $detail;
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'];
                $valueNew[$count]['price'] = floatval($sukValue[$suk]['price']);
                if ($type == 2) $valueNew[$count]['min_price'] = 0;
                $valueNew[$count]['cost'] = floatval($sukValue[$suk]['cost']);
                $valueNew[$count]['ot_price'] = floatval($sukValue[$suk]['ot_price']);
                $valueNew[$count]['stock'] = intval($sukValue[$suk]['stock']);
                $valueNew[$count]['quota'] = intval($sukValue[$suk]['quota']);
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'];
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
                $count++;
            }
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        if ($type == 1) {
            $header[] = ['title' => '秒杀价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 2) {
            $header[] = ['title' => '砍价起始金额', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '砍价最低价', 'slot' => 'min_price', 'align' => 'center', 'minWidth' => 80];
        } else {
            $header[] = ['title' => '拼团价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        if ($type == 2) {
            $header[] = ['title' => '限量', 'slot' => 'quota', 'align' => 'center', 'minWidth' => 80];
        } else {
            $header[] = ['title' => '限量', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        return ['attr' => $attr, 'value' => $valueNew, 'header' => $header];
    }

}