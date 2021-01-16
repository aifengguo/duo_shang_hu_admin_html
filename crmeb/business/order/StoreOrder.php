<?php

namespace crmeb\business\order;

use app\models\store\StoreOrderCartInfo;
use app\models\store\StoreOrderStatus;
use app\models\store\StorePink;
use crmeb\basic\BaseBusiness;
use crmeb\services\SystemConfigService;
use crmeb\utils\Arr;
use think\facade\Config;

/**
 * 订单业务逻辑处理
 * Class StoreOrder
 * @package crmeb\business\order
 */
class StoreOrder extends BaseBusiness
{

    public function __construct()
    {
        $this->model = new \app\models\store\StoreOrder;
        $this->pk = $this->model->getPk();
    }

    public function tidyOrder($data, $status = false)
    {
        $ids = array_column($data,'id');
        $info = StoreOrderCartInfo::whereIn('oid', $ids)->field('cart_info,oid')->select()->toArray();
        $infos = [];
        foreach ($info as $key => $value) {
            $infos[$value['oid']][]['cart_info'] = $value['cart_info'];
        }
        //系统预设取消订单时间段
        $keyValue = ['order_cancel_time', 'order_activity_time', 'order_bargain_time', 'order_seckill_time', 'order_pink_time'];
        //获取配置
        $systemValue = SystemConfigService::more($keyValue);
        //格式化数据
        $systemValue = Arr::setValeTime($keyValue, is_array($systemValue) ? $systemValue : []);
        foreach ($data as &$item) {
            $_info = $infos[$item['id']];
            foreach ($_info as $k => $v) {
                if (!is_array($v['cart_info']))
                    $_info[$k]['cart_info'] = json_decode($v['cart_info'], true);
            }
            foreach ($_info as $k => $v) {
                unset($_info[$k]['cart_info']['type'], $_info[$k]['cart_info']['product_id'], $_info[$k]['cart_info']['combination_id'], $_info[$k]['cart_info']['seckill_id'], $_info[$k]['cart_info']['bargain_id'], $_info[$k]['cart_info']['bargain_id'], $_info[$k]['cart_info']['truePrice'], $_info[$k]['cart_info']['vip_truePrice'], $_info[$k]['cart_info']['trueStock'], $_info[$k]['cart_info']['costPrice'], $_info[$k]['cart_info']['productInfo']['id'], $_info[$k]['cart_info']['productInfo']['vip_price'], $_info[$k]['cart_info']['productInfo']['postage'], $_info[$k]['cart_info']['productInfo']['give_integral'], $_info[$k]['cart_info']['productInfo']['sales'], $_info[$k]['cart_info']['productInfo']['stock'], $_info[$k]['cart_info']['productInfo']['unit_name'], $_info[$k]['cart_info']['productInfo']['is_postage'], $_info[$k]['cart_info']['productInfo']['slider_image'], $_info[$k]['cart_info']['productInfo']['cost'], $_info[$k]['cart_info']['productInfo']['mer_id'], $_info[$k]['cart_info']['productInfo']['cate_id'], $_info[$k]['cart_info']['productInfo']['is_show'], $_info[$k]['cart_info']['productInfo']['store_info'], $_info[$k]['cart_info']['productInfo']['is_del'], $_info[$k]['cart_info']['is_pay'], $_info[$k]['cart_info']['is_del'], $_info[$k]['cart_info']['is_new'], $_info[$k]['cart_info']['add_time'], $_info[$k]['cart_info']['id'], $_info[$k]['cart_info']['uid'], $_info[$k]['cart_info']['product_attr_unique']);
                $_info[$k]['cart_info']['productInfo']['suk'] = '';
                if (isset($v['cart_info']['productInfo']['attrInfo'])) {
                    $_info[$k]['cart_info']['productInfo']['product_id'] = $_info[$k]['cart_info']['productInfo']['attrInfo']['product_id'];
                    $_info[$k]['cart_info']['productInfo']['image'] = $_info[$k]['cart_info']['productInfo']['attrInfo']['image'];
                    $_info[$k]['cart_info']['productInfo']['price'] = $_info[$k]['cart_info']['productInfo']['attrInfo']['price'];
                    $_info[$k]['cart_info']['productInfo']['suk'] = $_info[$k]['cart_info']['productInfo']['attrInfo']['suk'];
                    unset($_info[$k]['cart_info']['productInfo']['attrInfo']);
                }
                if (!isset($v['cart_info']['productInfo']['ot_price'])) {
                    $_info[$k]['cart_info']['productInfo']['ot_price'] = $v['cart_info']['productInfo']['price'];
                }
            }
            $item['_info'] = $_info;
            if ($status) {
                $status = [];
                if (!$item['paid'] && $item['pay_type'] == 'offline' && !$item['status'] >= 2) {
                    $status['_type'] = 9;
                    $status['_title'] = '线下付款';
                    $status['_msg'] = '等待商家处理,请耐心等待';
                    $status['_class'] = 'nobuy';
                } else if (!$item['paid']) {
                    $status['_type'] = 0;
                    $status['_title'] = '未支付';
                    if ($item['pink_id'] || $item['combination_id']) {
                        $order_pink_time = $systemValue['order_pink_time'] ? $systemValue['order_pink_time'] : $systemValue['order_activity_time'];
                        $time = bcadd($item['add_time'], $order_pink_time * 3600, 0);
                        $status['_msg'] = '请在' . date('Y-m-d H:i:s', $time) . '前完成支付!';
                    } else if ($item['seckill_id']) {
                        $order_seckill_time = $systemValue['order_seckill_time'] ? $systemValue['order_seckill_time'] : $systemValue['order_activity_time'];
                        $time = bcadd($item['add_time'], $order_seckill_time * 3600, 0);
                        $status['_msg'] = '请在' . date('Y-m-d H:i:s', $time) . '前完成支付!';
                    } else if ($item['bargain_id']) {
                        $order_bargain_time = $systemValue['order_bargain_time'] ? $systemValue['order_bargain_time'] : $systemValue['order_activity_time'];
                        $time = bcadd($item['add_time'], $order_bargain_time * 3600, 0);
                        $status['_msg'] = '请在' . date('Y-m-d H:i:s', $time) . '前完成支付!';
                    } else {
                        $time = bcadd($item['add_time'], $systemValue['order_cancel_time'] * 3600, 0);
                        $status['_msg'] = '请在' . date('Y-m-d H:i:s', $time) . '前完成支付!';
                    }
                    $status['_class'] = 'nobuy';
                } else if ($item['refund_status'] == 1) {
                    $status['_type'] = -1;
                    $status['_title'] = '申请退款中';
                    $status['_msg'] = '商家审核中,请耐心等待';
                    $status['_class'] = 'state-sqtk';
                } else if ($item['refund_status'] == 2) {
                    $status['_type'] = -2;
                    $status['_title'] = '已退款';
                    $status['_msg'] = '已为您退款,感谢您的支持';
                    $status['_class'] = 'state-sqtk';
                } else if (!$item['status']) {
                    if ($item['pink_id']) {
                        if (StorePink::where('id', $item['pink_id'])->where('status', 1)->count()) {
                            $status['_type'] = 11;
                            $status['_title'] = '拼团中';
                            $status['_msg'] = '等待其他人参加拼团';
                            $status['_class'] = 'state-nfh';
                        } else {
                            $status['_type'] = 1;
                            $status['_title'] = '未发货';
                            $status['_msg'] = '商家未发货,请耐心等待';
                            $status['_class'] = 'state-nfh';
                        }
                    } else {
                        $status['_type'] = 1;
                        $status['_title'] = '未发货';
                        $status['_msg'] = '商家未发货,请耐心等待';
                        $status['_class'] = 'state-nfh';
                    }
                } else if ($item['status'] == 1) {
                    if ($item['delivery_type'] == 'send') {//TODO 送货
                        $status['_type'] = 2;
                        $status['_title'] = '待收货';
                        $status['_msg'] = date('m月d日H时i分', StoreOrderStatus::getTime($item['id'], 'delivery')) . '服务商已送货';
                        $status['_class'] = 'state-ysh';
                    } else {//TODO  发货
                        $status['_type'] = 2;
                        $status['_title'] = '待收货';
                        $status['_msg'] = date('m月d日H时i分', StoreOrderStatus::getTime($item['id'], 'delivery_goods')) . '服务商已发货';
                        $status['_class'] = 'state-ysh';
                    }
                } else if ($item['status'] == 2) {
                    $status['_type'] = 3;
                    $status['_title'] = '待评价';
                    $status['_msg'] = '已收货,快去评价一下吧';
                    $status['_class'] = 'state-ypj';
                } else if ($item['status'] == 3) {
                    $status['_type'] = 4;
                    $status['_title'] = '交易完成';
                    $status['_msg'] = '交易完成,感谢您的支持';
                    $status['_class'] = 'state-ytk';
                }
                $payType = Config::get("pay.payType") ?? [];
                $deliveryType = Config::get("pay.deliveryType") ?? [];
                if (isset($item['pay_type']))
                    $status['_payType'] = $payType[$item['pay_type']] ?? '其他方式';
                if (isset($item['delivery_type']))
                    $status['_deliveryType'] = $deliveryType[$item['delivery_type']] ?? '其他方式';
                $item['_status'] = $status;
            } else {
                if ($item['paid'] == 0 && $item['status'] == 0) {
                    $item['status_name'] = '未支付';
                } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['refund_status'] == 0) {
                    $item['status_name'] = '未发货';
                } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['refund_status'] == 0) {
                    $item['status_name'] = '待收货';
                } else if ($item['paid'] == 1 && $item['status'] == 2 && $item['refund_status'] == 0) {
                    $item['status_name'] = '待评价';
                } else if ($item['paid'] == 1 && $item['status'] == 3 && $item['refund_status'] == 0) {
                    $item['status_name'] = '已完成';
                }
            }
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        return $data;
    }
}