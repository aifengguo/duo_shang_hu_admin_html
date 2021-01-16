<?php
/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2019/11/13 4:52 PM
 */

namespace crmeb\repositories;

use app\models\user\WechatUser;
use app\models\wechat\WechatTemplate;
use crmeb\services\printer\Printer;
use app\models\routine\RoutineTemplate;
use app\models\store\StoreOrderCartInfo;
use app\models\user\UserExtract;
use app\models\store\StoreOrder;
use app\models\store\StoreProduct;
use app\models\store\StoreProductReply;
use think\facade\Log;

/** 消息通知静态类
 * Class NoticeRepositories
 * @package crmeb\repositories
 */
class NoticeRepositories
{
    /** 支付成功通知
     * @param $order
     */
    public static function noticeOrderPaySuccess($order)
    {
        $wechatUser = WechatUser::where('uid', $order['uid'])->field(['openid', 'routine_openid'])->find();
        if ($wechatUser) {
            $openid = $wechatUser['openid'];
            $routineOpenid = $wechatUser['routine_openid'];
            try {
                if ($openid && in_array($order['is_channel'], [0, 2])) {//公众号发送模板消息
                    $wechatTemplate = new WechatTemplate();
                    $wechatTemplate->sendOrderPaySuccess($order);
                    //订单支付成功后给客服发送模版消息
                    $wechatTemplate->sendServiceNotice($order);
                    //订单支付成功后给客服发送客服消息
                    CustomerRepository::sendOrderPaySuccessCustomerService($order, 1);
                } else if ($routineOpenid && in_array($order['is_channel'], [1, 2])) {//小程序发送模板消息
                    RoutineTemplate::sendOrderSuccess($order['uid'], $order['pay_price'], $order['order_id']);
                    //订单支付成功后给客服发送客服消息
                    CustomerRepository::sendOrderPaySuccessCustomerService($order, 0);
                }

            } catch (\Exception $e) {
            }
        }
        //打印小票
        $switch = sys_config('pay_success_printing_switch') ? true : false;
        if ($switch) {
            try {
                $order['cart_id'] = is_string($order['cart_id']) ? json_decode($order['cart_id'], true) : $order['cart_id'];
                $cartInfo = StoreOrderCartInfo::whereIn('cart_id', $order['cart_id'])->field('cart_info')->select();
                $cartInfo = count($cartInfo) ? $cartInfo->toArray() : [];
                $product = [];
                foreach ($cartInfo as $item) {
                    $value = is_string($item['cart_info']) ? json_decode($item['cart_info']) : $item['cart_info'];
                    $value['productInfo']['store_name'] = $value['productInfo']['store_name'] ?? "";
                    $value['productInfo']['store_name'] = StoreOrderCartInfo::getSubstrUTf8($value['productInfo']['store_name'], 10, 'UTF-8', '');
                    $product[] = $value;
                }
                (new Printer())->setPrinterContent([
                    'name' => sys_config('site_name'),
                    'orderInfo' => is_object($order) ? $order->toArray() : $order,
                    'product' => $product
                ])->startPrinter();
            } catch (\Exception $e) {
                Log::error('小票打印出现错误,错误原因：' . $e->getMessage());
            }
        }
        //短信通知 下发用户支付成功 下发管理员支付通知
        event('ShortMssageSend', [$order['order_id'], ['PaySuccess', 'AdminPaySuccess']]);

    }

    /**
     * 待办消息提醒
     * @return array
     */
    public static function jnotice()
    {
        $data['ordernum'] = StoreOrder::where('paid', 1)->where('status', 0)
            ->where('shipping_type', 1)->where('refund_status', 0)
            ->where('is_del', 0)->count();
        $store_stock = sys_config('store_stock');
        if ($store_stock < 0) $store_stock = 2;
        $data['inventory'] = StoreProduct::where('stock', '<=', $store_stock)->where('is_show', 1)->where('is_del', 0)->count();//库存
        $data['commentnum'] = StoreProductReply::where('is_reply', 0)->count();
        $data['reflectnum'] = UserExtract::where('status', 0)->count();//提现
        $data['msgcount'] = intval($data['ordernum']) + intval($data['inventory']) + intval($data['commentnum']) + intval($data['reflectnum']);
        $data['newOrderId'] = StoreOrder::statusByWhere(1)->where('is_remind', 0)->column('order_id', 'id');
        if (count($data['newOrderId'])) StoreOrder::where('order_id', 'in', $data['newOrderId'])->update(['is_remind' => 1]);
        $value = [];
        if ($data['ordernum'] != 0) {
            $value[] = [
                'title' => "您还有$data[ordernum]个待发货的订单",
                'type' => 'bulb'
            ];
        }
        if ($data['inventory'] != 0) {
            $value[] = [
                'title' => "您有$data[inventory]个商品库存预警",
                'type' => 'information',
            ];
        }
        if ($data['commentnum'] != 0) {
            $value[] = [
                'title' => "您有$data[commentnum]条评论待回复",
                'type' => 'bulb'
            ];
        }
        if ($data['reflectnum'] != 0) {
            $value[] = [
                'title' => "您有$data[reflectnum]个提现申请待审核",
                'type' => 'bulb'
            ];
        }
        return static::noticeData($value);
    }

    /**
     * 消息返回格式
     * @param array $data
     * @return array
     */
    public static function noticeData(array $data): array
    {
        // 消息图标
        $iconColor = [
            // 邮件 消息
            'mail' => [
                'icon' => 'md-mail',
                'color' => '#3391e5'
            ],
            // 普通 消息
            'bulb' => [
                'icon' => 'md-bulb',
                'color' => '#87d068'
            ],
            // 警告 消息
            'information' => [
                'icon' => 'md-information',
                'color' => '#fe5c57'
            ],
            // 关注 消息
            'star' => [
                'icon' => 'md-star',
                'color' => '#ff9900'
            ],
            // 申请 消息
            'people' => [
                'icon' => 'md-people',
                'color' => '#f06292'
            ],
        ];
        // 消息类型
        $type = array_keys($iconColor);
        // 默认数据格式
        $default = [
            'icon' => 'md-bulb',
            'iconColor' => '#87d068',
            'title' => '',
            'url' => '',
            'type' => 'bulb',
            'read' => 0,
            'time' => 0
        ];
        $value = [];
        foreach ($data as $item) {
            $val = array_merge($default, $item);
            if (isset($item['type']) && in_array($item['type'], $type)) {
                $val['type'] = $item['type'];
                $val['iconColor'] = $iconColor[$item['type']]['color'] ?? '';
                $val['icon'] = $iconColor[$item['type']]['icon'] ?? '';
            }
            $value[] = $val;
        }
        return $value;
    }
}