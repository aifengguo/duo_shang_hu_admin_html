<?php

namespace app\adminapi\controller\v1\product;

use app\adminapi\controller\AuthController;
use app\models\article\ArticleCategory;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\UtilService as Util;
use think\Request;
use app\models\store\StoreCategory as CategoryModel;
use think\facade\Route as Url;

/**
 * 商品分类控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class StoreCategory extends AuthController
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
            ['is_show', ''],
            ['pid', ''],
            ['cate_name', ''],
            ['type', 0]
        ]);
        if ($where['type']) {
            $data1 = CategoryModel::getCategoryList();
            $list = ArticleCategory::tidyTree($data1);
        } else {
            $list = CategoryModel::CategoryList($where);
            if ($where['pid'] == '' || $where['cate_name'] == '') {
//                $list['list'] = ArticleCategory::tidyTree($list['list']);
                $list['list'] = array_slice(ArticleCategory::tidyTree($list['list']), ((int)$where['page'] - 1) * $where['limit'], $where['limit']);
            }

        }
        return $this->success($list);
    }

    /**
     * 树形列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function tree_list($type)
    {
        $list = CategoryModel::getTierList(null, $type);
        return $this->success($list);
    }

    /**
     * 修改状态
     * @param string $is_show
     * @param string $id
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && $this->fail('缺少参数');
        if (CategoryModel::setCategoryShow($id, (int)$is_show)) {
            CacheService::delete('CATEGORY');
            return $this->success($is_show == 1 ? '显示成功' : '隐藏成功');
        } else {
            return $this->fail(CategoryModel::getErrorInfo($is_show == 1 ? '显示失败' : '隐藏失败'));
        }
    }

    /**
     * 快速编辑
     * @param string $field
     * @param string $id
     * @param string $value
     */
    public function set_category($id)
    {
        $data = Util::postMore([
            ['field', 'cate_name'],
            ['value', '']
        ]);
        $data['field'] == '' || $id == '' || $data['value'] == '' && $this->fail('缺少参数');
        if (CategoryModel::where('id', $id)->update([$data['field'] => $data['value']])) {
            CacheService::delete('CATEGORY');
            return $this->success('保存成功');
        } else {
            return $this->fail('保存失败');

        }
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $field = [
            Form::select('pid', '父级')->setOptions(function () {
                $list = CategoryModel::getTierList(null, 0);
                $menus = [['value' => 0, 'label' => '顶级菜单']];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('cate_name', '分类名称'),
            Form::frameImageOne('pic', '分类图标(180*180)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pic')))->icon('ios-add')->width('60%')->height('435px'),
            Form::number('sort', '排序')->value(0),
            Form::radio('is_show', '状态', 1)->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]])
        ];
        return $this->makePostForm('添加分类', $field, Url::buildUrl('/product/category'), 'POST');
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
            'pid',
            'cate_name',
            ['pic', []],
            'sort',
            ['is_show', 0]
        ], $request);
        if ($data['pid'] == '') return $this->fail('请选择父类');
        if (!$data['cate_name']) return $this->fail('请输入分类名称');
        if (count($data['pic']) < 1) return $this->fail('请上传分类图标');
        if ($data['sort'] < 0) $data['sort'] = 0;
        $data['pic'] = $data['pic'][0];
        $data['add_time'] = time();
        CategoryModel::create($data);
        CacheService::delete('CATEGORY');
        return $this->success('添加分类成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $c = CategoryModel::get($id);
        if (!$c) return $this->fail('数据不存在!');
        $field = [
            Form::select('pid', '父级', (string)$c->getData('pid'))->setOptions(function () use ($id) {
                $list = CategoryModel::getTierList(CategoryModel::where('id', '<>', $id), 0);
//                $list = (Util::sortListTier(CategoryModel::where('id','<>',$id)->select()->toArray(),'顶级','pid','cate_name'));
                $menus = [['value' => 0, 'label' => '顶级菜单']];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('cate_name', '分类名称', $c->getData('cate_name')),
            Form::frameImageOne('pic', '分类图标', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pic')), $c->getData('pic'))->icon('ios-add')->width('60%')->height('435px'),
            Form::number('sort', '排序', $c->getData('sort')),
            Form::radio('is_show', '状态', $c->getData('is_show'))->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]])
        ];
        return $this->makePostForm('编辑分类', $field, Url::buildUrl('/product/category/' . $id), 'PUT');
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
            'pid',
            'cate_name',
            ['pic', []],
            'sort',
            ['is_show', 0]
        ], $request);
        if ($data['pid'] == '') return $this->fail('请选择父类');
        if (!$data['cate_name']) return $this->fail('请输入分类名称');
        if (count($data['pic']) < 1) return $this->fail('请上传分类图标');
        if ($data['sort'] < 0) $data['sort'] = 0;
        $data['pic'] = $data['pic'][0];
        CategoryModel::edit($data, $id);
        CacheService::delete('CATEGORY');
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
        if (!CategoryModel::delCategory($id)) {
            CacheService::delete('CATEGORY');
            return $this->fail(CategoryModel::getErrorInfo('删除失败,请稍候再试!'));
        } else {

            return $this->success('删除成功!');
        }
    }
}
