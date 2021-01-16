<?php

namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use app\models\store\{
    StoreCategory, StoreCouponIssue, StoreCoupon as CouponModel
};
use crmeb\services\{
    FormBuilder as Form, UtilService as Util
};
use think\facade\Route as Url;
use think\Request;

/**
 * 优惠券制作
 * Class StoreCoupon
 * @package app\adminapi\controller\v1\marketing
 */
class StoreCoupon extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['status', ''],
            ['title', ''],
        ], $this->request);
        $list = CouponModel::systemPage($where);
        return $this->success($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $data = Util::getMore(['type']);//接收参数
        $f[] = Form::input('title', '优惠券名称');
        switch ($data['type']) {
            case 1://品类券
                $f[] = Form::select('category_id', '选择品类')->setOptions(function () {
                    $list = StoreCategory::getTierList(null, 1);
                    $menus = [];
                    foreach ($list as $menu) {
                        $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] ? false : true];
                    }
                    return $menus;
                })->filterable(1)->col(12);
                break;
            case 2://商品券
                $f[] = Form::frameImages('image', '商品', Url::buildUrl('admin/store.StoreProduct/index', array('fodder' => 'image', 'type' => 'many')))->icon('ios-add')->width('60%')->height('550px')->setProps(['srcKey' => 'image']);
                $f[] = Form::hidden('product_id', '');
                break;
        }
        $f[] = Form::number('coupon_price', '优惠券面值', 0)->min(0);
        $f[] = Form::number('use_min_price', '优惠券最低消费', 0)->min(0);
        $f[] = Form::number('coupon_time', '优惠券有效期限', 0)->min(0);
        $f[] = Form::number('sort', '排序')->value(0);
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $f[] = Form::hidden('type', $data['type']);
        return $this->makePostForm('添加优惠券', $f, Url::buildUrl('/marketing/coupon/save'), 'POST');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            ['title', ''],
            ['image', ''],
            ['category_id', ''],
            ['coupon_price', ''],
            ['use_min_price', ''],
            ['coupon_time', ''],
            ['sort', ''],
            ['status', 0],
            ['type', 0]
        ]);
        if ($data['type'] == 1) {
            $this->validate($data, \app\adminapi\validates\marketing\StoreCouponValidate::class, 'type');
        } elseif ($data['type'] == 2) {
            $this->validate($data, \app\adminapi\validates\marketing\StoreCouponValidate::class, 'product');
            $data['product_id'] = implode(',', array_column($data['image'], 'product_id'));
        } else {
            $this->validate($data, \app\adminapi\validates\marketing\StoreCouponValidate::class, 'save');
        }
        $data['add_time'] = time();
        unset($data['image']);
        CouponModel::create($data);
        return $this->success('添加优惠券成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('数据不存在!');
        $data['is_del'] = 1;
        if (!CouponModel::edit($data, $id))
            return $this->fail(CouponModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }

    /**
     * 修改状态 立即失效
     * @param $id
     * @param $status
     * @return mixed
     */
    public function status($id)
    {
        if (!$id) return $this->fail('数据不存在!');
        if (!CouponModel::editIsDel($id))
            return $this->fail(CouponModel::getErrorInfo('修改失败,请稍候再试!'));
        else
            return $this->success('修改成功!');
    }

    /**
     * 发布优惠券表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function issue($id)
    {
        if (!CouponModel::be(['id' => $id, 'status' => 1, 'is_del' => 0]))
            return $this->fail('发布的优惠劵已失效或不存在!');
        $f = [];
        $f[] = Form::input('id', '优惠劵ID', $id)->disabled(1);
        $f[] = Form::dateTimeRange('range_date', '领取时间')->placeholder('不填为永久有效');
        $f[] = Form::radio('is_permanent', '是否限量', 1)->options([['label' => '不限量', 'value' => 1], ['label' => '限量', 'value' => 0]]);
        $f[] = Form::number('count', '发布数量', 0)->min(0)->placeholder('不填或填0,为不限量');
        $f[] = Form::radio('is_type', '赠送活动', 0)->options([['label' => '关闭', 'value' => 0], ['label' => '消费满赠', 'value' => 1], ['label' => '首次关注赠送', 'value' => 2]]);
        $f[] = Form::number('full_reduction', '满赠金额', 0)->min(0)->placeholder('赠送优惠券的最低消费金额');
//        $f[] = Form::radio('is_give_subscribe', '首次关注赠送', 0)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return $this->makePostForm('发布优惠券', $f, Url::buildUrl('/marketing/coupon/issue/' . $id), 'POST');
    }

    /**
     * 发布优惠券
     * @param $id
     * @return mixed
     */
    public function update_issue($id)
    {
        list($_id, $rangeTime, $count, $status, $is_permanent, $full_reduction, $is_give_subscribe, $is_full_give, $is_type) = Util::postMore([
            'id',
            ['range_date', ['', '']],
            ['count', 0],
            ['status', 0],
            ['is_permanent', 0],
            ['full_reduction', 0],
            ['is_give_subscribe', 0],
            ['is_full_give', 0],
            ['is_type', 0]
        ], null, true);
        if ($is_type == 1) {
            $is_full_give = 1;
        } elseif ($is_type == 2) {
            $is_give_subscribe = 1;
        }
        if ($_id != $id) return $this->fail('操作失败,信息不对称');
        if (!$count) $count = 0;
        if (!CouponModel::be(['id' => $id, 'status' => 1, 'is_del' => 0])) return $this->fail('发布的优惠劵已失效或不存在!');
        if (count($rangeTime) != 2) return $this->fail('请选择正确的时间区间');
        list($startTime, $endTime) = $rangeTime;
        if (!$startTime) $startTime = 0;
        if (!$endTime) $endTime = 0;
        if (!$startTime && $endTime) return $this->fail('请选择正确的开始时间');
        if ($startTime && !$endTime) return $this->fail('请选择正确的结束时间');
        if (StoreCouponIssue::setIssue($id, $count, strtotime($startTime), strtotime($endTime), $count, $status, $is_permanent, $full_reduction, $is_give_subscribe, $is_full_give))
            return $this->success('发布优惠劵成功!');
        else
            return $this->fail('发布优惠劵失败!');
    }

    /**
     * 发送优惠券列表
     * @param $id
     */
    public function grant()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['status', ''],
            ['title', ''],
            ['is_del', 0],
        ], $this->request);
        $list = CouponModel::systemPageCoupon($where);
        return $this->success($list);
    }
}
