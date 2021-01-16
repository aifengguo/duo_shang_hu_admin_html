<?php

namespace app\models\wechat;

use app\models\store\StoreBargain;
use app\models\store\StoreOrderCartInfo;
use app\models\store\StorePink;
use crmeb\services\template\Template;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\models\user\WechatUser;
use think\facade\Route;
use app\models\wechat\StoreService as ServiceModel;

/**
 * 微信模板消息model
 * Class WechatTemplate
 * @package app\models\wechat
 */
class WechatTemplate extends BaseModel
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
    protected $name = 'template_message';

    use ModelTrait;

    /**
     * 获取系统分页数据   分类
     * @param array $where
     * @return array
     */
    public static function systemPage($where = array())
    {
        $model = self::where('type', 1);
        if ($where['name'] !== '') $model = $model->where('name', 'LIKE', "%$where[name]%");
        if ($where['status'] !== '') $model = $model->where('status', $where['status']);
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                if ($item['content']) $item['content'] = explode("\n", $item['content']);
            });
        return compact('count', 'list');
    }

    /**
     * 支付成功发送模板消息
     * @param $order
     * @return bool
     */
    public function sendOrderPaySuccess($order)
    {
        return $this->sendTemplate('ORDER_PAY_SUCCESS', $order['uid'], [
            'first' => '亲，您购买的商品已支付成功',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['pay_price'],
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/order_details/index?order_id=' . $order['order_id'])->suffix('')->domain(true)->build());
    }

    /**
     * 订单发货
     * @param $order
     * @param array $data
     * @return bool|mixed
     */
    public function sendOrderDeliver($order, array $data)
    {
        $goodsName = StoreOrderCartInfo::getProductNameList($order['id']);
        return $this->sendTemplate('ORDER_DELIVER_SUCCESS', $order['uid'], [
            'keyword1' => $goodsName,
            'keyword2' => $order['pay_type'] == 'offline' ? '线下支付' : date('Y/m/d H:i', $order['pay_time']),
            'keyword3' => $order['user_address'],
            'keyword4' => $data['delivery_name'],
            'keyword5' => $data['delivery_id'],
            'first' => '亲,您的订单已发货,请注意查收',
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/order_details/index?order_id=' . $order['order_id'])->suffix(false)->domain(true)->build());
    }

    /**
     * 订单发货
     * @param $order
     * @param array $data
     * @return bool|mixed
     */
    public function sendOrderPostage($order, array $data)
    {
        return $this->sendTemplate('ORDER_POSTAGE_SUCCESS', $order['uid'], [
            'keyword1' => $order['order_id'],
            'keyword2' => $data['delivery_name'],
            'keyword3' => $data['delivery_id'],
            'first' => '亲,您的订单已发货,请注意查收',
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/order_details/index?order_id=' . $order['order_id'])->suffix(false)->domain(true)->build());
    }

    /**
     * 发送客服消息
     * @param $order
     * @param string|null $link
     * @return bool
     */
    public function sendServiceNotice($order, string $link = null)
    {
        $kefuIds = ServiceModel::where('notify', 1)->column('uid', 'uid');
        if (empty($kefuIds)) {
            return true;
        }
        $data = [
            'first' => "亲,您有一个新订单 \n订单号:{$order['order_id']}",
            'keyword1' => '新订单',
            'keyword2' => '已支付',
            'keyword3' => date('Y/m/d H:i', time()),
            'remark' => '请及时处理'
        ];
        foreach ($kefuIds as $uid) {
            $this->sendTemplate('ADMIN_NOTICE', $uid, $data, $link);
        }
    }

    /**
     * 退款发送客服消息
     * @param $order
     * @param string|null $link
     * @return bool
     */
    public function sendRefundServiceNotice($order, string $link = null)
    {
        $kefuIds = ServiceModel::where('notify', 1)->column('uid', 'uid');
        if (empty($kefuIds)) {
            return true;
        }
        $data = [
            'first' => "亲,您有一个订单申请退款 \n订单号:{$order['order_id']}",
            'keyword1' => '申请退款',
            'keyword2' => '待处理',
            'keyword3' => date('Y/m/d H:i', time()),
            'remark' => '请及时处理'
        ];
        foreach ($kefuIds as $uid) {
            $this->sendTemplate('ADMIN_NOTICE', $uid, $data, $link);
        }
    }

    /**
     * 确认收货发送模板消息
     * @param $order
     * @return bool|mixed
     */
    public function sendOrderTakeSuccess($order, $title)
    {
        return $this->sendTemplate('ORDER_TAKE_SUCCESS', $order['uid'], [
            'first' => '亲，您的订单已收货',
            'keyword1' => $order['order_id'],
            'keyword2' => '已收货',
            'keyword3' => date('Y-m-d H:i:s', time()),
            'keyword4' => $title,
            'remark' => '感谢您的光临！'
        ]);
    }

    /**
     * 发送退款模板消息
     * @param array $data
     * @param $order
     * @return bool|mixed
     */
    public function sendOrderRefundStatus(array $data, $order)
    {
        return $this->sendTemplate('ORDER_REFUND_STATUS', $order['uid'], [
            'first' => '亲，您购买的商品已退款,本次退款' . $data['refund_price'] . '金额',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['pay_price'],
            'keyword3' => date('Y-m-d H:i:s', $order['add_time']),
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/order_details/index?order_id=' . $order['order_id'])->suffix('')->domain(true)->build());
    }

    /**
     * 发送用户充值退款模板消息
     * @param array $data
     * @param $userRecharge
     * @return bool|mixed
     */
    public function sendRechargeRefundStatus(array $data, $userRecharge)
    {
        return $this->sendTemplate('ORDER_REFUND_STATUS', $userRecharge['uid'], [
            'first' => '亲，您充值的金额已退款,本次退款' .
                $data['refund_price'] . '金额',
            'keyword1' => $userRecharge['order_id'],
            'keyword2' => $userRecharge['price'],
            'keyword3' => date('Y-m-d H:i:s', $userRecharge['add_time']),
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/users/user_bill/index')->domain(true)->suffix(false)->build());
    }

    /**
     * 佣金提现失败发送模板消息
     * @param $uid
     * @param $extract_number
     * @param $fail_msg
     * @return bool|mixed
     */
    public function sendUserBalanceChangeFial($uid, $extract_number, $fail_msg)
    {
        return $this->sendTemplate('USER_BALANCE_CHANGE', $uid, [
            'first' => '提现失败,退回佣金' . $extract_number . '元',
            'keyword1' => '佣金提现',
            'keyword2' => date('Y-m-d H:i:s', time()),
            'keyword3' => $extract_number,
            'remark' => '错误原因:' . $fail_msg
        ], Route::buildUrl('/pages/users/user_spread_money/index?type=1')->suffix(false)->domain(true)->build());
    }

    /**
     * 佣金提现成功发送模板消息
     * @param $uid
     * @param $extractNumber
     * @return bool|mixed
     */
    public function sendUserBalanceChangeSuccess($uid, $extractNumber)
    {
        return $this->sendTemplate('USER_BALANCE_CHANGE', $uid, [
            'first' => '成功提现佣金' . $extractNumber . '元',
            'keyword1' => '佣金提现',
            'keyword2' => date('Y-m-d H:i:s', time()),
            'keyword3' => $extractNumber,
            'remark' => '点击查看我的佣金明细'
        ], Route::buildUrl('/pages/users/user_spread_money/index?type=1')->suffix(false)->domain(true)->build());
    }

    /**
     * 拼团成功发送模板消息
     * @param $uid
     * @param $order_id
     * @param $title
     * @return bool|mixed
     */
    public function sendOrderPinkSuccess($uid, $order_id, $title)
    {
        return $this->sendTemplate('ORDER_USER_GROUPS_SUCCESS', $uid, [
            'first' => '亲，您的拼团已经完成了',
            'keyword1' => $order_id,
            'keyword2' => $title,
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/activity/goods_combination_status/index?id=' . $order_id)->suffix(false)->domain(true)->build());
    }

    /**
     * 参团成功发送模板消息
     * @param $uid
     * @param $order_id
     * @param $title
     * @return bool|mixed
     */
    public function sendOrderPinkUseSuccess($uid, string $order_id, string $title, string $pink_id)
    {
        return $this->sendTemplate('ORDER_USER_GROUPS_SUCCESS', $uid, [
            'first' => '亲，您已成功参与拼团',
            'keyword1' => $order_id,
            'keyword2' => $title,
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/activity/goods_combination_status/index?id=' . $pink_id)->suffix(false)->domain(true)->build());
    }

    /**
     * 取消拼团发送模板消息
     * @param $uid
     * @param StorePink $order_id
     * @param $price
     * @param string $title
     * @return bool|mixed
     */
    public function sendOrderPinkClone($uid, StorePink $pink, $title)
    {
        return $this->sendTemplate('ORDER_USER_GROUPS_LOSE', $uid, [
            'first' => '亲，您的拼团取消',
            'keyword1' => $title,
            'keyword2' => $pink->price,
            'keyword3' => $pink->price,
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/activity/goods_combination_status/index?id=' . $pink->id)->suffix(false)->domain(true)->build());
    }

    /**
     * 拼团失败发送模板消息
     * @param $uid
     * @param StorePink $pink
     * @param $title
     * @return bool|mixed
     */
    public function sendOrderPinkFial($uid, StorePink $pink, $title)
    {
        return $this->sendTemplate('ORDER_USER_GROUPS_LOSE', $uid, [
            'first' => '亲，您的拼团失败',
            'keyword1' => $title,
            'keyword2' => $pink->price,
            'keyword3' => $pink->price,
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/activity/goods_combination_status/index?id=' . $pink->id)->suffix(false)->domain(true)->build());
    }

    /**
     * 开团成功发送模板消息
     * @param $uid
     * @param StorePink $pink
     * @param $title
     * @return bool|mixed
     */
    public function sendOrderPinkOpenSuccess($uid, $pink, $title)
    {
        return $this->sendTemplate('OPEN_PINK_SUCCESS', $uid, [
            'first' => '您好，您已成功开团！赶紧与小伙伴们分享吧！！！',
            'keyword1' => $title,
            'keyword2' => $pink['total_price'],
            'keyword3' => $pink['people'],
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/activity/goods_combination_status/index?id=' . $pink['id'])->suffix(false)->domain(true)->build());
    }

    /**
     * 砍价成功发送模板消息
     * @param $uid
     * @param StoreBargain $bargain
     * @return bool|mixed
     */
    public function sendBrgainSuccess($uid, StoreBargain $bargain)
    {
        return $this->sendTemplate('BARGAIN_SUCCESS', $uid, [
            'first' => '好腻害！你的朋友们已经帮你砍到底价了！',
            'keyword1' => $bargain['title'],
            'keyword2' => $bargain['min_price'],
            'remark' => '点击查看订单详情'
        ], Route::buildUrl('/pages/activity/goods_bargain_details/index?id=' . $bargain['id'] . '&bargain=' . $uid)->suffix(false)->domain(true)->build());
    }

    /**
     * 发送模板消息
     * @param string $tempCode 模板消息常量名称
     * @param $uid 用户uid
     * @param array $data 模板内容
     * @param string $link 跳转链接
     * @param string|null $color 文字颜色
     * @return bool|mixed
     */
    public function sendTemplate(string $tempCode, $uid, array $data, string $link = null, string $color = null)
    {
        try {
            $openid = WechatUser::uidToOpenid($uid, 'openid');
            if (!$openid) return false;
            $template = new Template('wechat');
            $template->to($openid)->color($color);
            if ($link) $template->url($link);
            return $template->send($tempCode, $data);
        } catch (\Exception $e) {
            return false;
        }
    }
}