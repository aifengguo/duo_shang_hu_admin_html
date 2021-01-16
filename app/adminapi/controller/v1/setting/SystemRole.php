<?php

namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use app\models\system\{SystemMenus, SystemRole as RoleModel};
use crmeb\services\UtilService as Util;
use think\Request;

class SystemRole extends AuthController
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
            ['role_name', ''],
        ], $this->request);
        $where['level'] = $this->adminInfo['level'];
//        $where['level'] = -1;
        $list = RoleModel::systemPage($where);
        return $this->success($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $menus = $this->adminInfo['level'] == 0 ? SystemMenus::ruleList() : SystemMenus::rolesByRuleList($this->adminInfo['roles']);
        return $this->success(compact('menus'));
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request, $id)
    {
        $data = Util::postMore([
            'role_name',
            ['status', 0],
            ['checked_menus', [], '', 'rules']
        ]);
        if (!$data['role_name']) return $this->fail('请输入身份名称');
        if (!is_array($data['rules']) || !count($data['rules']))
            return $this->fail('请选择最少一个权限');
        $data['rules'] = implode(',', $data['rules']);
        if ($id) {
            if (!RoleModel::edit($data, $id)) return $this->fail('修改失败!');
            \think\facade\Cache::clear();
            return $this->success('修改成功!');
        } else {
            $data['level'] = $this->adminInfo['level'] + 1;
            if (!RoleModel::create($data)) return $this->fail('添加身份失败!');
            \think\facade\Cache::clear();
            return $this->success('添加身份成功!');
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $role = RoleModel::get($id);
        $menus = $this->adminInfo['level'] == 0 ? SystemMenus::ruleList() : SystemMenus::rolesByRuleList($this->adminInfo['roles']);
        return $this->success(['role' => $role, 'menus' => $menus]);
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!RoleModel::del($id))
            return $this->fail(RoleModel::getErrorInfo('删除失败,请稍候再试!'));
        else {
            \think\facade\Cache::clear();
            return $this->success('删除成功!');
        }
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if (!$id) {
            return $this->fail('缺少参数');
        }
        $role = RoleModel::get($id);
        if (!$role) {
            return $this->fail('没有查到此身份');
        }
        $role->status = $status;
        if ($role->save()) {
            \think\facade\Cache::clear();
            return $this->success('修改成功');
        } else {
            return $this->fail('修改失败');
        }
    }
}
