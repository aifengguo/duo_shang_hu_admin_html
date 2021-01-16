<?php

namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use crmeb\services\{UtilService, FormBuilder as Form, CacheService};
use think\facade\{Config, Route as Url};
use app\models\system\{SystemRole, SystemAdmin as SystemAdminModel};
use think\Request;

class SystemAdmin extends AuthController
{
    /**
     * 显示管理员资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        [$name, $roles, $page, $limit] = UtilService::getMore([
            ['name', ''],
            ['roles', ''],
            ['page', 1],
            ['limit', 10],
        ], $this->request, true);
        return $this->success(SystemAdminModel::getAdminList($name, bcadd($this->adminInfo['level'], 1, 0), $roles, $page, $limit));
    }

    /**
     * 创建表单
     * @return mixed
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function create()
    {
        $f[] = Form::input('account', '管理员账号')->required('请填写管理员账号');
        $f[] = Form::input('pwd', '管理员密码')->type('password')->required('请填写管理员密码');
        $f[] = Form::input('conf_pwd', '确认密码')->type('password')->required('请输入确认密码');
        $f[] = Form::input('real_name', '管理员姓名')->required('请输入管理员姓名');
        $list = SystemRole::getRole(bcadd($this->adminInfo['level'], 1, 0));
        $options = [];
        foreach ($list as $id => $roleName) {
            $options[] = ['label' => $roleName, 'value' => $id];
        }
        $f[] = Form::select('roles', '管理员身份')->setOptions($options)->multiple(true)->required('请选择管理员身份');
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return $this->makePostForm('管理员添加', $f, Url::buildUrl('/setting/admin')->suffix(false));
    }

    /**
     * 保存管理员
     * @param Request $request
     * @return mixed
     */
    public function save(Request $request)
    {
        $data = UtilService::postMore([
            ['account', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['real_name', ''],
            ['roles', []],
            ['status', 0],
        ], $request);

        $this->validate($data, \app\adminapi\validates\setting\SystemAdminValidata::class);

        if ($data['conf_pwd'] != $data['pwd']) return $this->fail('两次输入的密码不相同');
        unset($data['conf_pwd']);
        if (SystemAdminModel::be(['account' => $data['account']])) return $this->fail('管理员账号已存在');

        $data['pwd'] = password_hash($data['pwd'], PASSWORD_BCRYPT);
        $data['add_time'] = time();
        $data['level'] = $this->adminInfo['level'] + 1;
        $data['roles'] = implode(',', $data['roles']);
        if (SystemAdminModel::create($data)) {
            \think\facade\Cache::clear();
            return $this->success('添加成功');
        } else
            return $this->fail('添加失败');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id || !($adminInfo = SystemAdminModel::get($id)))
            return $this->fail('管理员信息读取失败');
        $f[] = Form::input('account', '管理员账号', $adminInfo->getData('account'))->required('请填写管理员账号');
        $f[] = Form::input('pwd', '管理员密码')->type('password')->placeholder('请填写管理员密码');
        $f[] = Form::input('conf_pwd', '确认密码')->type('password')->placeholder('请输入确认密码');
        $f[] = Form::input('real_name', '管理员姓名', $adminInfo->getData('real_name'))->required('请输入管理员姓名');
        $list = SystemRole::getRole(bcadd($this->adminInfo['level'], 1, 0));
        $options = [];
        foreach ($list as $k => $roleName) {
            $options[] = ['label' => $roleName, 'value' => $k];
        }
        $f[] = Form::select('roles', '管理员身份', $adminInfo->roles)->setOptions($options)->multiple(true)->required('请选择管理员身份');
        $f[] = Form::radio('status', '状态', $adminInfo->getData('status'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return $this->makePostForm('管理员修改', $f, Url::buildUrl('/setting/admin/' . $id)->suffix(false), 'PUT');
    }

    /**
     * 修改管理员信息
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $data = UtilService::postMore([
            ['account', ''],
            ['conf_pwd', ''],
            ['pwd', ''],
            ['real_name', ''],
            ['roles', []],
            ['status', 0],
        ], $request);

        $this->validate($data, \app\adminapi\validates\setting\SystemAdminValidata::class, 'update');

        if (!$adminInfo = SystemAdminModel::get($id))
            return $this->fail('管理员不存在,无法修改');
        if ($data['pwd']) {
            if (!$data['conf_pwd'])
                return $this->fail('请输入确认密码');
            if ($data['conf_pwd'] != $data['pwd'])
                return $this->fail('上次输入的密码不相同');
            $adminInfo->pwd = password_hash($data['pwd'], PASSWORD_BCRYPT);
        }
        if (SystemAdminModel::where(['account' => $data['account']])->where('id', '<>', $id)->count())
            return $this->fail('管理员账号已存在');

        $adminInfo->roles = implode(',', $data['roles']);
        $adminInfo->real_name = $data['real_name'];
        $adminInfo->account = $data['account'];
        $adminInfo->status = $data['status'];
        if ($adminInfo->save()) {
            \think\facade\Cache::clear();
            return $this->success('修改成功');
        } else {
            return $this->fail('修改失败');
        }
    }

    /**
     * 删除管理员
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('删除失败，缺少参数');
        if (SystemAdminModel::edit(['is_del' => 1, 'status' => 0], $id, 'id'))
            return $this->success('删除成功！');
        else
            return $this->fail('删除失败');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        SystemAdminModel::where(['id' => $id])->update(['status' => $status]);
        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }

    /**
     * 获取当前登陆管理员的信息
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info()
    {
        return $this->success(SystemAdminModel::where(['id' => $this->adminId])->find()->hidden(['pwd', 'is_del', 'status'])->toArray());
    }

    /**
     * 修改当前登陆admin信息
     * @return mixed
     */
    public function update_admin()
    {
        $data = UtilService::postMore([
            ['real_name', ''],
            ['head_pic', ''],
            ['pwd', ''],
            ['new_pwd', ''],
            ['conf_pwd', ''],
        ], $this->request);

        $adminInfo = SystemAdminModel::get($this->adminId);
        if (!$adminInfo)
            return $this->fail('管理员信息未查到');
        if (!$data['real_name'])
            return $this->fail('管理员姓名不能为空');
        if ($data['pwd']) {
            if (!password_verify($data['pwd'], $this->adminInfo['pwd']))
                return $this->fail('原始密码错误');
            if (!$data['new_pwd'])
                return $this->fail('请输入新密码');
            if (!$data['conf_pwd'])
                return $this->fail('请输入确认密码');
            if ($data['new_pwd'] != $data['conf_pwd'])
                return $this->fail('两次输入的密码不一致');
            $adminInfo->pwd = password_hash($data['new_pwd'], PASSWORD_BCRYPT);
        }

        $adminInfo->real_name = $data['real_name'];
        $adminInfo->head_pic = $data['head_pic'];
        if ($adminInfo->save())
            return $this->success('修改成功');
        else
            return $this->fail('修改失败');
    }

    /**
     * 退出登陆
     * @return mixed
     */
    public function logout()
    {
        $key = trim(ltrim($this->request->header(Config::get('cookie.token_name')), 'Bearer'));
        $res = CacheService::redisHandler()->delete($key);
        return $this->success();
    }
}
