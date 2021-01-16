<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/20
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use app\models\wechat\WechatUser as UserModel;
use think\facade\Config;

/**
 * TODO 优惠券发放Model
 * Class StoreCouponUser
 * @package app\models\store
 */
class StoreCouponUser extends BaseModel
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
    protected $name = 'store_coupon_user';

    protected $type = [
        'coupon_price' => 'float',
        'use_min_price' => 'float',
    ];

    protected $hidden = [
        'uid'
    ];

    use ModelTrait;

    /**
     * TODO 获取用户优惠券（全部）
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserAllCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid', $uid)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }

    /**
     * 获取用户优惠券（未使用）
     * @return \think\response\Json
     */
    public static function getUserValidCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid', $uid)->where('status', 0)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }

    /**
     * 获取用户优惠券（已使用）
     * @return \think\response\Json
     */
    public static function getUserAlreadyUsedCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid', $uid)->where('status', 1)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }

    /**
     * 获取用户优惠券（已过期）
     * @return \think\response\Json
     */
    public static function getUserBeOverdueCoupon($uid)
    {
        self::checkInvalidCoupon();
        $couponList = self::where('uid', $uid)->where('status', 2)->order('is_fail ASC,status ASC,add_time DESC')->select()->toArray();
        return self::tidyCouponList($couponList);
    }

    public static function beUsableCoupon($uid, $price)
    {
        return self::where('uid', $uid)->where('is_fail', 0)->where('status', 0)->where('use_min_price', '<=', $price)->find();
    }

    /**
     * 获取用户可以使用的优惠券
     * @param $uid
     * @param $price
     * @return false|\PDOStatement|string|\think\Collection
     */
//    public static function beUsableCouponList($uid, $price = 0)
//    {
//        $list = self::where('uid', $uid)->where('is_fail', 0)->where('status', 0)->where('use_min_price', '<=', $price)->order('coupon_price', 'DESC')->select();
//        $list = count($list) ? $list->hidden(['type', 'status', 'is_fail'])->toArray() : [];
//        foreach ($list as &$item) {
//            $item['add_time'] = date('Y/m/d', $item['add_time']);
//            $item['end_time'] = date('Y/m/d', $item['end_time']);
//        }
//        return $list;
//    }
    public static function beUsableCouponList($uid, $cartId, $price = 0)
    {
        $cartGroup = StoreCart::getUserProductCartListV1($uid, $cartId, 1);
        return self::getUsableCouponList($uid, $cartGroup, $price);
    }

    public static function validAddressWhere($model = null, $prefix = '')
    {
        self::checkInvalidCoupon();
        if ($prefix) $prefix .= '.';
        $model = self::getSelfModel($model);
        return $model->where("{$prefix}is_fail", 0)->where("{$prefix}status", 0);
    }

    public static function checkInvalidCoupon()
    {
        self::where('end_time', '<', time())->where('status', 0)->update(['status' => 2]);
    }

    public static function tidyCouponList($couponList)
    {
        $time = time();
        foreach ($couponList as $k => $coupon) {
            $coupon['_add_time'] = date('Y/m/d', $coupon['add_time']);
            $coupon['_end_time'] = date('Y/m/d', $coupon['end_time']);
            $coupon['use_min_price'] = number_format($coupon['use_min_price'], 2);
            $coupon['coupon_price'] = number_format($coupon['coupon_price'], 2);
            if ($coupon['is_fail']) {
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已失效';
            } else if ($coupon['status'] == 1) {
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已使用';
            } else if ($coupon['status'] == 2) {
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
            } else if ($coupon['add_time'] > $time || $coupon['end_time'] < $time) {
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
            } else {
                if ($coupon['add_time'] + 3600 * 24 > $time) {
                    $coupon['_type'] = 2;
                    $coupon['_msg'] = '可使用';
                } else {
                    $coupon['_type'] = 1;
                    $coupon['_msg'] = '可使用';
                }
            }
            $couponList[$k] = $coupon;
        }
        return $couponList;
    }

    public static function getUserValidCouponCount($uid)
    {
        self::checkInvalidCoupon();
        return self::where('uid', $uid)->where('status', 0)->order('is_fail ASC,status ASC,add_time DESC')->count();
    }

    public static function useCoupon($id)
    {
        return self::where('id', $id)->update(['status' => 1, 'use_time' => time()]);
    }

    public static function addUserCoupon($uid, $cid, $type = 'get')
    {
        $couponInfo = StoreCoupon::find($cid);
        if (!$couponInfo) return self::setErrorInfo('优惠劵不存在!');
        $data = [];
        $data['cid'] = $couponInfo['id'];
        $data['uid'] = $uid;
        $data['coupon_title'] = $couponInfo['title'];
        $data['coupon_price'] = $couponInfo['coupon_price'];
        $data['use_min_price'] = $couponInfo['use_min_price'];
        $data['add_time'] = time();
        $data['end_time'] = $data['add_time'] + $couponInfo['coupon_time'] * 86400;
        $data['type'] = $type;
        return self::create($data);
    }

    //获取个人优惠券列表
    public static function getOneCouponsList($where)
    {
        $list = self::where(['uid' => $where['id']])->page((int)$where['page'], (int)$where['limit'])->select();
        $data['count'] = self::where(['uid' => $where['id']])->count();
        $data['list'] = self::tidyCouponList($list);
        return $data;
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        if ($where['coupon_title'] != '') $model = $model->where('coupon_title', 'LIKE', "%$where[coupon_title]%");
        if ($where['nickname'] != '') {
            $model = $model->whereIn('uid', function ($query) use ($where) {
                $query->name('user')->where('nickname', 'LIKE', "%$where[nickname]%")->field('uid')->select();
            });
        };
        $count = $model->count();
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        $model = $model->order('id desc')->field(['*',"(SELECT `nickname` FROM `{$prefix}user` WHERE `uid` = `{$prefix}store_coupon_user`.`uid`) as nickname"]);
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                $item['type'] = $item['type'] == 'send' ? '后台发放' : '手动领取';
                switch ($item['status']) {
                    case 0:
                        $item['status'] = '未使用';
                        break;
                    case 1:
                        $item['status'] = '已使用';
                        break;
                    case 2:
                        $item['status'] = '已过期';
                        break;
                }
            });
        return compact('count', 'list');
    }


    /**
     * 给用户发放优惠券
     * @param $coupon
     * @param $user
     * @return int|string
     */
    public static function setCoupon($coupon, $user)
    {
        $data = array();
        foreach ($user as $k => $v) {
            $data[$k]['cid'] = $coupon['id'];
            $data[$k]['uid'] = $v;
            $data[$k]['coupon_title'] = $coupon['title'];
            $data[$k]['coupon_price'] = $coupon['coupon_price'];
            $data[$k]['use_min_price'] = $coupon['use_min_price'];
            $data[$k]['add_time'] = time();
            $data[$k]['end_time'] = $data[$k]['add_time'] + $coupon['coupon_time'] * 86400;
        }
        $data_num = array_chunk($data, 30);
        self::beginTrans();
        $res = true;
        foreach ($data_num as $k => $v) {
            $res = $res && self::insertAll($v);
        }
        self::checkTrans($res);
        return $res;
    }

    /**
     * @param $uid
     * @param $cartGroup
     * @param $price
     * @return array
     */
//    public static function getUsableCouponList($uid, $cartGroup, $price)
//    {
//        $model = new self();
//        $list = [];
//        $catePrice = [];
//        $product_all = [];
//        $product_ids = array_unique(array_column($cartGroup['valid'], 'product_id'));
//        if (!empty($product_ids)) {
//            $product_all = StoreProduct::field('id,cate_id')->where('id', 'in', $product_ids)->select()->toArray();
//            if (!empty($product_all)) $product_all = array_combine(array_column($product_all, 'id'), $product_all);
//        }
//        foreach ($cartGroup['valid'] as $value) {
//            if (!empty($value['seckill_id']) || !empty($value['bargain_id']) || !empty($value['combination_id'])) continue;
//            $cate_id = $product_all[$value['product_id']]['cate_id'];
//            if (!isset($catePrice[$cate_id])) $catePrice[$cate_id] = 0;
//            $catePrice[$cate_id] = bcadd(bcmul($value['truePrice'], $value['cart_num'], 2), $catePrice[$cate_id], 2);
//        }
////        var_dump($cartGroup['valid']);die;
//        foreach ($cartGroup['valid'] as $value) {
//            $lst1[] = $model->alias('a')
//                ->join('store_coupon b', 'b.id=a.cid')
//                ->where('a.uid', $uid)
//                ->where('a.is_fail', 0)
//                ->where('a.status', 0)
//                ->where('a.use_min_price', '<=', bcmul($value['truePrice'], $value['cart_num'], 2))
//                ->whereFindinSet('b.product_id', $value['product_id'])
//                ->where('b.type', 2)
//                ->field('a.*,b.type')
//                ->order('a.coupon_price', 'DESC')
//                ->select()
//                ->hidden(['status', 'is_fail'])
//                ->toArray();
//        }
//        $lst2 = [];
//        foreach ($catePrice as $cateIds => $_price) {
//            $cateId = explode(',', $cateIds);
//            $cate_pid_arr = StoreCategory::field('pid')->where('id', 'in', $cateId)->select()->toArray();
//            $cate_pids = [];
//            if (!empty($cate_pid_arr)) {
//                $cate_pids = array_column($cate_pid_arr, 'pid');
//            }
//            $cateId = array_merge($cateId, $cate_pids);
//            $cateId = array_unique($cateId);
//            $where = [];
//            foreach ($cateId as $cate_id) {
////                $where ['b.category_id'] = ['exp','FIND_IN_SET('.$cate_id.', b.category_id)'];
//                $where [] = 'FIND_IN_SET(' . $cate_id . ', b.category_id)';
//            }
//            $coupon = $model->alias('a')
//                ->join('store_coupon b', 'b.id=a.cid')
//                ->where('a.uid', $uid)
//                ->where('a.is_fail', 0)
//                ->where('a.status', 0)
//                ->where('a.use_min_price', '<=', $_price)
////                ->whereFindinSet('b.category_id', $value)
////                ->where(function ($query)use($where){
////                    $query->whereOr($where);
////                })
//                ->where(implode(' or ', $where))
//                ->where('b.type', 1)
//                ->field('a.*,b.type')
//                ->order('a.coupon_price', 'DESC')
//                ->select()
//                ->hidden(['status', 'is_fail'])
//                ->toArray();
//            $lst2 = array_merge($lst2, $coupon);
//        }
//        if (isset($lst1)) {
//            foreach ($lst1 as $value) {
//                if ($value) {
//                    foreach ($value as $v) {
//                        if ($v) {
//                            $list[] = $v;
//                        }
//                    }
//                }
//            }
//        }
//        if (isset($lst2)) {
//            foreach ($lst2 as $value) {
//                if ($value) {
//                    foreach ($value as $v) {
//                        if ($v) {
//                            $list[] = $v;
//                        }
//                    }
//                }
//            }
//        }
//        $lst3 = $model->alias('a')
//            ->join('store_coupon b', 'b.id=a.cid')
//            ->where('a.uid', $uid)
//            ->where('a.is_fail', 0)
//            ->where('a.status', 0)
//            ->where('a.use_min_price', '<=', $price)
//            ->where('b.type', 0)
//            ->field('a.*,b.type')
//            ->order('a.coupon_price', 'DESC')
//            ->select()
//            ->hidden(['status', 'is_fail'])
//            ->toArray();
//        $list = array_merge($list, $lst3);
//        $list = array_unique_fb($list);
//
//        foreach ($list as &$item) {
//            $item['add_time'] = date('Y/m/d', $item['add_time']);
//            $item['end_time'] = date('Y/m/d', $item['end_time']);
//            $item['title'] = $item['coupon_title'];
//        }
//        return $list;
//    }

    public static function getUsableCouponList($uid, $cartGroup, $price)
    {
        $model = new self();
        $list = [];
        $catePrice = [];
        foreach ($cartGroup['valid'] as $value) {
            if (!empty($value['seckill_id']) || !empty($value['bargain_id']) || !empty($value['combination_id'])) continue;
            $value['cate_id'] = StoreProduct::where('id', $value['product_id'])->value('cate_id');
            if (!isset($catePrice[$value['cate_id']])) $catePrice[$value['cate_id']] = 0;
            $catePrice[$value['cate_id']] = bcadd(bcmul($value['truePrice'], $value['cart_num'], 2), $catePrice[$value['cate_id']], 2);
        }
//        var_dump($cartGroup['valid']);die;
        foreach ($cartGroup['valid'] as $value) {
            $lst1[] = $model->alias('a')
                ->join('store_coupon b', 'b.id=a.cid')
                ->where('a.uid', $uid)
                ->where('a.is_fail', 0)
                ->where('a.status', 0)
                ->where('a.use_min_price', '<=', bcmul($value['truePrice'], $value['cart_num'], 2))
                ->whereFindinSet('b.product_id', $value['product_id'])
                ->where('b.type', 2)
                ->field('a.*,b.type')
                ->order('a.coupon_price', 'DESC')
                ->select()
                ->hidden(['status', 'is_fail'])
                ->toArray();
        }

        foreach ($catePrice as $cateIds => $_price) {
            $cateId = explode(',', $cateIds);
            foreach ($cateId as $value) {
                $temp[] = StoreCategory::where('id', $value)->value('pid');
            }
            $cateId = array_merge($cateId, $temp);
            $cateId = array_unique($cateId);
            foreach ($cateId as $value) {
                $lst2[] = $model->alias('a')
                    ->join('store_coupon b', 'b.id=a.cid')
                    ->where('a.uid', $uid)
                    ->where('a.is_fail', 0)
                    ->where('a.status', 0)
                    ->where('a.use_min_price', '<=', $_price)
                    ->whereFindinSet('b.category_id', $value)
                    ->where('b.type', 1)
                    ->field('a.*,b.type')
                    ->order('a.coupon_price', 'DESC')
                    ->select()
                    ->hidden(['status', 'is_fail'])
                    ->toArray();
            }
        }
        if (isset($lst1)) {
            foreach ($lst1 as $value) {
                if ($value) {
                    foreach ($value as $v) {
                        if ($v) {
                            $list[] = $v;
                        }
                    }
                }
            }
        }
        if (isset($lst2)) {
            foreach ($lst2 as $value) {
                if ($value) {
                    foreach ($value as $v) {
                        if ($v) {
                            $list[] = $v;
                        }
                    }
                }
            }
        }
        $lst3 = $model->alias('a')
            ->join('store_coupon b', 'b.id=a.cid')
            ->where('a.uid', $uid)
            ->where('a.is_fail', 0)
            ->where('a.status', 0)
            ->where('a.use_min_price', '<=', $price)
            ->where('b.type', 0)
            ->field('a.*,b.type')
            ->order('a.coupon_price', 'DESC')
            ->select()
            ->hidden(['status', 'is_fail'])
            ->toArray();
        $list = array_merge($list, $lst3);
        $list = array_unique_fb($list);

        foreach ($list as &$item) {
            $item['add_time'] = date('Y/m/d', $item['add_time']);
            $item['end_time'] = date('Y/m/d', $item['end_time']);
            $item['title'] = $item['coupon_title'];
        }
        return $list;
    }

    /**
     * TODO 恢复优惠券
     * @param $id
     * @return StoreCouponUser|bool
     */
    public static function recoverCoupon($id)
    {
        $status = self::where('id', $id)->value('status');
        if ($status) return self::where('id', $id)->update(['status' => 0, 'use_time' => '']);
        else return true;
    }
}