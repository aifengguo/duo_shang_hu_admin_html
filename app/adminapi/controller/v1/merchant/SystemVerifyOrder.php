<?php

namespace app\adminapi\controller\v1\merchant;

use app\adminapi\controller\AuthController;
use app\models\store\StoreOrder;
use crmeb\services\UtilService as Util;

class SystemVerifyOrder extends AuthController
{
    /**
     * 获取核销订单列表
     * return json
     */
    public function list()
    {
        $where = Util::getMore([
            ['data', ''],
            ['real_name', ''],
            ['store_id', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        return $this->success(StoreOrder::VerifyOrder($where));
    }

    /**
     * 获取核销订单头部
     * @return mixed
     */
    public function getVerifyBadge()
    {
        $where = Util::postMore([
            ['real_name', ''],
            ['is_del', 0],
            ['data', ''],
            ['store_id', ''],
        ]);
        return $this->success(StoreOrder::getVerifyBadge($where));
    }

    /**
     * 订单列表推荐人详细
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function order_spread_user($uid)
    {
        return $this->success(StoreOrder::order_spread_user($uid));
    }
}