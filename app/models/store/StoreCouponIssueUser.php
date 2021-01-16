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
 * TODO 优惠券前台用户领取Model
 * Class StoreCouponIssueUser
 * @package app\models\store
 */
class StoreCouponIssueUser extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_coupon_issue_user';

    use ModelTrait;

    public static function addUserIssue($uid, $issue_coupon_id)
    {
        $add_time = time();
        return self::create(compact('uid', 'issue_coupon_id', 'add_time'));
    }

    public static function systemCouponIssuePage($issue_coupon_id, $where)
    {
        $model = self::alias('A')->field('B.nickname,B.avatar,A.add_time')
            ->join('user B', 'A.uid = B.uid')
            ->where('A.issue_coupon_id', $issue_coupon_id);
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                $item['add_time'] = $item['add_time'] == 0 ? '未知' : date('Y/m/d H:i', $item['add_time']);
            });
        return compact('count', 'list');
    }
}