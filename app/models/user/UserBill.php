<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2019 http://www.crmeb.com All rights reserved.
 * Author: liaofei <136327134@qq.com>
 * Date: 2019/3/27 21:44
 */

namespace app\models\user;

use app\models\store\StoreOrder;
use think\facade\Cache;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 用户消费新增金额明细 model
 * Class UserBill
 * @package app\models\user
 */
class UserBill extends BaseModel
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
    protected $name = 'user_bill';

    use ModelTrait;

    public static function income($title, $uid, $category, $type, $number, $link_id = 0, $balance = 0, $mark = '', $status = 1)
    {
        $pm = 1;
        $add_time = time();
        return self::create(compact('title', 'uid', 'link_id', 'category', 'type', 'number', 'balance', 'mark', 'status', 'pm', 'add_time'));
    }

    public static function expend($title, $uid, $category, $type, $number, $link_id = 0, $balance = 0, $mark = '', $status = 1)
    {
        $pm = 0;
        $add_time = time();
        return self::create(compact('title', 'uid', 'link_id', 'category', 'type', 'number', 'balance', 'mark', 'status', 'pm', 'add_time'));
    }

    /**
     * 积分/佣金 使用记录
     * @param $uid
     * @param $page
     * @param $limit
     * @param string $category
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function userBillList($uid, $page, $limit, $category = 'integral')
    {
        if ($page) {
            $list = self::where('uid', $uid)
                ->where('category', $category)
                ->field('mark,pm,number,add_time')
                ->where('status', 1)
                ->order('add_time DESC')
                ->page((int)$page, (int)$limit)
                ->select();
        } else {
            $list = self::where('uid', $uid)
                ->where('category', $category)
                ->field('mark,pm,number,add_time')
                ->where('status', 1)
                ->order('add_time DESC')
                ->select();
        }
        $list = count($list) ? $list->toArray() : [];
        foreach ($list as &$v) {
            $v['add_time'] = date('Y/m/d H:i', $v['add_time']);
            $v['number'] = floatval($v['number']);
        }
        return $list;
    }

    /**
     * 获取昨日佣金
     * @param $uid
     * @return float
     */
    public static function yesterdayCommissionSum($uid)
    {
        return self::where('uid', $uid)->where('category', 'now_money')->where('type', 'brokerage')->where('pm', 1)
            ->where('status', 1)->whereTime('add_time', 'yesterday')->sum('number');
    }

    /**
     * 获取总佣金
     * @param $uid
     * @return float
     */
    public static function getBrokerage($uid)
    {
        $countInfo = self::where('uid', $uid)->where('category', 'now_money')->where('type', 'brokerage')
            ->where('status', 1)->field('SUM(IF(pm=1,`number`,-`number`)) as number')->select()->toArray();
        return $countInfo['number'] ?? 0;
    }

    /**
     * @param $uid
     * @param string $category
     * @param string $type
     * @param $where
     * @return mixed
     */
    public static function getAdminBrokerage($uid, $category = 'now_money', $type = 'brokerage', $where)
    {
        return self::getModelTime($where, self::where('uid', 'in', $uid)->where('category', $category)
            ->where('type', $type)->where('pm', 1)->where('status', 1))->sum('number');
    }

    /**
     * 获取后台添加的余额
     * @param $uid
     * @return float
     */
    public static function getSystemAdd($uid)
    {
        return self::where('uid', $uid)->where('category', 'now_money')->where('type', 'system_add')->where('pm', 1)
            ->where('status', 1)->sum('number');
    }


    /**
     * 累计充值
     * @param $uid
     * @return float
     */
    public static function getRecharge($uid)
    {
        return self::where('uid', $uid)
            ->where('category', 'now_money')
            ->where('type', 'recharge')
            ->where('pm', 1)
            ->where('status', 1)
            ->sum('number');
    }

    /**
     * 获取用户账单明细
     * @param int $uid 用户uid
     * @param int $page 页码
     * @param int $limit 展示多少条
     * @param int $type 展示类型
     * @return array
     * */
    public static function getUserBillList($uid, $page, $limit, $type)
    {
        if (!$limit) return [];
        $model = self::where('uid', $uid)->where('category', 'now_money')->order('add_time desc')->where('number', '<>', 0)
            ->field('FROM_UNIXTIME(add_time,"%Y-%m") as time,group_concat(id SEPARATOR ",") ids')->group('time');
        switch ((int)$type) {
            case 0:
                $model = $model->where('type', 'in', 'recharge,brokerage,pay_money,system_add,pay_product_refund,system_sub');
                break;
            case 1:
                $model = $model->where('type', 'pay_money');
                break;
            case 2:
                $model = $model->where('type', 'in', 'recharge,system_add');
                break;
            case 3:
                $model = $model->where('type', 'in', 'brokerage,recharge,extract');
                break;
            case 4:
                $model = $model->where('type', 'in', 'extract,recharge');
                break;
        }
        if ($page) $model = $model->page((int)$page, (int)$limit);
        $list = ($list = $model->select()) ? $list->toArray() : [];
        $ids = array_map(function ($item) {
            return $item['ids'];
        }, $list);
        $info = self::where('id', 'in', implode(',', $ids))->order('add_time DESC')->column('FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i") as add_time,title,number,pm', 'id');
        $data = [];
        foreach ($list as $item) {
            $value['time'] = $item['time'];
            $id = explode(',', $item['ids']);
            array_multisort($id, SORT_DESC);
            $value['list'] = array_map(function ($value) use ($info) {
                return $info[$value];
            }, $id);
            array_push($data, $value);
        }
        return $data;
    }

    /**
     * TODO 获取用户记录 按月查找
     * @param $uid $uid  用户编号
     * @param int $page $page 分页起始值
     * @param int $limit $limit 查询条数
     * @param string $category $category 记录类型
     * @param string $type $type 记录分类
     * @return mixed
     */
    public static function getRecordList($uid, $page = 1, $limit = 8, $category = 'now_money', $type = '')
    {
        $uids = User::where('spread_uid', $uid)->column('uid');
        $model = new self;
        $model = $model->alias('b');
        $model = $model->field("FROM_UNIXTIME(b.add_time, '%Y-%m') as time");
        $model = $model->join('StoreOrder o', 'o.id=b.link_id');
        $model = $model->where('o.refund_status', 0);
        $model = $model->where('b.category', $category);
        $model = $model->where(function ($query) use ($uid, $type, $uids) {
            $query->where(function ($query1) use ($uid, $type) {
                $query1->where('b.uid', $uid)->where('b.type', $type);
            })->whereOr(function ($query2) use ($uids, $type) {
                $query2->where('b.uid', 'in', $uids)->where('b.type', 'pay_money');
            });
        });
        $model = $model->group("FROM_UNIXTIME(b.add_time, '%Y-%m')");
        $model = $model->order('time desc');
        $model = $model->page($page, $limit);
        return $model->select();
    }

    /**
     * TODO  按月份查找用户记录
     * @param $uid $uid  用户编号
     * @param int $addTime $addTime 月份
     * @param string $category $category 记录类型
     * @param string $type $type 记录分类
     * @return mixed
     */
    public static function getRecordListDraw($uid, $addTime = 0, $category = 'now_money', $type = '')
    {
        if (!$uid) [];
        $model = new self;
        $model = $model->field("title,FROM_UNIXTIME(add_time, '%Y-%m-%d %H:%i') as time,number,pm");
        $model = $model->where('uid', $uid);
        $model = $model->where("FROM_UNIXTIME(add_time, '%Y-%m')= '{$addTime}'");
        $model = $model->where('category', $category);
        if (strlen(trim($type))) $model = $model->where('type', 'in', $type);
        $model = $model->order('add_time desc');
        $list = $model->select();
        if ($list) return $list->toArray();
        else [];
    }

    /**
     * TODO 获取订单返佣记录
     * @param $uid
     * @param int $addTime
     * @param string $category
     * @param string $type
     * @return mixed
     */
    public static function getRecordOrderListDraw($uid, $addTime = 0, $category = 'now_money', $type = 'brokerage')
    {
        if (!strlen(trim($uid))) return [];
        $uids = User::where('spread_uid', $uid)->column('uid');
        $model = new self;
        $model = $model->alias('b');
        $model = $model->join('StoreOrder o', 'o.id=b.link_id');
        $model = $model->join('User u', 'u.uid=o.uid', 'right');
        $model = $model->where('o.refund_status', 0);
        $model = $model->where(function ($query) use ($uid, $type, $uids) {
            $query->where(function ($query1) use ($uid, $type) {
                $query1->where('b.uid', $uid)->where('b.type', $type);
            })->whereOr(function ($query2) use ($uids, $type) {
                $query2->where('b.uid', 'in', $uids)->where('b.type', 'pay_money');
            });
        });
        $model = $model->where("FROM_UNIXTIME(b.add_time, '%Y-%m')= '{$addTime}'");
        $model = $model->where('b.category', $category);
        $model = $model->where('b.take', 0);
        $model = $model->order('b.add_time desc');
        $model = $model->field("o.order_id,FROM_UNIXTIME(b.add_time, '%Y-%m-%d %H:%i') as time,b.number,u.avatar,u.nickname,b.type");
        $list = $model->select();
        if ($list) return $list->toArray();
        else return [];
    }

    /**
     * TODO 获取用户记录总和
     * @param $uid
     * @param string $category
     * @param string $type
     * @return mixed
     */
    public static function getRecordCount($uid, $category = 'now_money', $type = '', $time = '', $pm = false)
    {
        $model = new self;
        $model = $model->where('uid', $uid);
        $model = $model->where('category', $category);
        $model = $model->where('status', 1);
        if (strlen(trim($type))) $model = $model->where('type', 'in', $type);
        if ($time) $model = $model->whereTime('add_time', $time);
        if ($pm) {
            $model = $model->where('pm', 0);
        }
        return $model->sum('number');
    }

    /**
     * TODO 获取订单返佣记录总数
     * @param $uid
     * @param string $category
     * @param string $type
     * @return mixed
     */
    public static function getRecordOrderCount($uid, $category = 'now_money', $type = 'brokerage')
    {
        $uids = User::where('spread_uid', $uid)->column('uid');
        $model = new self;
        $model = $model->alias('b');
        $model = $model->join('StoreOrder o', 'o.id=b.link_id');
        $model = $model->where('o.refund_status', 0);
        $model = $model->where(function ($query) use ($uid, $type, $uids) {
            $query->where(function ($query1) use ($uid, $type) {
                $query1->where('b.uid', $uid)->where('b.type', $type);
            })->whereOr(function ($query2) use ($uids, $type) {
                $query2->where('b.uid', 'in', $uids)->where('b.type', 'now_money');
            });
        });
        $model = $model->where('b.category', $category);
        $model = $model->where('b.take', 0);
        return $model->count('b.id');
    }

    /**
     * 记录分享次数
     * @param int $uid 用户uid
     * @param int $cd 冷却时间
     * @return Boolean
     * */
    public static function setUserShare($uid, $cd = 300)
    {
        $user = User::where('uid', $uid)->find();
        if (!$user) return self::setErrorInfo('用户不存在！');
        $cachename = 'Share_' . $uid;
        if (Cache::has($cachename)) return false;
        self::income('用户分享记录', $uid, 'share', 'share', 1, 0, 0, date('Y-m-d H:i:s', time()) . ':用户分享');
        Cache::set($cachename, 1, $cd);
        event('UserLevelAfter', [$user]);
        return true;
    }

    //查询积分个人明细
    public static function getOneIntegralList($where)
    {
        return self::setWhereList(
            $where, '',
//            ['deduction','system_add','sign'],
            ['title', 'number', 'balance', 'mark', 'FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time'],
            'integral'
        );
    }

    //设置where条件分页.返回数据
    public static function setWhereList($where, $type = '', $field = [], $category = 'integral')
    {
        $models = self::where('uid', $where['id'])
            ->where('category', $category)
            ->page((int)$where['page'], (int)$where['limit'])
            ->order('id', 'desc')
            ->field($field);
        if (is_array($type)) {
            $models = $models->where('type', 'in', $type);
        } else {
            if (!empty($type)) $models = $models->where('type', $type);
        }
        $list = $models->select();
        $data['count'] = $models->count();
        $data['list'] = count($list) ? $list->toArray() : [];
        return $data;
    }

    //查询个人签到明细
    public static function getOneSignList($where)
    {
        return self::setWhereList(
            $where, 'sign',
            ['title', 'number', 'mark', 'FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time']
        );
    }

    //查询个人余额变动记录
    public static function getOneBalanceChangList($where)
    {
        $list = self::setWhereList(
            $where, '',
//            ['system_add','pay_product','extract','pay_product_refund','system_sub','brokerage','recharge','user_recharge_refund'],
            ['FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time', 'title', 'type', 'mark', 'number', 'balance', 'pm', 'status'],
            'now_money'
        );
        foreach ($list['list'] as &$item) {
            switch ($item['type']) {
                case 'system_add':
                    $item['type'] = '系统添加';
                    break;
                case 'pay_product':
                    $item['type'] = '商品购买';
                    break;
                case 'extract':
                    $item['type'] = '提现';
                    break;
                case 'pay_product_refund':
                    $item['type'] = '退款';
                    break;
                case 'system_sub':
                    $item['type'] = '系统减少';
                    break;
                case 'brokerage':
                    $item['type'] = '系统返佣';
                    break;
                case 'recharge':
                    $item['type'] = '余额充值';
                    break;
                case 'user_recharge_refund':
                    $item['type'] = '系统退款';
                    break;
            }
            $item['pm'] = $item['pm'] == 1 ? '获得' : '支出';
        }
        return $list;
    }

    //获取佣金提现列表
    public static function getExtrctOneList($where, $uid)
    {
        $list = self::setOneWhere($where, $uid)
            ->page((int)$where['page'], (int)$where['limit'])
            ->field('number,link_id,mark,FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i:%s") as _add_time,status')
            ->select();
        count($list) && $list = $list->toArray();
        $count = self::setOneWhere($where, $uid)->count();
        foreach ($list as &$value) {
            $value['order_id'] = StoreOrder::where('order_id', $value['link_id'])->value('order_id');
        }
        return ['data' => $list, 'count' => $count];
    }

    //设置单个用户查询
    public static function setOneWhere($where, $uid)
    {
        $model = self::where('uid', $uid)->where('category', 'now_money')->where('type', 'brokerage');
        $time['data'] = '';
        if (strlen(trim($where['start_time'])) && strlen(trim($where['end_time']))) {
            $time['data'] = $where['start_time'] . ' - ' . $where['end_time'];
            $model = self::getModelTime($time, $model);
        }
        if (strlen(trim($where['nickname']))) {
            $model = $model->where('link_id|mark', 'like', "%$where[nickname]%");
        }
        return $model;
    }

    /**
     * 用户获得总佣金
     * @return float
     */
    public static function getBrokerageCount($where)
    {
        $model = new self;
        $model = self::getModelTime($where, $model, 'add_time');
        $model = $model->where('type', 'brokerage');
        $model = $model->where('category', 'now_money');
        $model = $model->where('status', 1);
        $model = $model->where('pm', 1);
        return $model->sum('number');
    }

    /**
     * 获取用户时间段内返佣金额
     * @param $type
     * @return float
     */
    public static function getBrokeragePrice($type, $uid)
    {
        $model = new self;
        $model = $model->where('category', 'now_money')->where('type', 'brokerage');
        if ($type == 'week') {
            $model = $model->whereWeek('add_time');
        } elseif ($type == 'month') {
            $model = $model->whereMonth('add_time');
        }
        return $model->where('uid', $uid)->where('pm', 1)->sum('number');
    }
}
