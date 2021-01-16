<?php
/**
 * Created by PhpStorm.
 * User: lofate
 * Date: 201/12/20
 * Time: 09:08
 */

namespace app\models\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;


class UserPoint extends BaseModel
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
    protected $name = 'user_point';

    use ModelTrait;

    /*
     * 获取积分信息
     * */
    public static function systemPage($where)
    {
        $model = new UserBill();
        if ($where['status'] != '') UserBill::where('status', $where['status']);
        if ($where['title'] != '') UserBill::where('title', 'like', "%$where[status]%");
        $model->where('category', 'integral')->select();
        return $model::page($model);
    }

    /*
     *
     * 异步获取积分信息
     * */
    public static function getpointlist($where)
    {
        $list = self::setWhere($where)
            ->order('a.add_time desc')
            ->field(['a.*', 'b.nickname'])
            ->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->toArray();
        foreach ($list as $key => $item) {
            $list[$key]['add_time'] = date('Y-m-d', $item['add_time']);
        }
        $count = self::setWhere($where)->field(['a.*', 'b.nickname'])->count();
        return ['count' => $count, 'list' => $list];
    }

    //查出要导出数据
    public static function exportData($where)
    {
        $list = self::setWhere($where)->field(['a.*', 'b.nickname'])->select();
        return $list;
    }

    public static function setWhere($where)
    {
        $model = UserBill::alias('a')->join('user b', 'a.uid=b.uid', 'left')->where('a.category', 'integral');
        $time['data'] = '';
        if ($where['start_time'] != '' && $where['end_time'] != '') {
            $time['data'] = $where['start_time'] . ' - ' . $where['end_time'];
        }
        $model = self::getModelTime($time, $model, 'a.add_time');
        if ($where['nickname'] != '') {
            $model = $model->where('b.nickname|b.uid', 'like', "%$where[nickname]%");
        }
        return $model;
    }

    //获取积分头部信息
    public static function getUserpointBadgelist($where)
    {
        return [
            [
                'col' => 6,
                'count' => self::setWhere($where)->sum('a.number'),
                'name' => '总积分(个)',
            ],
            [
                'col' => 6,
                'count' => self::setWhere($where)->where('a.type', 'sign')->group('a.uid')->count(),
                'name' => '客户签到次数(次)',
            ],
            [
                'col' => 6,
                'count' => self::setWhere($where)->where('a.type', 'sign')->sum('a.number'),
                'name' => '签到送出积分(个)',
            ],
            [
                'col' => 6,
                'count' => self::setWhere($where)->where('a.type', 'deduction')->sum('a.number'),
                'name' => '使用积分(个)',
            ],
        ];
    }
}