<?php

namespace app\adminapi\controller\v1\finance;

use app\adminapi\controller\AuthController;
use app\models\routine\RoutineTemplate;
use app\models\user\{
    User, UserRecharge as UserRechargeModel, UserBill
};
use app\models\wechat\WechatTemplate;
use crmeb\services\{
    FormBuilder as Form, MiniProgramService, UtilService as Util, WechatService
};
use think\facade\Route as Url;

class UserRecharge extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['data', ''],
            ['paid', ''],
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['excel', ''],
        ]);
        return $this->success(UserRechargeModel::getUserRechargeList($where));
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('缺少参数');
        $rechargInfo = UserRechargeModel::get($id);
        if ($rechargInfo->paid) return $this->fail('已支付的订单记录无法删除');
        if (UserRechargeModel::del($id))
            return $this->success('删除成功');
        else
            return $this->fail('删除失败');
    }

    /**
     * 获取用户充值数据
     * @return array
     */
    public function user_recharge()
    {
        $where = Util::getMore([
            ['data', ''],
            ['paid', ''],
            ['nickname', ''],
        ]);
        return $this->success(UserRechargeModel::getDataList($where));
    }

    /**退款表单
     * @param $id
     * @return mixed|void
     */
    public function refund_edit($id)
    {
        if (!$id) return $this->fail('数据不存在');
        $UserRecharge = UserRechargeModel::get($id);
        if (!$UserRecharge) return $this->fail('数据不存在!');
        if ($UserRecharge['paid'] == 1) {
            $f = array();
            $f[] = Form::input('order_id', '退款单号', $UserRecharge->getData('order_id'))->disabled(1);
            $f[] = Form::number('refund_price', '退款金额', $UserRecharge->getData('price'))->precision(2)->min(0)->max($UserRecharge->getData('price'))->value(0);
            return $this->makePostForm('编辑', $f, Url::buildUrl('/recharge/' . $id), 'PUT');
        } else return $this->fail('数据不存在!');
    }

    /**
     * 退款操作
     * @param $id
     */
    public function refund_update($id)
    {
        $data = Util::postMore([
            'refund_price',
        ]);
        if (!$id) return $this->fail('数据不存在');
        $UserRecharge = UserRechargeModel::get($id);
        if (!$UserRecharge) return $this->fail('数据不存在!');
        if ($UserRecharge['price'] == $UserRecharge['refund_price']) return $this->fail('已退完支付金额!不能再退款了');
        if (!$data['refund_price']) return $this->fail('请输入退款金额');
        $refund_price = $data['refund_price'];
        $data['refund_price'] = bcadd($data['refund_price'], $UserRecharge['refund_price'], 2);
        $bj = bccomp((float)$UserRecharge['price'], (float)$data['refund_price'], 2);
        if ($bj < 0) return $this->fail('退款金额大于支付金额，请修改退款金额');
        $refund_data['pay_price'] = $UserRecharge['price'];
        $refund_data['refund_price'] = $refund_price;
//        $refund_data['refund_account']='REFUND_SOURCE_RECHARGE_FUNDS';
        try {
            $recharge_type = UserRechargeModel::where('order_id', $UserRecharge['order_id'])->value('recharge_type');
            if ($recharge_type == 'weixin') {
                WechatService::payOrderRefund($UserRecharge['order_id'], $refund_data);
            } else {
                MiniProgramService::payOrderRefund($UserRecharge['order_id'], $refund_data);
            }
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
        UserRechargeModel::edit($data, $id);
        User::bcDec($UserRecharge['uid'], 'now_money', $refund_price, 'uid');
        switch (strtolower($UserRecharge['recharge_type'])) {
            case 'weixin':
                $wechatTemplate = new WechatTemplate();
                $wechatTemplate->sendRechargeRefundStatus($data, $UserRecharge);
                break;
            case 'routine':
                RoutineTemplate::sendRechargeSuccess($UserRecharge, $data['refund_price']);
                break;
        }
        UserBill::expend('系统退款', $UserRecharge['uid'], 'now_money', 'user_recharge_refund', $refund_price, $id, $UserRecharge['price'], '退款给用户' . $refund_price . '元');
        return $this->success('退款成功!');
    }
}
