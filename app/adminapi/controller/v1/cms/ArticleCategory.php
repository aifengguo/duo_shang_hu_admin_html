<?php

namespace app\adminapi\controller\v1\cms;

use app\adminapi\controller\AuthController;
use think\Request;
use think\facade\Route as Url;
use crmeb\services\{CacheService, FormBuilder as Form, UtilService as Util};
use app\models\article\{
    ArticleCategory as ArticleCategoryModel, Article as ArticleModel
};

/**
 * 文章分类管理
 * Class ArticleCategory
 * @package app\adminapi\controller\v1\cms
 */
class ArticleCategory extends AuthController
{
    /**
     * 显示资源列表
     * @param $type 0-列表形式 1-树形形式
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 15],
            ['status', ''],
            ['title', ''],
            ['type', 0]
        ], $this->request);
        $type = $where['type'];
        unset($where['type']);
        if ($type) {
            $data1 = ArticleCategoryModel::getArticleCategoryList();
            $list = ArticleCategoryModel::tidyTree($data1);
        } else {
            //查出顶级分类列表 分页根据顶级分类
            $list = ArticleCategoryModel::systemPage($where);
        }
        return $this->success($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $f = array();
//        $f[] = Form::select('pid', '父级id')->setOptions(function () {
//            $list = ArticleCategoryModel::getTierList();
//            $menus[] = ['value' => 0, 'label' => '顶级分类'];
//            foreach ($list as $menu) {
//                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['title']];
//            }
//            return $menus;
//        })->filterable(1);
        $f[] = Form::input('title', '分类名称');
        $f[] = Form::input('intr', '分类简介')->type('textarea');
        $f[] = Form::frameImageOne('image', '分类图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')))->icon('ios-add')->width('60%')->height('435px');
        $f[] = Form::number('sort', '排序', 0);
        $f[] = Form::radio('status', '状态', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return $this->makePostForm('添加分类', $f, Url::buildUrl('/cms/category'), 'POST');
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
            'title',
            ['pid', 0],
            'intr',
            ['new_id', []],
            ['image', []],
            ['sort', 0],
            'status',]);
        if (!$data['title']) return $this->fail('请输入分类名称');
        if (count($data['image']) != 1) return $this->fail('请选择分类图片，并且只能上传一张');
        if ($data['sort'] < 0) return $this->fail('排序不能是负数');
        $data['add_time'] = time();
        $data['image'] = $data['image'][0];
        $new_id = $data['new_id'];
        unset($data['new_id']);
        $res = ArticleCategoryModel::create($data);
        if (!ArticleModel::saveBatchCid($res['id'], implode(',', $new_id))) return $this->fail('文章分类添加失败');
        CacheService::delete('ARTICLE_CATEGORY');
        return $this->success('添加分类成功!');
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
        if (!$id) return $this->fail('参数错误');
        $article = ArticleCategoryModel::get($id)->getData();
        if (!$article) return $this->fail('数据不存在!');
        $f = array();
        $f[] = Form::select('pid', '父级id', (string)$article['pid'])->setOptions(function () {
            $list = ArticleCategoryModel::getTierList();
            $menus[] = ['value' => 0, 'label' => '顶级分类'];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['title']];
            }
            return $menus;
        })->filterable(1);
        $f[] = Form::input('title', '分类名称', $article['title']);
        $f[] = Form::input('intr', '分类简介', $article['intr'])->type('textarea');
        $f[] = Form::frameImageOne('image', '分类图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $article['image'])->icon('ios-add')->width('60%')->height('435px');
        $f[] = Form::number('sort', '排序', 0);
        $f[] = Form::radio('status', '状态', $article['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return $this->makePostForm('编辑分类', $f, Url::buildUrl('/cms/category/' . $id), 'PUT');
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
            'title',
            'intr',
            ['image', []],
            ['sort', 0],
            'status',]);
        if (!$data['title']) return $this->fail('请输入分类名称');
        if (count($data['image']) != 1) return $this->fail('请选择分类图片，并且只能上传一张');
        if ($data['sort'] < 0) return $this->fail('排序不能是负数');
        $data['image'] = $data['image'][0];
        if (!ArticleCategoryModel::get($id)) return $this->fail('编辑的记录不存在!');
        ArticleCategoryModel::edit($data, $id);
        CacheService::delete('ARTICLE_CATEGORY');
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
        $res = ArticleCategoryModel::delArticleCategory($id);
        CacheService::delete('ARTICLE_CATEGORY');
        if (!$res)
            return $this->fail(ArticleCategoryModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return $this->fail('参数错误');
        ArticleCategoryModel::where(['id' => $id])->update(['status' => $status]);
        CacheService::delete('ARTICLE_CATEGORY');
        return $this->success($status == 0 ? '隐藏成功' : '显示成功');
    }
}
