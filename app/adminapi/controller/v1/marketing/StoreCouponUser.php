<?php

namespace app\adminapi\controller\v1\marketing;

use app\models\store\StoreCoupon as CouponModel;
use app\adminapi\controller\AuthController;
use crmeb\services\UtilService as Util;
use app\models\store\StoreCouponUser as CouponUserModel;

/**
 * 优惠券发放记录控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class StoreCouponUser extends AuthController
{

    /**
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['page',1],
            ['limit',20],
            ['status', ''],
//            ['is_fail', ''],
            ['coupon_title', ''],
            ['nickname', ''],
        ], $this->request);
        $list = CouponUserModel::systemPage($where);
        return $this->success($list);
    }

    /**
     * 发放优惠券到指定个人
     * @param $id
     * @param $uid
     * @return \think\response\Json
     */
    public function grant(){
        $data = Util::postMore([
            'id',
            'uid'
        ]);
        if(!$data['id']) return $this->fail('数据不存在!');
        $coupon = CouponModel::get($data['id'])->toArray();
        if(!$coupon) return $this->fail('数据不存在!');
        $user = explode(',',$data['uid']);
        if(!CouponUserModel::setCoupon($coupon,$user))
            return $this->fail(CouponUserModel::getErrorInfo('发放失败,请稍候再试!'));
        else
            return $this->success('发放成功!');

    }
}
