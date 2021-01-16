<?php
namespace crmeb\repositories;

use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\UserAddress;
use app\models\user\WechatUser;
use app\models\wechat\WechatTemplate;

/**
 * Class ProductRepositories
 * @package crmeb\repositories
 */
class ProductRepositories
{


    /**
     * 用户确认收货
     * @param $order
     * @param $uid
     * @throws \Exception
     */
    public static function storeProductOrderUserTakeDelivery($order, $uid)
    {
        $res1 = StoreOrder::gainUserIntegral($order);
        $res2 = User::backOrderBrokerage($order);
        StoreOrder::orderTakeAfter($order);
        WechatUser::userTakeOrderGiveCoupon($uid, $order['total_price']);//满赠优惠券
        if(!($res1 && $res2)) exception('收货失败!');
    }

    /**
     * 订单创建成功后  wap模块
     * @param $order
     * @param $group
     */
    public static function storeProductOrderCreateWap($order,$group)
    {
        UserAddress::be(['is_default'=>1,'uid'=>$order['uid']]) || UserAddress::setDefaultAddress($group['addressId'],$order['uid']);
    }

    /**
     * 退款发送管理员消息
     * @param $oid
     * @param $uid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function storeProductOrderApplyRefundWap($oid, $uid)
    {
        $order = StoreOrder::where('id',$oid)->find();
        (new WechatTemplate)->sendRefundServiceNotice($order);
    }


    /**
     * 评价商品
     * @param $replyInfo
     * @param $cartInfo
     * @return StoreOrder|\think\Model
     */
    public static function storeProductOrderReplyWap($replyInfo, $cartInfo)
    {
        return StoreOrder::checkOrderOver($cartInfo['oid']);
    }


}