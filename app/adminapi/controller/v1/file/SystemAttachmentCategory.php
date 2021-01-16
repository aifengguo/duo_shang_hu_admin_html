<?php

namespace app\adminapi\controller\v1\file;

use app\adminapi\controller\AuthController;
use think\Request;
use think\facade\Route as Url;
use crmeb\services\{
    UtilService as Util, FormBuilder as Form
};
use app\models\system\{
    SystemAttachment as SystemAttachmentModel, SystemAttachmentCategory as Category
};

/**
 * 图片分类管理类
 * Class SystemAttachmentCategory
 * @package app\adminapi\controller\v1\file
 */
class SystemAttachmentCategory extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($name = '')
    {
        return $this->success(Category::getAll($name));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $id = $this->request->param('id', 0);
        $formbuider = [];
        $formbuider[] = Form::select('pid', '上级分类', (string)$id)->setOptions(function () {
            $list = Category::getCateList(0,1);
            $options = [['value' => 0, 'label' => '所有分类']];
            foreach ($list as $id => $cateName) {
                $options[] = ['label' => $cateName['html'] . $cateName['name'], 'value' => $cateName['id']];
            }
            return $options;
        })->filterable(1);
        $formbuider[] = Form::input('name', '分类名称');
        return $this->makePostForm('添加分类', $formbuider, Url::buildUrl('/file/category'), 'POST');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $request = app('request');
        $post = $request->post();
        $data['pid'] = $post['pid'];
        $data['name'] = $post['name'];
        if (empty($post['name']))
            return $this->fail('分类名称不能为空！');
        $res = Category::create($data);
        if ($res)
            return $this->success('添加成功');
        else
            return $this->fail('添加失败！');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $Category = Category::get($id);
        if (!$Category) return $this->fail('数据不存在!');
        $formbuider = [];
        $formbuider[] = Form::hidden('id', $id);
        $formbuider[] = Form::select('pid', '上级分类', (string)$Category->getData('pid'))->setOptions(function () use ($id) {
            $list = Category::getCateList();
            $options = [['value' => 0, 'label' => '所有分类']];
            foreach ($list as $id => $cateName) {
                $options[] = ['label' => $cateName['html'] . $cateName['name'], 'value' => $cateName['id']];
            }
            return $options;
        })->filterable(1);
        $formbuider[] = Form::input('name', '分类名称', $Category->getData('name'));
        return $this->makePostForm('编辑分类', $formbuider, Url::buildUrl('/file/category/' . $id), 'PUT');
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
        $data = Util::postMore([
            'pid',
            'name'
        ]);
        if ($data['pid'] == '') return $this->fail('请选择父类');
        if (!$data['name']) return $this->fail('请输入分类名称');
        Category::edit($data, $id);
        return $this->success('分类编辑成功!');
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $chdcount = Category::where('pid', $id)->count();
        if ($chdcount) return $this->fail('有子栏目不能删除');
        $chdcount = SystemAttachmentModel::where('pid', $id)->count();
        if ($chdcount) return $this->fail('栏目内有图片不能删除');
        if (Category::del($id))
            return $this->success('删除成功!');
        else
            return $this->fail('删除失败');
    }
}
