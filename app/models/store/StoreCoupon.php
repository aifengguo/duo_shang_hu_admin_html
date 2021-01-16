<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/22
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 优惠券Model
 * Class StoreCoupon
 * @package app\models\store
 */
class StoreCoupon extends BaseModel
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
    protected $name = 'store_coupon';

    use ModelTrait;

    public function getTypeAttr($value)
    {
        $status = [0=>'通用券',1=>'品类券',2=>'商品券'];
        return $status[$value];
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        if ($where['title'] != '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        $model = $model->where('is_del', 0);
        $count = $model->count();
        $model = $model->order('sort desc,id desc');
        $list = $model->page((int)$where['page'], (int)$where['limit'])->select();
        return compact('count', 'list');
    }

    /**
     * 修改状态
     * @param $id
     * @return bool
     */
    public static function editIsDel($id)
    {
        $data['status'] = 0;
        self::beginTrans();
        $res1 = self::edit($data, $id);
        $res2 = false !== StoreCouponUser::where('cid', $id)->update(['is_fail' => 1]);
        $res3 = false !== StoreCouponIssue::where('cid', $id)->update(['status' => -1]);
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 发送优惠券列表
     * @param $where
     * @return array
     */
    public static function systemPageCoupon($where)
    {
        $model = new self;
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        if ($where['title'] != '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        $model = $model->where('is_del', 0);
        $model = $model->where('status', 1);
        $model = $model->order('sort desc,id desc');
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])->select();
        return compact('count', 'list');
    }
}