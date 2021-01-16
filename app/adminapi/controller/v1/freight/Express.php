<?php

namespace app\adminapi\controller\v1\freight;

use app\models\system\Express as ExpressModel;
use app\adminapi\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use crmeb\services\UtilService as Util;
use think\facade\Route as Url;
use think\Request;

class Express extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $params = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['keyword', '']
        ], $this->request);
        $list = ExpressModel::systemPage($params);
        return $this->success($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $formbuider = [
            Form::input('name', '公司名称')->required('公司名称名称必填'),
            Form::input('code', '编码'),
            Form::number('sort', '排序', 0),
            Form::radio('is_show', '是否启用', 1)->options([['value' => 0, 'label' => '隐藏'], ['value' => 1, 'label' => '启用']]),
        ];
        return $this->makePostForm('添加物流公司', $formbuider, Url::buildUrl('/freight/express'), 'POST');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'name',
            'code',
            ['sort', 0],
            ['is_show', 0]]);
        if (!$data['name']) return $this->fail('请输入公司名称');
        ExpressModel::create($data);
        return $this->success('添加公司成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $menu = ExpressModel::get($id);
        if (!$menu) return $this->fail('数据不存在!');
        $formbuider = [
            Form::input('name', '公司名称', $menu['name']),
            Form::input('code', '编码', $menu['code']),
            Form::number('sort', '排序', $menu['sort']),
            Form::radio('is_show', '是否启用', $menu['is_show'])->options([['value' => 0, 'label' => '隐藏'], ['value' => 1, 'label' => '启用']])
        ];
        return $this->makePostForm('编辑物流公司', $formbuider, Url::buildUrl('/freight/express/' . $id), 'PUT');
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'name',
            'code',
            ['sort', 0],
            ['is_show', 0]]);
        if (!$data['name']) return $this->fail('请输入公司名称');
        if (!ExpressModel::get($id)) return $this->fail('编辑的记录不存在!');
        ExpressModel::edit($data, $id);
        return $this->success('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('参数错误，请重新打开');
        $res = ExpressModel::destroy($id);
        if (!$res)
            return $this->fail(ExpressModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }

    /**
     * 修改状态
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function set_status($id = 0, $status = '')
    {
        if ($status == '' || $id == 0) return $this->fail('参数错误');
        ExpressModel::where(['id' => $id])->update(['is_show' => $status]);

        return $this->success($status == 0 ? '隐藏成功' : '显示成功');
    }
}
