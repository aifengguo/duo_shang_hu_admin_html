<?php
/**
 * Created by PhpStorm.
 * User: lofate
 * Date: 2019/11/28
 * TIME: 12:27
 */

namespace app\adminapi\controller\v1\finance;

use app\adminapi\controller\AuthController;
use app\models\user\UserExtract as UserExtractModel;
use crmeb\services\{
    FormBuilder as Form, UtilService as Util
};
use think\facade\Route as Url;
use think\Request;

class UserExtract extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['status', ''],
            ['extract_type', ''],
            ['nireid', ''],
            ['data', ''],
        ], $this->request);
        $map['data'] = $where['data'];
        $extract_statistics = UserExtractModel::extractStatistics($map);
        $list = UserExtractModel::systemPage($where);
        return $this->success(compact('extract_statistics', 'list'));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) return $this->fail('数据不存在');
        $UserExtract = UserExtractModel::get($id);
        if (!$UserExtract) return $this->fail('数据不存在!');
        $f = array();
        $f[] = Form::input('real_name', '姓名', $UserExtract['real_name']);
        $f[] = Form::number('extract_price', '提现金额', $UserExtract['extract_price'])->precision(2);
        if ($UserExtract['extract_type'] == 'alipay') {
            $f[] = Form::input('alipay_code', '支付宝账号', $UserExtract['alipay_code']);
        } else if ($UserExtract['extract_type'] == 'weixin') {
            $f[] = Form::input('wechat', '微信号', $UserExtract['wechat']);
        } else {
            $f[] = Form::input('bank_code', '银行卡号', $UserExtract['bank_code']);
            $f[] = Form::input('bank_address', '开户行', $UserExtract['bank_address']);
        }
        $f[] = Form::input('mark', '备注', $UserExtract['mark'])->type('textarea');
        return $this->makePostForm('编辑', $f, Url::buildUrl('/finance/extract/' . $id), 'PUT');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $UserExtract = UserExtractModel::get($id);
        if (!$UserExtract) return $this->fail('数据不存在!');
        if ($UserExtract['extract_type'] == 'alipay') {
            $data = Util::postMore([
                'real_name',
                'mark',
                'extract_price',
                'alipay_code',
            ]);
            if (!$data['real_name']) return $this->fail('请输入姓名');
            if ($data['extract_price'] <= -1) return $this->fail('请输入提现金额');
            if (!$data['alipay_code']) return $this->fail('请输入支付宝账号');
        } else if ($UserExtract['extract_type'] == 'weixin') {
            $data = Util::postMore([
                'real_name',
                'mark',
                'extract_price',
                'wechat',
            ]);
            if ($data['extract_price'] <= -1) return $this->fail('请输入提现金额');
            if (!$data['wechat']) return $this->fail('请输入微信账号');
        } else {
            $data = Util::postMore([
                'real_name',
                'extract_price',
                'mark',
                'bank_code',
                'bank_address',
            ]);
            if (!$data['real_name']) return $this->fail('请输入姓名');
            if ($data['extract_price'] <= -1) return $this->fail('请输入提现金额');
            if (!$data['bank_code']) return $this->fail('请输入银行卡号');
            if (!$data['bank_address']) return $this->fail('请输入开户行');
        }
        if (!UserExtractModel::edit($data, $id))
            return $this->fail(UserExtractModel::getErrorInfo('修改失败'));
        else
            return $this->success('修改成功!');
    }

    /**
     * 拒绝
     * @param $id
     * @return mixed
     */
    public function refuse($id)
    {
        if (!UserExtractModel::be(['id' => $id, 'status' => 0])) return $this->fail('操作记录不存在或状态错误!');
        $fail_msg = request()->post();
        $extract = UserExtractModel::get($id);
        if (!$extract) return $this->fail('操作记录不存在!');
        if ($extract->status == 1) return $this->fail('已经提现,错误操作');
        if ($extract->status == -1) return $this->fail('您的提现申请已被拒绝,请勿重复操作!');
        $res = UserExtractModel::changeFail($id, $fail_msg['message']);
        if ($res) {
            return $this->success('操作成功!');
        } else {
            return $this->fail('操作失败!');
        }
    }

    /**
     * 通过
     * @param $id
     * @return mixed
     */
    public function adopt($id)
    {
        if (!UserExtractModel::be(['id' => $id, 'status' => 0]))
            return $this->fail('操作记录不存在或状态错误!');
        UserExtractModel::beginTrans();
        $extract = UserExtractModel::get($id);
        if (!$extract) return $this->fail('操作记录不存!');
        if ($extract->status == 1) return $this->fail('您已提现,请勿重复提现!');
        if ($extract->status == -1) return $this->fail('您的提现申请已被拒绝!');
        $res = UserExtractModel::changeSuccess($id);
        if ($res) {
            UserExtractModel::commitTrans();
            return $this->success('操作成功!');
        } else {
            UserExtractModel::rollbackTrans();
            return $this->fail('操作失败!');
        }
    }
}
