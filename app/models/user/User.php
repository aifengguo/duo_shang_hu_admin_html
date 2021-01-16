<?php


namespace app\models\user;

use app\models\store\StoreOrder;
use app\models\store\StoreCouponUser;
use app\models\system\SystemUserLevel;
use crmeb\services\CacheService;
use think\facade\Session;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\traits\JwtAuthModelTrait;
use think\facade\Db;
use app\models\store\StoreProduct;

/**
 * TODO 用户Model
 * Class User
 * @package app\models\user
 */
class User extends BaseModel
{
    use JwtAuthModelTrait;
    use ModelTrait;

    protected $pk = 'uid';

    protected $name = 'user';

    protected $insert = ['add_time', 'add_ip', 'last_time', 'last_ip'];

    protected $hidden = [
        'add_ip', 'account', 'clean_time', 'last_ip', 'pwd', 'pwd'
    ];

    protected function setAddTimeAttr($value)
    {
        return time();
    }

    protected function setAddIpAttr($value)
    {
        return app('request')->ip();
    }

    protected function setLastTimeAttr($value)
    {
        return time();
    }

    protected function setLastIpAttr($value)
    {
        return app('request')->ip();
    }

    public function systemUserLevel()
    {
        return self::hasOne(SystemUserLevel::class, 'id' ,'level');
    }

    public function userGroup()
    {
        return self::hasOne(UserGroup::class, 'id', 'group_id');
    }
    public function spreadUser()
    {
        return self::hasOne(self::class, 'uid', 'spread_uid');
    }

    public function userLabelRelation()
    {
        return self::hasMany(UserLabelRelation::class, 'uid' ,'uid');
    }

    public static function setWechatUser($wechatUser, $spread_uid = 0)
    {
        return self::create([
            'account' => 'wx' . $wechatUser['uid'] . time(),
            'pwd' => md5(123456),
            'nickname' => $wechatUser['nickname'] ?: '',
            'avatar' => $wechatUser['headimgurl'] ?: '',
            'spread_uid' => $spread_uid,
            'add_time' => time(),
            'add_ip' => app('request')->ip(),
            'last_time' => time(),
            'last_ip' => app('request')->ip(),
            'uid' => $wechatUser['uid'],
            'user_type' => 'wechat'
        ]);

    }


    /**
     * TODO 获取会员是否被清除过的时间
     * @param $uid
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCleanTime($uid)
    {
        $user = self::where('uid', $uid)->field(['add_time', 'clean_time'])->find();
        if (!$user) return 0;
        return $user['clean_time'] ? $user['clean_time'] : $user['add_time'];
    }

    public static function updateWechatUser($wechatUser, $uid)
    {
        $userInfo = self::where('uid', $uid)->find();
        if (!$userInfo) return;
        if ($userInfo->spread_uid) {
            return self::edit([
                'nickname' => $wechatUser['nickname'] ?: '',
                'avatar' => $wechatUser['headimgurl'] ?: '',
                'login_type' => isset($wechatUser['login_type']) ? $wechatUser['login_type'] : $userInfo->login_type,
            ], $uid, 'uid');
        } else {
            $data = [
                'nickname' => $wechatUser['nickname'] ?: '',
                'avatar' => $wechatUser['headimgurl'] ?: '',
                'is_promoter' => $userInfo->is_promoter,
                'login_type' => isset($wechatUser['login_type']) ? $wechatUser['login_type'] : $userInfo->login_type,
                'spread_uid' => 0,
                'spread_time' => 0,
                'last_time' => time(),
                'last_ip' => request()->ip(),
            ];
            //TODO 获取后台分销类型
            $storeBrokerageStatus = sys_config('store_brokerage_statu');
            $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
            if (isset($wechatUser['code']) && $wechatUser['code'] && $wechatUser['code'] != $uid && $uid != self::where('uid', $wechatUser['code'])->value('spread_uid')) {
                if ($storeBrokerageStatus == 1) {
                    $spreadCount = self::where('uid', $wechatUser['code'])->count();
                    if ($spreadCount) {
                        $spreadInfo = self::where('uid', $wechatUser['code'])->find();
                        if ($spreadInfo->is_promoter) {
                            //TODO 只有扫码才可以获得推广权限
//                            if(isset($wechatUser['isPromoter'])) $data['is_promoter'] = $wechatUser['isPromoter'] ? 1 : 0;
                        }
                    }
                }
                $data['spread_uid'] = $wechatUser['code'];
                $data['spread_time'] = time();
            }
            return self::edit($data, $uid, 'uid');
        }
    }

    /**
     * 设置推广关系
     * @param $spread
     * @param $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function setSpread($spread, $uid)
    {
        //当前用户信息
        $userInfo = self::where('uid', $uid)->find();
        if (!$userInfo) return true;
        //当前用户有上级直接返回
        if ($userInfo->spread_uid) return true;
        //没有推广编号直接返回
        if (!$spread) return true;
        if ($spread == $uid) return true;
        if ($uid == self::where('uid', $spread)->value('spread_uid')) return true;
        //TODO 获取后台分销类型
        $storeBrokerageStatus = sys_config('store_brokerage_statu');
        $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
        if ($storeBrokerageStatus == 1) {
            $spreadCount = self::where('uid', $spread)->count();
            if ($spreadCount) {
                $spreadInfo = self::where('uid', $spread)->find();
                if ($spreadInfo->is_promoter) {
                    //TODO 只有扫码才可以获得推广权限
//                            if(isset($wechatUser['isPromoter'])) $data['is_promoter'] = $wechatUser['isPromoter'] ? 1 : 0;
                }
            }
        }
        $data['spread_uid'] = $spread;
        $data['spread_time'] = time();
        return self::edit($data, $uid, 'uid');
    }

    /**
     * 小程序用户添加
     * @param $routineUser
     * @param int $spread_uid
     * @return object
     */
    public static function setRoutineUser($routineUser, $spread_uid = 0)
    {
        self::beginTrans();
        $res1 = true;
        if ($spread_uid) $res1 = self::where('uid', $spread_uid)->inc('spread_count', 1)->update();
//        $storeBrokerageStatu = sys_config('store_brokerage_statu') ? : 1;//获取后台分销类型
        $res2 = self::create([
            'account' => 'rt' . $routineUser['uid'] . time(),
            'pwd' => md5(123456),
            'nickname' => $routineUser['nickname'] ?: '',
            'avatar' => $routineUser['headimgurl'] ?: '',
            'spread_uid' => $spread_uid,
//            'is_promoter'=>$spread_uid || $storeBrokerageStatu != 1 ? 1: 0,
            'spread_time' => $spread_uid ? time() : 0,
            'uid' => $routineUser['uid'],
            'add_time' => $routineUser['add_time'],
            'add_ip' => request()->ip(),
            'last_time' => time(),
            'last_ip' => request()->ip(),
            'user_type' => $routineUser['user_type']
        ]);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res2;
    }

    /**
     * 获得当前登陆用户UID
     * @return int $uid
     */
    public static function getActiveUid()
    {
        $uid = null;
        $uid = Session::get('LoginUid');
        if ($uid) return $uid;
        else return 0;
    }

    /**
     * TODO 查询当前用户信息
     * @param $uid $uid 用户编号
     * @param string $field $field 查询的字段
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserInfo($uid, $field = '')
    {
        if (strlen(trim($field))) $userInfo = self::where('uid', $uid)->field($field)->find();
        else  $userInfo = self::where('uid', $uid)->find();
        if (!$userInfo) return [];
        return $userInfo->toArray();
    }

    /**
     * 判断当前用户是否推广员
     * @param int $uid
     * @return bool
     */
    public static function isUserSpread($uid = 0)
    {
        if (!$uid) return false;
        $status = (int)sys_config('store_brokerage_statu');
        $isPromoter = true;
        if ($status == 1) $isPromoter = self::where('uid', $uid)->value('is_promoter');
        if ($isPromoter) return true;
        else return false;
    }


    /**
     * TODO 一级返佣
     * @param $orderInfo
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function backOrderBrokerage($orderInfo, bool $open = true)
    {
        //TODO 营销产品不返佣金
        if (isset($orderInfo['combination_id']) && $orderInfo['combination_id']) return true;
        if (isset($orderInfo['seckill_id']) && $orderInfo['seckill_id']) return true;
        if (isset($orderInfo['bargain_id']) && $orderInfo['bargain_id']) return true;

        $userInfo = User::getUserInfo($orderInfo['uid']);
        //TODO 当前用户不存在 没有上级 或者 当用用户上级时自己  直接返回
        if (!$userInfo || !$userInfo['spread_uid'] || $userInfo['spread_uid'] == $orderInfo['uid']) return true;
        if (!User::be(['uid' => $userInfo['spread_uid'], 'is_promoter' => 1])) return self::backOrderBrokerageTwo($orderInfo, $open);
        $cartId = is_string($orderInfo['cart_id']) ? json_decode($orderInfo['cart_id'], true) : $orderInfo['cart_id'];
        $brokeragePrice = StoreProduct::getProductBrokerage($cartId);
        //TODO 返佣金额小于等于0 直接返回不返佣金
        if ($brokeragePrice <= 0) return true;
        //TODO 获取上级推广员信息
        $spreadUserInfo = User::getUserInfo($userInfo['spread_uid']);
        //TODO 上级推广员返佣之后的金额
        $balance = bcadd($spreadUserInfo['brokerage_price'], $brokeragePrice, 2);
        $mark = $userInfo['nickname'] . '成功消费' . floatval($orderInfo['pay_price']) . '元,奖励推广佣金' . floatval($brokeragePrice);
        $open && self::beginTrans();
        //TODO 添加推广记录
        $res1 = UserBill::income('获得推广佣金', $userInfo['spread_uid'], 'now_money', 'brokerage', $brokeragePrice, $orderInfo['id'], $balance, $mark);
        //TODO 添加用户余额
        $res2 = self::bcInc($userInfo['spread_uid'], 'brokerage_price', $brokeragePrice, 'uid');
        //TODO 一级返佣成功 跳转二级返佣
        $res = $res1 && $res2 && self::backOrderBrokerageTwo($orderInfo, $open);
        $open && self::checkTrans($res);
//        if($res) return self::backOrderBrokerageTwo($orderInfo);
        return $res;
    }

    /**
     * TODO 二级推广
     * @param $orderInfo
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function backOrderBrokerageTwo($orderInfo, bool $open = true)
    {
        //TODO 获取购买商品的用户
        $userInfo = User::getUserInfo($orderInfo['uid']);
        //TODO 获取上推广人
        $userInfoTwo = User::getUserInfo($userInfo['spread_uid']);
        //TODO 上推广人不存在 或者 上推广人没有上级  或者 当用用户上上级时自己  直接返回
        if (!$userInfoTwo || !$userInfoTwo['spread_uid'] || $userInfoTwo['spread_uid'] == $orderInfo['uid']) return true;
        //TODO 获取后台分销类型  1 指定分销 2 人人分销
        if (!User::be(['uid' => $userInfoTwo['spread_uid'], 'is_promoter' => 1])) return true;
        $cartId = is_string($orderInfo['cart_id']) ? json_decode($orderInfo['cart_id'], true) : $orderInfo['cart_id'];
        $brokeragePrice = StoreProduct::getProductBrokerage($cartId, false);
        //TODO 返佣金额小于等于0 直接返回不返佣金
        if ($brokeragePrice <= 0) return true;
        //TODO 获取上上级推广员信息
        $spreadUserInfoTwo = User::getUserInfo($userInfoTwo['spread_uid']);
        //TODO 获取上上级推广员返佣之后余额
        $balance = bcadd($spreadUserInfoTwo['brokerage_price'], $brokeragePrice, 2);
        $mark = '二级推广人' . $userInfo['nickname'] . '成功消费' . floatval($orderInfo['pay_price']) . '元,奖励推广佣金' . floatval($brokeragePrice);
        $open && self::beginTrans();
        //TODO 添加返佣记录
        $res1 = UserBill::income('获得推广佣金', $userInfoTwo['spread_uid'], 'now_money', 'brokerage', $brokeragePrice, $orderInfo['id'], $balance, $mark);
        //TODO 添加用户余额
        $res2 = self::bcInc($userInfoTwo['spread_uid'], 'brokerage_price', $brokeragePrice, 'uid');
        $res = $res1 && $res2;
        $open && self::checkTrans($res);
        return $res;
    }

    /*
     *  获取推荐人
     * @param int $two_uid
     * @param int $first
     * @param int $limit
     * @return array
     * */
    public static function getSpreadList($uid, $page, $limit)
    {
        $one_uids = self::where('spread_uid', $uid)->column('uid');
        $two_uids = self::whereIn('spread_uid', $one_uids)->where('spread_uid', '<>', 0)->column('uid');
        $uids = array_merge($one_uids, $two_uids);
        $list = self::whereIn('uid', $uids)->field('uid,nickname,real_name,avatar,add_time')->page($page, $limit)->order('add_time DESC')->select()->toArray();
        foreach ($list as $k => $user) {
            $list[$k]['type'] = in_array($user['uid'], $one_uids) ? '一级' : '二级';
            $list[$k]['add_time'] = date('Y-m-d', $user['add_time']);
        }
        $count = self::whereIn('uid', $uids)->count();
        $data['count'] = $count;
        $data['list'] = $list;
        return $data;
    }

    /*
     * 获取某个用户的下级uid
     * @param int $uid 用户uid
     * @return array
     * */
    public static function getOneSpreadUid($uid)
    {
        return self::where('spread_uid', $uid)->column('uid');
    }

    /*
     * 修改个人信息
     * */
    public static function editUser($avatar, $nickname, $uid)
    {
        return self::edit(['avatar' => $avatar, 'nickname' => $nickname], $uid, 'uid');
    }

    /**
     * TODO 获取推广人数 一级
     * @param int $uid
     * @return bool|int|string
     */
    public static function getSpreadCount($uid = 0)
    {
        if (!$uid) return false;
        return self::where('spread_uid', $uid)->count('uid');
    }

    public static function setUserSpreadCount($uid)
    {
        self::where('uid', $uid)->update(['spread_count' => self::getSpreadCount($uid)]);
    }

    /**
     * TODO 获取推广人数 二级
     * @param int $uid
     * @return bool|int|string
     */
    public static function getSpreadLevelCount($uid = 0)
    {
        if (!$uid) return false;
        $uidSubordinate = self::where('spread_uid', $uid)->column('uid');
        if (!count($uidSubordinate)) return 0;
        return self::where('spread_uid', 'IN', implode(',', $uidSubordinate))->count('uid');
    }

    /**
     * 获取用户下级推广人
     * @param int $uid 当前用户
     * @param int $grade 等级  0  一级 1 二级
     * @param string $orderBy 排序
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @return array|bool
     */
    public static function getUserSpreadGrade($uid = 0, $grade = 0, $orderBy = '', $keyword = '', $page = 0, $limit = 20)
    {
        if (!$uid) return [];
        $gradeGroup = [0, 1];
        if (!in_array($grade, $gradeGroup)) return self::setErrorInfo('等级错误');
        $userStair = self::where('spread_uid', $uid)->column('uid');
        if (!count($userStair)) return [];
        if ($grade == 0) return self::getUserSpreadCountList(implode(',', $userStair), $orderBy, $keyword, $page, $limit);
        $userSecondary = self::where('spread_uid', 'in', implode(',', $userStair))->column('uid');
        return self::getUserSpreadCountList(implode(',', $userSecondary), $orderBy, $keyword, $page, $limit);
    }

    /**
     * 获取团队信息
     * @param $uid
     * @param string $orderBy
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getUserSpreadCountList($uid, $orderBy = '', $keyword = '', $page = 0, $limit = 20)
    {
        $model = new self;
        if ($orderBy === '') $orderBy = 'u.add_time desc';
        $model = $model->alias(' u');
        $sql = StoreOrder::where('o.paid', 1)->group('o.uid')->field(['SUM(o.pay_price) as numberCount', 'o.uid', 'o.order_id'])
            ->where('o.is_del', 0)->where('o.is_system_del', 0)->alias('o')->fetchSql(true)->select();
        $model = $model->join("(" . $sql . ") p", 'u.uid = p.uid', 'LEFT');
        $model = $model->where('u.uid', 'IN', $uid);
        $model = $model->field("u.uid,u.nickname,u.avatar,from_unixtime(u.add_time,'%Y/%m/%d') as time,u.spread_count as childCount,u.pay_count as orderCount,p.numberCount");
        if (strlen(trim($keyword))) $model = $model->where('u.nickname|u.phone', 'like', "%$keyword%");
        $model = $model->group('u.uid');
        $model = $model->order($orderBy);
        $model = $model->page($page, $limit);
        $list = $model->select();
        if ($list) return $list->toArray();
        else return [];
    }

    public static function setSpreadUid($uid, $spreadUid)
    {
        // 自己不能绑定自己为上级
        if ($uid == $spreadUid) return false;
        //TODO 获取后台分销类型
        $storeBrokerageStatus = sys_config('store_brokerage_statu');
        $storeBrokerageStatus = $storeBrokerageStatus ? $storeBrokerageStatus : 1;
        if ($storeBrokerageStatus == 1) {
            $spreadCount = self::where('uid', $spreadUid)->count();
            if ($spreadCount) {
                $spreadInfo = self::where('uid', $spreadUid)->find();
                if ($spreadInfo->is_promoter) {
                    //TODO 只有扫码才可以获得推广权限
                    if (isset($wechatUser['isPromoter'])) $data['is_promoter'] = 1;
                }
            }
        }
        return self::where('uid', $uid)->update(['spread_uid' => $spreadUid, 'spread_time' => time()]);
    }

    /**
     * 判断上下级关系是否存在
     * @param $uid
     * @param $spreadUid
     * @return bool|int
     */
    public static function validSpread($uid, $spreadUid)
    {
        if (!$uid || !$spreadUid) return false;
        return self::where('uid', $uid)->where('spread_uid', $spreadUid)->count();
    }

    /**
     * H5用户注册
     * @param $account
     * @param $password
     * @param $spread
     * @return User|\think\Model
     */
    public static function register($account, $password, $spread)
    {
        if (self::be(['account' => $account])) return self::setErrorInfo('用户已存在');
        $phone = $account;
        $data['account'] = $account;
        $data['pwd'] = md5($password);
        $data['phone'] = $phone;
        if ($spread) {
            $data['spread_uid'] = $spread;
            $data['spread_time'] = time();
        }
        $data['real_name'] = '';
        $data['birthday'] = 0;
        $data['card_id'] = '';
        $data['mark'] = '';
        $data['addres'] = '';
        $data['user_type'] = 'h5';
        $data['add_time'] = time();
        $data['add_ip'] = app('request')->ip();
        $data['last_time'] = time();
        $data['last_ip'] = app('request')->ip();
        $data['nickname'] = substr(md5($account . time()), 0, 12);
        $data['avatar'] = $data['headimgurl'] = sys_config('h5_avatar');
        $data['city'] = '';
        $data['language'] = '';
        $data['province'] = '';
        $data['country'] = '';
        self::beginTrans();
        $res2 = WechatUser::create($data);
        $data['uid'] = $res2->uid;
        $res1 = self::create($data);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 密码修改
     * @param $account
     * @param $password
     * @return User|bool
     */
    public static function reset($account, $password)
    {
        if (!self::be(['account' => $account])) return self::setErrorInfo('用户不存在');
        $count = self::where('account', $account)->where('pwd', md5($password))->count();
        if ($count) return true;
        return self::where('account', $account)->update(['pwd' => md5($password)]);
    }

    /**
     * 获取手机号是否注册
     * @param $phone
     * @return bool
     */
    public static function checkPhone($phone)
    {
        return self::be(['account' => $phone]);
    }

    /**
     * 获取推广人
     * @param $data 查询条件
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRankList($data)
    {
        switch ($data['type']) {
            case 'week':
                $startTime = strtotime('this week');
                $endTime = time();
                break;
            case 'month':
                $startTime = strtotime('last month');
                $endTime = time();
                break;
        }
        $list = self::alias('t0')
            ->field('t0.uid,t0.spread_uid,count(t1.spread_uid) AS count,t0.add_time,t0.nickname,t0.avatar')
            ->join('user t1', 't0.uid = t1.spread_uid', 'LEFT')
            ->where('t1.spread_uid', '<>', 0)
            ->order('count desc')
            ->order('t0.uid desc')
            ->where('t1.add_time', 'BETWEEN', [$startTime, $endTime])
            ->page($data['page'], $data['limit'])
            ->group('t0.uid')
            ->select();
        return count($list) ? $list->toArray() : [];
    }

    /**
     * 获取佣金排行
     * @param $data
     * @return array
     */
    public static function brokerageRank($data)
    {

        $model = UserBill::alias('b')->join('user u', 'b.uid = u.uid');
        $model = $model->where('b.category', 'now_money')->where('b.type', 'brokerage');
        switch ($data['type']) {
            case 'week':
                $model = $model->whereWeek('b.add_time');
                break;
            case 'month':
                $model = $model->whereMonth('b.add_time');
                break;
        }
        $users = $model->group('b.uid')
            ->field('b.uid,u.nickname,u.avatar,SUM(IF(pm=1,`number`,-`number`)) as brokerage_price')
            ->order('brokerage_price desc')
            ->page((int)$data['page'], (int)$data['limit'])
            ->select();
        return count($users) ? $users->toArray() : [];
    }

    /**
     * 获取当前用户的佣金排行位置
     * @param $uid
     * @return int
     */
    public static function currentUserRank($type, $brokerage_price)
    {
        $model = self::where('status', 1);
        switch ($type) {
            case 'week':
                $model = $model->whereIn('uid', function ($query) {
                    $query->name('user_bill')->where('category', 'now_money')->where('type', 'brokerage')
                        ->whereWeek('add_time')->field('uid');
                });
                break;
            case 'month':
                $model = $model->whereIn('uid', function ($query) {
                    $query->name('user_bill')->where('category', 'now_money')->where('type', 'brokerage')
                        ->whereMonth('add_time')->field('uid');
                });
                break;
        }
        return $model->where('brokerage_price', '>', $brokerage_price)->count('uid');
    }

    /** 后台 获取用户某个时间段的消费信息
     * @param $where
     * @param string $status
     * @param string $keep
     * @return array|float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function consume($where, $status = '', $keep = '')
    {
        $model = new self;
        $user_id = [];
        if (is_array($where)) {
            if ($where['is_promoter'] != '') $model = $model->where('is_promoter', $where['is_promoter']);
            if ($where['status'] != '') $model = $model->where('status', $where['status']);
            switch ($where['date']) {
                case null:
                case 'today':
                case 'week':
                case 'year':
                    if ($where['date'] == null) {
                        $where['date'] = 'month';
                    }
                    if ($keep) {
                        $model = $model->whereTime('add_time', $where['date'])->whereTime('last_time', $where['date']);
                    } else {
                        $model = $model->whereTime('add_time', $where['date']);
                    }
                    break;
                case 'quarter':
                    $quarter = self::getMonth('n');
                    $startTime = strtotime($quarter[0]);
                    $endTime = strtotime($quarter[1]);
                    if ($keep) {
                        $model = $model->where('add_time', '>', $startTime)->where('add_time', '<', $endTime)->where('last_time', '>', $startTime)->where('last_time', '<', $endTime);
                    } else {
                        $model = $model->where('add_time', '>', $startTime)->where('add_time', '<', $endTime);
                    }
                    break;
                default:
                    //自定义时间
                    if (strstr($where['date'], '-') !== FALSE) {
                        list($startTime, $endTime) = explode('-', $where['date']);
                        $model = $model->where('add_time', '>', strtotime($startTime))->where('add_time', '<', bcadd(strtotime($endTime), 86400, 0));
                    } else {
                        $model = $model->whereTime('add_time', 'month');
                    }
                    break;
            }
        } else {
            if (is_array($status)) {
                $model = $model->where('add_time', '>', $status[0])->where('add_time', '<', $status[1]);
            }
        }
        if ($keep === true) {
            return $model->count();
        }
        if ($status === 'default') {
            return $model->group('from_unixtime(add_time,\'%Y-%m-%d\')')->field('count(uid) num,from_unixtime(add_time,\'%Y-%m-%d\') add_time,uid')->select()->toArray();
        }
        if ($status === 'grouping') {
            return $model->group('user_type')->field('user_type')->select()->toArray();
        }
        $uid = $model->field('uid')->select()->toArray();
        foreach ($uid as $val) {
            $user_id[] = $val['uid'];
        }
        if (empty($user_id)) {
            $user_id = [0];
        }
        if ($status === 'xiaofei') {
            $list = UserBill::where('uid', 'in', $user_id)
                ->group('type')
                ->field('sum(number) as top_number,title')
                ->select()
                ->toArray();
            $series = [
                'name' => isset($list[0]['title']) ? $list[0]['title'] : '',
                'type' => 'pie',
                'radius' => ['40%', '50%'],
                'data' => []
            ];
            foreach ($list as $key => $val) {
                $series['data'][$key]['value'] = $val['top_number'];
                $series['data'][$key]['name'] = $val['title'];
            }
            return $series;
        } else if ($status === 'form') {
            $list = WechatUser::where('uid', 'in', $user_id)->group('city')->field('count(city) as top_city,city')->limit(0, 10)->select()->toArray();
            $count = self::getcount();
            $option = [
                'legend_date' => [],
                'series_date' => []
            ];
            foreach ($list as $key => $val) {
                $num = $count != 0 ? (bcdiv($val['top_city'], $count, 2)) * 100 : 0;
                $t = ['name' => $num . '%  ' . (empty($val['city']) ? '未知' : $val['city']), 'icon' => 'circle'];
                $option['legend_date'][$key] = $t;
                $option['series_date'][$key] = ['value' => $num, 'name' => $t['name']];
            }
            return $option;
        } else {
            $number = UserBill::where('uid', 'in', $user_id)->where('type', 'pay_product')->sum('number');
            return $number;
        }
    }

    /*
     * 获取 用户某个时间段的钱数或者TOP20排行
     *
     * return Array  || number
     */
    public static function getUserSpend($date, $status = '')
    {
        $model = new self();
        $model = $model->alias('A');
        switch ($date) {
            case null:
            case 'today':
            case 'week':
            case 'year':
                if ($date == null) $date = 'month';
                $model = $model->whereTime('A.add_time', $date);
                break;
            case 'quarter':
                list($startTime, $endTime) = User::getMonth('n');
                $model = $model->where('A.add_time', '>', strtotime($startTime));
                $model = $model->where('A.add_time', '<', bcadd(strtotime($endTime), 86400, 0));
                break;
            default:
                list($startTime, $endTime) = explode('-', $date);
                $model = $model->where('A.add_time', '>', strtotime($startTime));
                $model = $model->where('A.add_time', '<', bcadd(strtotime($endTime), 86400, 0));
                break;
        }
        if ($status === true) {
            return $model->join('user_bill B', 'B.uid=A.uid')->where('B.type', 'pay_product')->where('B.pm', 0)->sum('B.number');
        }
        $list = $model->join('user_bill B', 'B.uid=A.uid')
            ->where('B.type', 'pay_product')
            ->where('B.pm', 0)
            ->field('sum(B.number) as totel_number,A.nickname,A.avatar,A.now_money,A.uid,A.add_time')
            ->order('totel_number desc')
            ->limit(0, 20)
            ->select()
            ->toArray();
        if (!isset($list[0]['totel_number'])) {
            $list = [];
        }
        return $list;
    }

    /*
     * 获取 相对于上月或者其他的数据
     *
     * return Array
     */
    public static function getPostNumber($date, $status = false, $field = 'A.add_time', $t = '消费')
    {
        $model = new self();
        if (!$status) $model = $model->alias('A');
        switch ($date) {
            case null:
            case 'today':
            case 'week':
            case 'year':
                if ($date == null) {
                    $date = 'last month';
                    $title = '相比上月用户' . $t . '增长';
                }
                if ($date == 'today') {
                    $date = 'yesterday';
                    $title = '相比昨天用户' . $t . '增长';
                }
                if ($date == 'week') {
                    $date = 'last week';
                    $title = '相比上周用户' . $t . '增长';
                }
                if ($date == 'year') {
                    $date = 'last year';
                    $title = '相比去年用户' . $t . '增长';
                }
                $model = $model->whereTime($field, $date);
                break;
            case 'quarter':
                $title = '相比上季度用户' . $t . '增长';
                list($startTime, $endTime) = User::getMonth('n', 1);
                $model = $model->where($field, '>', $startTime);
                $model = $model->where($field, '<', $endTime);
                break;
            default:
                list($startTime, $endTime) = explode('-', $date);
                $title = '相比' . $startTime . '-' . $endTime . '时间段用户' . $t . '增长';
                $Time = strtotime($endTime) - strtotime($startTime);
                $model = $model->where($field, '>', strtotime($startTime) + $Time);
                $model = $model->where($field, '<', strtotime($endTime) + $Time);
                break;
        }
        if ($status) {
            return [$model->count(), $title];
        }
        $number = $model->join('user_bill B', 'B.uid=A.uid')->where('B.type', 'pay_product')->where('B.pm', 0)->sum('B.number');
        return [$number, $title];
    }

    /**
     * 设置搜索条件
     * @param $where
     * @return $this
     */
    public static function setWhere($where)
    {
        if ($where['order'] != '') {
            $model = self::order(self::setOrder($where['order']));
        } else {
            $model = self::order('u.uid desc');
        }
        if ($where['user_time_type'] == 'visitno' && $where['user_time'] != '') {
            list($startTime, $endTime) = explode('-', $where['user_time']);
            $endTime = strtotime($endTime) + 24 * 3600;
            $model = $model->where("u.last_time < " . strtotime($startTime) . " OR u.last_time > " . $endTime);
        }
        if ($where['user_time_type'] == 'visit' && $where['user_time'] != '') {
            list($startTime, $endTime) = explode('-', $where['user_time']);
            $model = $model->where('u.last_time', '>', strtotime($startTime));
            $model = $model->where('u.last_time', '<', strtotime($endTime) + 24 * 3600);
        }
        if ($where['user_time_type'] == 'add_time' && $where['user_time'] != '') {
            list($startTime, $endTime) = explode('-', $where['user_time']);
            $model = $model->where('u.add_time', '>', strtotime($startTime));
            $model = $model->where('u.add_time', '<', strtotime($endTime) + 24 * 3600);
        }
        if ($where['pay_count'] !== '') {
            if ($where['pay_count'] == '-1')
                $model = $model->where('pay_count', 0);
            else
                $model = $model->where('pay_count', '>', $where['pay_count']);
        }
        if ($where['user_type'] != '') {
            if ($where['user_type'] == 'routine')
                $model = $model->where('w.routine_openid', 'not null');
            else if ($where['user_type'] == 'wechat')
                $model = $model->where('w.openid', 'not null');
            else
                $model = $model->where('u.user_type', $where['user_type']);
        }
        if ($where['country'] != '') {
            if ($where['country'] == 'domestic')
                $model = $model->where('w.country', '中国');
            else if ($where['country'] == 'abroad')
                $model = $model->where('w.country', '<>', '中国');
        }
        if ($where['level'] != '') {
            $model = $model->where('level', $where['level'])->where('clean_time', 0);
        }
        if ($where['group_id'] != '') {
            $model = $model->where('group_id', $where['group_id']);
        }
        if ($where['label_id'] != '') {
            $model = $model->whereIn('u.uid', function ($query) use ($where) {
                $query->name('user_label_relation')->where('label_id', $where['label_id'])->field('uid')->select();
            });
        }
        return $model;
    }

    /**
     * 异步获取当前用户 信息
     * @param $where
     * @return array
     */
    public static function getUserList($where)
    {
        $model = self::setWherePage(self::setWhere($where), $where, ['w.sex', 'w.province', 'w.city', 'u.status', 'u.is_promoter'], ['u.nickname', 'u.uid', 'u.phone'])->alias('u')->join('WechatUser w', 'u.uid=w.uid');
        $labels = UserLabel::column('label_name', 'id');
        $systemUserLevel = SystemUserLevel::column('name', 'id');
        $userGroup = UserGroup::column('group_name', 'id');
        $count = $model->count();
        $list = $model->field('u.*,w.country,w.province,w.city,w.sex,w.unionid,w.openid,w.routine_openid,w.groupid,w.tagid_list,w.subscribe,w.subscribe_time')
            ->with(['spreadUser'=>function($query){
                $query->field('uid,nickname');
            },'userLabelRelation'])
            ->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) use ($labels, $systemUserLevel, $userGroup) {
                $item['status'] = ($item['status'] == 1) ? '正常' : '禁止';
                $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
                $item['last_time'] = $item['last_time'] ? date('Y-m-d H:i:s', $item['last_time']) : '无访问';
                $item['birthday'] = $item['birthday']? date('Y-m-d', $item['birthday']) : '';
                $pay_count = StoreOrder::getUserCountPay($item['uid']);
                if($pay_count != $item['pay_count'])//购买次数
                    self::edit(['pay_count' => $pay_count], $item['uid']);
                $item['extract_count_price'] = UserExtract::getUserCountPrice($item['uid']);//累计提现
                $item['spread_uid_nickname'] = $item['spread_uid'] ? ($item['spreadUser']['nickname'] ?? '') .  '/' . $item['spread_uid'] : '无';
                if ($item['openid'] != '' && $item['routine_openid'] != '') {
                    $item['user_type'] = '通用';
                } else if ($item['openid'] == '' && $item['routine_openid'] != '') {
                    $item['user_type'] = '小程序';
                } else if ($item['openid'] != '' && $item['routine_openid'] == '') {
                    $item['user_type'] = '公众号';
                } else if ($item['user_type'] == 'h5') {
                    $item['user_type'] = 'H5';
                } else $item['user_type'] = '其他';
                if ($item['sex'] == 1) {
                    $item['sex'] = '男';
                } else if ($item['sex'] == 2) {
                    $item['sex'] = '女';
                } else $item['sex'] = '保密';
                $item['level'] = $systemUserLevel[$item['level']] ?? '无';
                $item['group_id'] = $userGroup[$item['group_id']] ?? '无';
                $item['vip_name'] = false;
                $levelinfo = UserLevel::where('uid', $item['uid'])->where('level_id', $item['level'])->where('is_del', 0)->order('grade desc')->field('level_id,is_forever,valid_time')->find();
                if ($levelinfo && ( $levelinfo['is_forever'] || time() < $levelinfo['valid_time'] )) {
                    $item['vip_name'] = SystemUserLevel::where('id', $levelinfo['level_id'])->value('name');
                }
                $label = [];
                if($item['userLabelRelation'] && !empty($labels)){
                    foreach ($item['userLabelRelation'] as $k => $v) {
                        if (isset($labels[$v['label_id']]))
                            $label[] = $labels[$v['label_id']];
                    }
                }
                $item['labels'] = implode(',', $label);
                unset($item['spreadUser'],$item['userLabelRelation']);
            });//->toArray();
        return ['count' => $count, 'list' => $list];
    }

    //获取某用户的详细信息
    public static function getUserDetailed($uid)
    {
        $key_field = ['real_name', 'phone', 'province', 'city', 'district', 'detail', 'post_code'];
        $Address = ($thisAddress = UserAddress::where('uid', $uid)->where('is_default', 1)->field($key_field)->find()) ?
            $thisAddress :
            UserAddress::where('uid', $uid)->field($key_field)->find();
        $UserInfo = self::get($uid);
        return [
            ['col' => 12, 'name' => '默认收货地址', 'value' => $thisAddress ? '收货人:' . $thisAddress['real_name'] . '邮编:' . $thisAddress['post_code'] . ' 收货人电话:' . $thisAddress['phone'] . ' 地址:' . $thisAddress['province'] . ' ' . $thisAddress['city'] . ' ' . $thisAddress['district'] . ' ' . $thisAddress['detail'] : ''],
//            ['name'=>'微信OpenID','value'=>WechatUser::where('uid', $uid)->value('openid'),'col'=>8],
            ['name' => '手机号码', 'value' => $UserInfo['phone']],
//            ['name'=>'ID','value'=>$uid],
            ['name' => '姓名', 'value' => ''],
            ['name' => '微信昵称', 'value' => $UserInfo['nickname']],
            ['name' => '头像', 'value' => $UserInfo['avatar']],
            ['name' => '邮箱', 'value' => ''],
            ['name' => '生日', 'value' => ''],
            ['name' => '积分', 'value' => $UserInfo['integral']],
            ['name' => '上级推广人', 'value' => $UserInfo['spread_uid'] ? self::where('uid', $UserInfo['spread_uid'])->value('nickname') : ''],
            ['name' => '账户余额', 'value' => $UserInfo['now_money']],
            ['name' => '佣金总收入', 'value' => UserBill::where('category', 'now_money')->where('type', 'brokerage')->where('uid', $uid)->sum('number')],
            ['name' => '提现总金额', 'value' => UserExtract::where('uid', $uid)->where('status', 1)->sum('extract_price')],
        ];
    }

    //获取某用户的订单个数,消费明细
    public static function getHeaderList($uid)
    {
        return [
            [
                'title' => '余额',
                'value' => self::where('uid', $uid)->value('now_money'),
                'key' => '元',
                'class' => '',
            ],
            [
                'title' => '总计订单',
                'value' => StoreOrder::where('uid', $uid)->count(),
                'key' => '笔',
                'class' => '',
            ],
            [
                'title' => '总消费金额',
                'value' => StoreOrder::where('uid', $uid)->where('paid', 1)->sum('total_price'),
                'key' => '元',
                'class' => '',
            ],
            [
                'title' => '积分',
                'value' => self::where('uid', $uid)->value('integral'),
                'key' => '',
                'class' => '',
            ],
            [
                'title' => '本月订单',
                'value' => StoreOrder::where('uid', $uid)->whereTime('add_time', 'month')->count(),
                'key' => '笔',
                'class' => '',
            ],
            [
                'title' => '本月消费金额',
                'value' => StoreOrder::where('uid', $uid)->where('paid', 1)->whereTime('add_time', 'month')->sum('total_price'),
                'key' => '元',
                'class' => '',
            ]
        ];
    }

    /*
     * 获取 会员 订单个数,积分明细,优惠劵明细
     *
     * $uid 用户id;
     *
     * return array
     */
    public static function getCountInfo($uid)
    {
        $order_count = StoreOrder::where('uid', $uid)->count();
        $integral_count = UserBill::where('uid', $uid)->where('category', 'integral')->where('type', 'in', 'deduction,system_add')->count();
        $sign_count = UserBill::where('type', 'sign')->where('uid', $uid)->where('category', 'integral')->count();
        $balanceChang_count = UserBill::where('category', 'now_money')->where('uid', $uid)
            ->where('type', 'in', 'system_add,pay_product,extract,pay_product_refund,system_sub')
            ->count();
        $coupon_count = StoreCouponUser::where('uid', $uid)->count();
        $spread_count = self::where('spread_uid', $uid)->count();
        return compact('order_count', 'integral_count', 'sign_count', 'balanceChang_count', 'coupon_count', 'spread_count');
    }

    //获取佣金记录列表
    public static function getCommissionList($where)
    {
        $model = self::setCommissionWhere($where);
        $list = $model->page((int)$where['page'], (int)$where['limit'])->select();
        count($list) && $list = $list->toArray();
        foreach ($list as &$value) {
            $value['ex_price'] = UserExtract::where('uid', $value['uid'])->sum('extract_price');
            $value['extract_price'] = UserExtract::where('uid', $value['uid'])->where('status', 1)->sum('extract_price');
            $cashPrice = UserExtract::where('uid', $value['uid'])->where('status', 0)->sum('extract_price');
            $value['money'] = bcsub($value['ex_price'], $value['extract_price'], 2);
            $value['money'] = bcsub($value['money'], $cashPrice, 2);
        }
        $count = self::setCommissionWhere($where)->count();
        return ['data' => $list, 'count' => $count];
    }

    //获取佣金记录列表
    public static function exportData($where)
    {
        $model = self::setCommissionWhere($where);
        $list = $model->select();
        count($list) && $list = $list->toArray();
        foreach ($list as &$value) {
            $value['ex_price'] = UserExtract::where('uid', $value['uid'])->sum('extract_price');
            $value['extract_price'] = UserExtract::where('uid', $value['uid'])->where('status', 1)->sum('extract_price');
            $cashPrice = UserExtract::where('uid', $value['uid'])->where('status', 0)->sum('extract_price');
            $value['money'] = bcsub($value['ex_price'], $value['extract_price'], 2);
            $value['money'] = bcsub($value['money'], $cashPrice, 2);
        }
        return $list;

    }

    //获取佣金记录列表的查询条件
    public static function setCommissionWhere($where)
    {
        $models = self::setWherePage(self::alias('A'), $where, [], ['A.nickname', 'A.uid'])
            ->join('user_bill B', 'B.uid=A.uid')
            ->group('A.uid')
            ->where('B.type', 'brokerage')
            ->where('B.category', 'now_money')
            ->field('sum(B.number) as sum_number,A.nickname,A.uid,A.now_money,A.brokerage_price');
        if ($where['price_max'] != '' && $where['price_min'] != '') {
            $models = $models->whereBetween('A.brokerage_price', "$where[price_min], $where[price_max]");
        }
        return $models;
    }

    //获取某人用户推广信息
    public static function userInfo($uid)
    {
        $userinfo = self::where('uid', $uid)->field('nickname,spread_uid,now_money,add_time')->find()->toArray();
        $userinfo['number'] = (float)UserBill::where('category', 'now_money')->where('uid', $uid)->where('type', 'brokerage')->sum('number');
        $userinfo['spread_name'] = $userinfo['spread_uid'] ? self::where('uid', $userinfo['spread_uid'])->value('nickname') : '';
        return $userinfo;
    }

    /*
     * 获取和提现金额
     * @param array $uid
     * @return float
     * */
    public static function getextractPrice($uid, $where = [])
    {
        if (is_array($uid)) {
            if (!count($uid)) return 0;
        } else
            $uid = [$uid];
        $brokerage = UserBill::getAdminBrokerage($uid, 'now_money', 'brokerage', $where);//获取总佣金
        $recharge = UserBill::getAdminBrokerage($uid, 'now_money', 'recharge', $where);//累计充值
        $extractTotalPrice = UserExtract::userExtractAdminTotalPrice($uid, 1, $where);//累计提现
        if ($brokerage > $extractTotalPrice) {
            $orderYuePrice = self::getModelTime($where, StoreOrder::where('uid', 'in', $uid)->where(['is_del' => 0, 'paid' => 1]))->sum('pay_price');//余额累计消费
            $systemAdd = UserBill::getAdminBrokerage($uid, 'now_money', 'system_add', $where);//后台添加余额
            $yueCount = bcadd($recharge, $systemAdd, 2);// 后台添加余额 + 累计充值  = 非佣金的总金额
            $orderYuePrice = $yueCount > $orderYuePrice ? 0 : bcsub($orderYuePrice, $yueCount, 2);// 余额累计消费（使用佣金消费的金额）
            $brokerage = bcsub($brokerage, $extractTotalPrice, 2);//减去已提现金额
            $extract_price = UserExtract::userExtractAdminTotalPrice($uid, 0, $where);
            $brokerage = $extract_price < $brokerage ? bcsub($brokerage, $extract_price, 2) : 0;//减去审核中的提现金额
            $brokerage = $brokerage > $orderYuePrice ? bcsub($brokerage, $orderYuePrice, 2) : 0;//减掉余额支付
        } else {
            $brokerage = 0;
        }
        $num = (float)bcsub($brokerage, $extractTotalPrice, 2);
        return $num > 0 ? $num : 0;//可提现
    }

    /**获取用户详细信息
     * @param $uid
     * @return array
     */
    public static function getUserInfos($uid)
    {
        $userInfo = self::where('uid', $uid)->find();
        if (!$userInfo) exception('读取用户信息失败!');
        return $userInfo->toArray();
    }

}