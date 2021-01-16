<?php

namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use crmeb\services\{
    FormBuilder as Form, UtilService as Util
};
use app\models\store\{
    StoreCouponIssue as CouponIssueModel, StoreCouponIssueUser
};
use think\facade\Route as Url;
use crmeb\traits\CurdControllerTrait;

/**
 * 已发布优惠券管理
 * Class StoreCouponIssue
 * @package app\adminapi\controller\v1\marketing
 */
class StoreCouponIssue extends AuthController
{
    use CurdControllerTrait;

    protected $bindModel = CouponIssueModel::class;

    /**
     * 列表
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['status', ''],
            ['coupon_title', '']
        ]);
        $list = CouponIssueModel::sysPage($where);
        return $this->success($list);
    }

    /**
     * 删除
     * @param string $id
     * @return mixed
     */
    public function delete($id)
    {
        if (CouponIssueModel::edit(['is_del' => 1], $id, 'id'))
            return $this->success('删除成功!');
        else
            return $this->fail('删除失败!');
    }

    /**
     * @param string $id
     * @return mixed|string
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit($id)
    {
        $issueInfo = CouponIssueModel::get($id);
        if (-1 == $issueInfo['status'] || 1 == $issueInfo['is_del']) return $this->fail('状态错误,无法修改');
        $f = [Form::radio('status', '是否开启', $issueInfo['status'])->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])];
        return $this->makePostForm('状态修改', $f, Url::buildUrl('/marketing/coupon/released/status/' . $id), 'PUT');
    }

    /**修改状态
     * @param $id
     */
    public function status($id)
    {
        $data = Util::postMore([
            'status'
        ]);
        if (!isset($data['status'])) return $this->fail('缺少参数');
        CouponIssueModel::where(['id' => $id])->update(['status' => $data['status']]);
        return $this->success('修改成功');
    }

    /**
     * 领取记录
     * @param string $id
     * @return mixed|string
     */
    public function issue_log($id)
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20]
        ]);
        $list = StoreCouponIssueUser::systemCouponIssuePage($id, $where);
        return $this->success($list);
    }
}