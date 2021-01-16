<?php
/**
 * Created by PhpStorm.
 * User: lofate
 * Date: 2019/11/28
 * TIME: 10:16
 */

namespace app\adminapi\controller\v1\finance;

use app\models\finance\FinanceModel;
use app\models\user\{
    User, UserBill
};
use app\adminapi\controller\AuthController;
use crmeb\services\UtilService as Util;

class Finance extends AuthController
{
    /**
     * 筛选类型
     */
    public function bill_type()
    {
        $list = UserBill::where('type', 'not in', ['gain', 'system_sub', 'deduction', 'sign'])
            ->where('category', 'not in', 'integral')
            ->field(['title', 'type'])
            ->group('type')
            ->distinct(true)
            ->select();
        return $this->success(compact('list'));
    }

    /**
     * 资金记录
     */
    public function list()
    {
        $where = Util::getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['limit', 20],
            ['page', 1],
            ['type', ''],
        ]);
        return $this->success(FinanceModel::getBillList($where));
    }

    /**
     * 佣金记录
     */
    public function get_commission_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['price_max', ''],
            ['price_min', ''],
            ['excel', ''],
        ]);
        return $this->success(User::getCommissionList($where));
    }

    /**
     * 佣金详情用户信息
     */
    public function user_info($id = '')
    {
        if ($id == '') return $this->fail('缺少参数');
        $user_info = User::userInfo($id);
        return $this->success(compact('user_info'));
    }

    /**
     * 佣金提现记录个人列表
     */
    public function get_extract_list($id = '')
    {
        if ($id == '') return $this->fail('缺少参数');
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', '']
        ]);
        return $this->success(UserBill::getExtrctOneList($where, $id));
    }

}
