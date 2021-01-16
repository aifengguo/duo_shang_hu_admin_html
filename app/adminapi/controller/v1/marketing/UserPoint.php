<?php

namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use crmeb\services\UtilService as Util;
use app\models\user\UserPoint as UserPointModel;

/**
 * 积分控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class UserPoint extends AuthController
{

    /**
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['page', 1],
            ['limit', 10],
        ]);
        $list = UserPointModel::getpointlist($where);
        return $this->success($list);
    }

    //获取积分日志头部信息
    public function integral_statistics()
    {
        $where = Util::getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
        ]);
        $res = UserPointModel::getUserpointBadgelist($where);
        return $this->success(compact('res'));
    }

}
