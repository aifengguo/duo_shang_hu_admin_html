<?php

namespace app\models\article;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\models\article\Article as ArticleModel;

/**
 * TODO 文章分类Model
 * Class ArticleCategory
 * @package app\models\article
 */
class ArticleCategory extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'article_category';

    use ModelTrait;

    /**
     * TODO 获取文章分类
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArticleCategory()
    {
        return self::where('hidden', 0)->where('is_del', 0)->where('status', 1)->where('pid', 0)->order('sort DESC')->field('id,title')->select();
    }

    /**
     * TODO  获取分类字段
     * @param $id $id 编号
     * @param string $field $field 字段
     * @return mixed|string
     */
    public static function getArticleCategoryField($id, $field = 'title')
    {
        if (!$id) return '';
        return self::where('id', $id)->value($field);
    }

    /**
     * @param $cid
     * @param $first
     * @param $limit
     * @param string $field
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function cidByArticleList($cid, $first, $limit, $field = '*')
    {
        $model = new Article();
        if ($cid) $model->where('cid', $cid);
        return $model->field($field)->where('status', 1)->where('hide', 0)->order('sort DESC,add_time DESC')->limit($first, $limit)->select();
    }

    //后台模型

    /**
     * TODO 获取文章分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArticleCategoryList()
    {
        $list = self::where('is_del', 0)->order('sort desc,id desc')->select();
        if ($list) return $list->toArray();
        return [];
    }

    /**
     * TODO 获取文章分类信息
     * @param $id
     * @param string $field
     * @return mixed
     */
    public static function getArticleCategoryInfo($id, $field = 'title')
    {
        $model = new self;
        if ($id) $model = $model->where('id', $id);
        $model = $model->where('is_del', 0);
        $model = $model->where('status', 1);
        return $model->column($field, 'id');
    }

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getTierList($model = null)
    {
        if ($model === null) $model = new self();
        return sort_list_tier($model->where('is_del', 0)->where('status', 1)->select()->toArray());
    }

    /**
     * 获取系统分页数据   分类
     * @param array $where
     * @return array
     */
    public static function systemPage($where = array())
    {
        $model = new self;
        if ($where['title'] !== '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        if ($where['status'] !== '') $model = $model->where('status', $where['status']);
        $model = $model->where('is_del', 0);
        $model = $model->where('hidden', 0);
        $model = $model->order('sort desc,id desc');
        $model = $model->where('pid', 0);
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])->select()->toArray();
        return compact('count', 'list');
    }

    /**
     * 格式化分类
     * @param $menusList
     * @param int $pid
     * @param array $navList
     * @return array
     */
    public static function tidyTree($menusList, $pid = 0, $navList = [])
    {
        foreach ($menusList as $k => $menu) {
//            $menu = $menu->getData();
//            $menu['title']=$menu['name'];
            if ($menu['pid'] == $pid) {
                unset($menusList[$k]);
                $menu['children'] = self::tidyTree($menusList, $menu['id']);
                if ($menu['children']) $menu['expand'] = true;
                $navList[] = $menu;
            }
        }
        return $navList;
    }

    /**
     * 删除分类
     * @param $id
     * @return bool
     */
    public static function delArticleCategory($id)
    {
        if (count(self::getArticle($id, '*')) > 0)
            return self::setErrorInfo('请先删除改分类下的文章!');
        return self::edit(['is_del' => 1], $id, 'id');
    }

    /**
     * 获取分类底下的文章
     * id  分类表中的分类id
     * return array
     * */
    public static function getArticle($id, $field)
    {
        $res = ArticleModel::where('status', 1)->where('hide', 0)->column($field, 'id');
        $new_res = array();
        foreach ($res as $k => $v) {
            $cid_arr = explode(',', $v['cid']);
            if (in_array($id, $cid_arr)) {
                $new_res[$k] = $res[$k];
            }
        }
        return $new_res;
    }

    public static function getSonCate($arr, $where)
    {
        $model = new self;
        if ($where['title'] !== '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        if ($where['status'] !== '') $model = $model->where('status', $where['status']);
        $model = $model->where('is_del', 0);
        $model = $model->where('hidden', 0);
        foreach ($arr as $key => $value) {
            $son_class = $model->where('pid', $value['id'])->select()->toArray();
            if ($son_class) array_splice($arr, $key, 0, $son_class);
        }
        return $arr;
    }
}