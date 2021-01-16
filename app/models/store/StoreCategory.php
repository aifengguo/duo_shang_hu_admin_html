<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/12
 */

namespace app\models\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\facade\Cache;

/**
 * TODO 商品分类Model
 * Class StoreCategory
 * @package app\models\store
 */
class StoreCategory extends BaseModel
{
    use ModelTrait;
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_category';

    protected function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public static function pidByCategory($pid, $field = '*', $limit = 0)
    {
        $model = self::where('pid', $pid)->where('is_show', 1)->order('sort desc,id desc')->field($field);
        if ($limit) $model->limit($limit);
        return $model->select();
    }

    public static function pidBySidList($pid)
    {
        return self::where('pid', $pid)->field('id,cate_name,pid')->select();
    }

    public static function cateIdByPid($cateId)
    {
        return self::where('id', $cateId)->value('pid');
    }

    /*
     * 获取一级和二级分类
     * @return array
     * */
    public static function getProductCategory($expire = 800)
    {
        if (Cache::has('parent_category')) {
            return Cache::get('parent_category');
        } else {
            $parentCategory = self::pidByCategory(0, 'id,cate_name')->toArray();
            foreach ($parentCategory as $k => $category) {
                $category['child'] = self::pidByCategory($category['id'], 'id,cate_name,pic')->toArray();
                $parentCategory[$k] = $category;
            }
            Cache::set('parent_category', $parentCategory, $expire);
            return $parentCategory;
        }
    }

    /**
     * TODO  获取首页展示的二级分类  排序默认降序
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function byIndexList($limit = 4, $field = 'id,cate_name,pid,pic')
    {
        return self::where('pid', '>', 0)->where('is_show', 1)->field($field)->order('sort DESC')->limit($limit)->select();
    }

    /**
     * 获取子集分类查询条件
     * @return \think\model\relation\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'pid','id')->where('is_show',1)->order('sort DESC,id DESC');
    }

    /**
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function CategoryList($where)
    {
        $data = ($data = self::systemPage($where, true)->order('sort desc,id desc')->select()) && count($data) ? $data->toArray() : [];
        $count = self::systemPage($where, true)->where('id|pid',$where['pid']!=''?$where['pid']:0)->count();
        return ['count' => $count, 'list' => $data];
    }

    /**
     * 查询条件处理
     * @param $where
     * @return array
     */
    public static function systemPage($where, $isAjax = false)
    {
        $model = new self;
        if ($where['is_show'] != '') $model = $model->where('is_show', $where['is_show']);
        if ($where['cate_name'] != '') $model = $model->where('cate_name', 'LIKE', "%$where[cate_name]%");
        if ($where['pid']) $model = $model->where('id|pid', $where['pid']);
        if ($isAjax === true) {
            return $model;
        }
    }

    /**
     * 商品分类隐藏显示
     * @param $id
     * @param $show
     * @return bool
     */
    public static function setCategoryShow($id, $show)
    {
        $count = self::where('id', $id)->count();
        if (!$count) return self::setErrorInfo('参数错误');
        $count = self::where('id', $id)->where('is_show', $show)->count();
        if ($count) return true;
        $pid = self::where('id', $id)->value('pid');
        self::beginTrans();
        $res1 = true;
        $res2 = self::where('id', $id)->update(['is_show' => $show]);
        if (!$pid) {//一级分类隐藏
            $count = self::where('pid', $id)->count();
            if ($count) {
                $count = self::where('pid', $id)->where('is_show', $show)->count();
                $countWhole = self::where('pid', $id)->count();
                if (!$count || $countWhole > $count) {
                    $res1 = self::where('pid', $id)->update(['is_show' => $show]);
                }
            }
        }
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 删除分类
     * @param $id
     * @return bool
     */
    public static function delCategory($id)
    {
        $count = self::where('pid', $id)->count();
        if ($count)
            return self::setErrorInfo('请先删除下级子分类');
        else {
            return self::del($id);
        }
    }

    /**
     * 获取所有分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException\
     */
    public static function getCategoryList()
    {
        $list = self::order('sort desc,id desc')->select()->toArray();
        if ($list) return $list;
        return [];
    }

    /**
     * 分级排序列表
     * @param null $model
     * @param int $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getTierList($model = null, $type = 0)
    {
        if ($model === null) $model = new self();
        if (!$type) return sort_list_tier($model->order('sort desc,id desc')->where('pid', 0)->select()->toArray());
        return sort_list_tier($model->order('sort desc,id desc')->select()->toArray());
    }
}