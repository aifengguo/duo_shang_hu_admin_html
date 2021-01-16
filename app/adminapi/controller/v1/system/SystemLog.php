<?php

namespace app\adminapi\controller\v1\system;

use app\models\system\SystemAdmin;
use app\models\system\SystemLog as LogModel;
use app\adminapi\controller\AuthController;
use crmeb\services\UtilService as Util;

/**
 * 管理员操作记录表控制器
 * Class SystemLog
 * @package app\adminapi\controller\v1\system
 */
class SystemLog extends AuthController
{

    /**
     * 显示操作记录
     */
    public function index()
    {
        LogModel::deleteLog();
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['pages', ''],
            ['path', ''],
            ['ip', ''],
            ['admin_id', ''],
            ['data', ''],
        ], $this->request);
        $where['level'] = $this->adminInfo['level'];
        $list = LogModel::systemPage($where);
        return $this->success($list);
    }

    public function search_admin()
    {
        $info = SystemAdmin::getOrdAdmin('id,real_name', $this->adminInfo['level']);
        return $this->success(compact('info'));
    }

}

