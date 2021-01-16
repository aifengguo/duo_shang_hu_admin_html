<?php

namespace app\adminapi\controller\v1\user;

use app\adminapi\controller\AuthController;
use app\models\user\UserGroup as GroupModel;
use crmeb\services\{FormBuilder as Form, UtilService as Util};
use think\facade\Route as Url;
use crmeb\traits\CurdControllerTrait;

/**
 * 会员设置
 * Class UserLevel
 * @package app\admin\controller\user
 */
class UserGroup extends AuthController
{
    use CurdControllerTrait;

    /**
     * 分组列表
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
        ]);
        return $this->success(GroupModel::getList($where));
    }

    /**
     * 添加/修改分组页面
     * @param int $id
     * @return string
     */
    public function add()
    {
        $data = Util::getMore([
            ['id', 0],
        ]);
        $group = GroupModel::get($data['id']);
        $field = array();
        if (!$group) {
            $title = '添加分组';
            $field[] = Form::input('group_name', '分组名称', '');
        } else {
            $title = '修改分组';
            $field[] = Form::hidden('id', $group->getData('id'));
            $field[] = Form::input('group_name', '分组名称', $group->getData('group_name'));
        }
        return $this->makePostForm($title, $field, Url::buildUrl('/user/user_group/save'), 'POST');
    }

    /**
     *
     * @param int $id
     * @return mixed
     */
    public function save()
    {
        $data = Util::postMore([
            ['id', 0],
            ['group_name', ''],
        ]);
        if ($data['id'] != 0) {
            if (GroupModel::where('id', $data['id'])->update($data)) {
                return $this->success('修改成功');
            } else {
                return $this->fail('修改失败或者您没有修改什么！');
            }
        } else {
            unset($data['id']);
            if ($res = GroupModel::create($data)) {
                return $this->success('添加成功');
            } else {
                return $this->fail('添加失败！');
            }
        }
    }

    /**
     * 删除
     * @param $id
     * @throws \Exception
     */
    public function delete()
    {
        $data = Util::getMore([
            ['id', 0],
        ]);
        if (!$data['id']) return $this->fail('数据不存在');
        if (!GroupModel::be(['id' => $data['id']])) return $this->fail('分组数据不存在');
        if (!GroupModel::where('id', $data['id'])->delete()) {
            return $this->fail(GroupModel::getErrorInfo('删除失败,请稍候再试!'));
        } else {
            return $this->success('删除成功!');
        }
    }
}