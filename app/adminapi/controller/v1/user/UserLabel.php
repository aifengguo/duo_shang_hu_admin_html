<?php

namespace app\adminapi\controller\v1\user;

use app\adminapi\controller\AuthController;
use app\models\user\UserLabel as LabelModel;
use crmeb\services\{FormBuilder as Form, UtilService as Util};
use think\facade\Route as Url;
use crmeb\traits\CurdControllerTrait;

/**
 * 用户标签控制器
 * Class UserLabel
 * @package app\adminapi\controller\v1\user
 */
class UserLabel extends AuthController
{
    use CurdControllerTrait;

    /**
     * 标签列表
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
        ]);
        return $this->success(LabelModel::getList($where));
    }

    /**
     * 添加修改标签表单
     * @return mixed
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function add()
    {
        list($id) = Util::getMore([
            ['id', 0],
        ], $this->request, true);
        $label = LabelModel::get($id);
        $field = array();
        if (!$label) {
            $title = '添加标签';
            $field[] = Form::input('label_name', '标签名称', '');
        } else {
            $title = '修改标签';
            $field[] = Form::hidden('id', $label->getData('id'));
            $field[] = Form::input('label_name', '标签名称', $label->getData('label_name'))->required('请填写标签名称');
        }
        return $this->makePostForm($title, $field, Url::buildUrl('/user/user_label/save'), 'POST');
    }

    /**
     * 保存标签表单数据
     * @param int $id
     * @return mixed
     */
    public function save()
    {
        $data = Util::postMore([
            ['id', 0],
            ['label_name', ''],
        ]);
        if (!$data['label_name'] = trim($data['label_name'])) return $this->fail('会员标签不能为空！');
        if ($data['id'] != 0) {
            if (LabelModel::where('id', $data['id'])->update($data)) {
                return $this->success('修改成功');
            } else {
                return $this->fail('修改失败或者您没有修改什么！');
            }
        } else {
            unset($data['id']);
            if ($res = LabelModel::create($data)) {
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
        list($id) = Util::getMore([
            ['id', 0],
        ], $this->request, true);
        if (!$id) return $this->fail('数据不存在');
        if (!LabelModel::be(['id' => $id])) return $this->fail('分组数据不存在');
        if (!LabelModel::where('id', $id)->delete()) {
            return $this->fail(LabelModel::getErrorInfo('删除失败,请稍候再试!'));
        } else {
            return $this->success('删除成功!');
        }
    }
}