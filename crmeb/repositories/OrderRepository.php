<?php

namespace crmeb\repositories;

use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\user\WechatUser;
use crmeb\exceptions\AdminException;

/**
 * Class OrderRepository
 * @package crmeb\repositories
 */
class OrderRepository
{

    /**
     * 用户确认收货
     * @param $order
     * @param $uid
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function storeProductOrderUserTakeDelivery($order, $uid)
    {
        $res1 = StoreOrder::gainUserIntegral($order);
        $res2 = User::backOrderBrokerage($order);
        StoreOrder::orderTakeAfter($order);
        WechatUser::userTakeOrderGiveCoupon($uid, $order['total_price']);//满赠优惠券
        UserBill::where('uid', $order['uid'])->where('link_id', $order['id'])->where('type', 'pay_money')->update(['take' => 1]);
        if (!($res1 && $res2)) exception('收货失败!');
    }

    /**
     * 修改状态 为已收货  admin模块
     * @param $order
     * @throws \Exception
     */
    public static function storeProductOrderTakeDeliveryAdmin($order)
    {

        $res1 = StoreOrder::gainUserIntegral($order, false);
        $res2 = User::backOrderBrokerage($order, false);
        StoreOrder::orderTakeAfter($order);
        UserBill::where('uid', $order['uid'])->where('link_id', $order['id'])->where('type', 'pay_money')->update(['take' => 1]);
        if (!($res1 && $res2)) throw new AdminException('收货失败!');
    }


    /**
     * 修改状态为  已退款  admin模块
     * @param $data
     * @param $oid
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function storeProductOrderRefundY($data, $oid)
    {
        $order = StoreOrder::where('id', $oid)->find();
        if ($order['is_channel'] == 1)
            return StoreOrder::refundRoutineTemplate($oid); //TODO 小程序余额退款模板消息
        else
            return StoreOrder::refundTemplate($data, $oid);//TODO 公众号余额退款模板消息
    }


    /**
     * TODO  后台余额退款
     * @param $product
     * @param $refund_data
     * @throws \Exception
     */
    public static function storeOrderYueRefund($product, $refund_data)
    {
        $res = StoreOrder::integralBack($product['id']);
        if (!$res) throw new AdminException('退积分失败!');
    }

    /**
     * 订单退积分
     * @param $product $product 商品信息
     * @param $back_integral $back_integral 退多少积分
     */
    public static function storeOrderIntegralBack($product, $back_integral)
    {

    }


    public static function computedOrder()
    {

    }

}