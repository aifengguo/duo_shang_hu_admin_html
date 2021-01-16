<?php

namespace app\adminapi\controller\v1\export;

use app\adminapi\controller\AuthController;
use crmeb\services\UtilService as Util;
use app\models\finance\FinanceModel;
use app\models\user\{
    User, UserPoint, UserRecharge as UserRechargeModel
};
use app\models\wechat\WechatUser;
use app\models\store\{
    StoreBargain, StoreCombination, StoreProduct, StoreSeckill, StoreOrder as StoreOrderModel
};
use app\models\system\SystemStore;
use crmeb\repositories\ExportRepositories;

/**
 * 导出excel类
 * Class ExportExcel
 * @package app\adminapi\controller\v1\export
 */
class ExportExcel extends AuthController
{

    /**
     *保存用户资金监控的excel表格
     */
    public function userFinance()
    {
        $where = Util::getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['type', ''],
        ]);
        $data = FinanceModel::exportData($where);
        return $this->success(ExportRepositories::userFinance($data));
    }

    /**
     * 用户佣金
     */
    public function userCommission()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['price_max', ''],
            ['price_min', ''],
            ['excel', '1'],
        ]);
        $data = User::exportData($where);
        return $this->success(ExportRepositories::userCommission($data));
    }

    /**
     * 用户积分
     */
    public function userPoint()
    {
        $where = Util::getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
        ]);
        $data = UserPoint::exportData($where);
        return $this->success(ExportRepositories::userPoint($data));
    }

    /**
     * 用户充值
     */
    public function userRecharge()
    {
        $where = Util::getMore([
            ['data', ''],
            ['paid', ''],
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['excel', '1'],
        ]);
        $data = UserRechargeModel::exportData($where);
        return $this->success(ExportRepositories::userRecharge($data));
    }

    /**
     * 分销管理 用户推广
     */
    public function userAgent()
    {
        $where = Util::getMore([
            ['nickname', ''],
            ['data', ''],
            ['excel', '1'],
            ['page', 1],
            ['limit', 20],
        ]);
        $data = WechatUser::exportAgentData($where);
        return $this->success(ExportRepositories::userAgent($data));
    }

    /**
     * 微信用户导出
     */
    public function wechatUser()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['data', ''],
            ['tagid_list', ''],
            ['groupid', '-1'],
            ['sex', ''],
            ['export', '1'],
            ['subscribe', '']
        ], $this->request);
        $tagidList = explode(',', $where['tagid_list']);
        foreach ($tagidList as $k => $v) {
            if (!$v) {
                unset($tagidList[$k]);
            }
        }
        $tagidList = array_unique($tagidList);
        $where['tagid_list'] = implode(',', $tagidList);
        $data = WechatUser::exportData($where);
        return $this->success(ExportRepositories::wechatUser($data));
    }

    /**
     * 商铺砍价活动导出
     */
    public function storeBargain()
    {
        $where = Util::getMore([
            [['page', 'd'], 1],
            [['limit', 'd'], 20],
            ['status', ''],
            ['store_name', ''],
            ['export', 1],
            ['data', ''],
        ], $this->request);
        $data = StoreBargain::exportData($where);
        return $this->success(ExportRepositories::storeBargain($data));
    }

    /**
     * 商铺拼团导出
     */
    public function storeCombination()
    {
        $where = Util::getMore([
            ['is_show', ''],
            ['store_name', ''],
        ]);
        $data = StoreCombination::exportData($where);
        return $this->success(ExportRepositories::storeCombination($data));
    }

    /**
     * 商铺秒杀导出
     */
    public function storeSeckill()
    {
        $where = Util::getMore([
            ['status', ''],
            ['store_name', '']
        ]);
        $data = StoreSeckill::exportData($where);
        return $this->success(ExportRepositories::storeSeckill($data));
    }

    /**
     * 商铺产品导出
     */
    public function storeProduct()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['store_name', ''],
            ['cate_id', ''],
            ['excel', 1],
            ['type', 1]
        ]);
        $data = StoreProduct::exportData($where);
        return $this->success(ExportRepositories::storeProduct($data));
    }

    /**
     * 订单列表导出
     * @return mixed
     */
    public function storeOrder()
    {
        $where = Util::getMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', 0],
            ['data', ''],
            ['type', ''],
            ['pay_type', ''],
            ['order', ''],
            ['page', 1],
            ['limit', 10],
        ], $this->request);
        $data = StoreOrderModel::exportData($where);
        return $this->success(ExportRepositories::storeOrder($data));
    }

    /**
     * 获取提货点
     * @return mixed
     */
    public function storeMerchant()
    {
        $where = Util::getMore([
            [['keywords', 's'], ''],
            [['type', 'd'], 0],
        ]);
        $data = SystemStore::exportData($where);
        return $this->success(ExportRepositories::storeMerchant($data));
    }
}