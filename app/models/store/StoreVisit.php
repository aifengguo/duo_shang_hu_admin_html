<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\models\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\models\user\User;

/**
 * 商品浏览分析
 * Class StoreVisit
 * @package app\models\store
 */
class StoreVisit extends BaseModel
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
    protected $name = 'store_visit';

    use ModelTrait;

    /**
     * @param $date
     * @param array $class
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getVisit($date, $class = [])
    {
        $model = new self();
        switch ($date) {
            case null:
            case 'today':
            case 'week':
            case 'year':
                if ($date == null) $date = 'month';
                $model = $model->whereTime('add_time', $date);
                break;
            case 'quarter':
                list($startTime, $endTime) = User::getMonth('n');
                $model = $model->where('add_time', '>', $startTime);
                $model = $model->where('add_time', '<', $endTime);
                break;
            default:
                list($startTime, $endTime) = explode('-', $date);
                $model = $model->where('add_time', '>', strtotime($startTime));
                $model = $model->where('add_time', '<', strtotime($endTime));
                break;
        }
        $list = $model->group('type')->field('sum(count) as sum,product_id,cate_id,type,content')->order('sum desc')->limit(0, 10)->select()->toArray();
        $view = [];
        foreach ($list as $key => $val) {
            $now_list['name'] = $val['type'] == 'viwe' ? '浏览量' : '搜索';
            $now_list['value'] = $val['sum'];
            $now_list['class'] = isset($class[$key]) ? $class[$key] : '';
            $view[] = $now_list;
        }
        if (empty($list)) {
            $view = [['name' => '暂无数据', 'value' => 100, 'class' => '']];
        }
        return $view;
    }

    /**
     * 获取查看拼团商品人数
     * @param int $combinationId
     * @param string $productType
     * @return mixed
     */
    public static function getVisitPeople($combinationId = 0, $productType = 'combination')
    {
        $model = new self();
        $model = $model->where('product_id', $combinationId);
        $model = $model->where('product_type', $productType);
        return $model->count();
    }

    /**
     *  设置浏览信息
     * @param $uid
     * @param int $product_id
     * @param int $cate
     * @param string $type
     * @param string $content
     * @param int $min
     */
    public static function setView($uid, $product_id = 0, $cate = 0, $type = '', $content = '', $min = 20)
    {
        $model = new self();
        $view = $model->where('uid', $uid)->where('product_id', $product_id)->field('count,add_time,id')->find();
        if ($view && $type != 'search') {
            $time = time();
            if (($view['add_time'] + $min) < $time) {
                $model->where(['id' => $view['id']])->update(['count' => $view['count'] + 1, 'add_time' => time()]);
            }
        } else {
            $cate = explode(',', $cate)[0];
            $model->insert([
                'add_time' => time(),
                'count' => 1,
                'product_id' => $product_id,
                'cate_id' => $cate,
                'type' => $type,
                'uid' => $uid,
                'content' => $content
            ]);
        }
    }
}