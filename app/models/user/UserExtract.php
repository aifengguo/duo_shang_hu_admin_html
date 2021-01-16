<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/3/3
 */

namespace app\models\user;

use app\models\user\User;
use app\models\user\UserBill;
use app\models\wechat\WechatTemplate;
use app\models\wechat\WechatUser;
use app\models\routine\RoutineTemplate;
use crmeb\basic\BaseModel;
use crmeb\services\workerman\ChannelService;
use crmeb\traits\ModelTrait;
use think\facade\Route as Url;

/**
 * TODO 用户提现
 * Class UserExtract
 * @package app\models\user
 */
class UserExtract extends BaseModel
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
    protected $name = 'user_extract';

    use ModelTrait;

    //审核中
    const AUDIT_STATUS = 0;
    //未通过
    const FAIL_STATUS = -1;
    //已提现
    const SUCCESS_STATUS = 1;

    protected static $extractType = ['alipay', 'bank', 'weixin'];

    protected static $extractTypeMsg = ['alipay' => '支付宝', 'bank' => '银行卡', 'weixin' => '微信'];

    protected static $status = array(
        -1 => '未通过',
        0 => '审核中',
        1 => '已提现'
    );

    /**
     * 用户自主提现记录提现记录,后台执行审核
     * @param array $userInfo 用户个人信息
     * @param array $data 提现详细信息
     * @return bool
     */
    public static function userExtract($userInfo, $data)
    {
        if (!in_array($data['extract_type'], self::$extractType))
            return self::setErrorInfo('提现方式不存在');
        $userInfo = User::get($userInfo['uid']);
        $extractPrice = $userInfo['brokerage_price'];
        if ($extractPrice < 0) return self::setErrorInfo('提现佣金不足' . $data['money']);
        if ($data['money'] > $extractPrice) return self::setErrorInfo('提现佣金不足' . $data['money']);
        if ($data['money'] <= 0) return self::setErrorInfo('提现佣金大于0');
        $balance = bcsub($userInfo['brokerage_price'], $data['money'], 2);
        if ($balance < 0) $balance = 0;
        $insertData = [
            'uid' => $userInfo['uid'],
            'extract_type' => $data['extract_type'],
            'extract_price' => $data['money'],
            'add_time' => time(),
            'balance' => $balance,
            'status' => self::AUDIT_STATUS
        ];
        if (isset($data['name']) && strlen(trim($data['name']))) $insertData['real_name'] = $data['name'];
        else $insertData['real_name'] = $userInfo['nickname'];
        if (isset($data['cardnum'])) $insertData['bank_code'] = $data['cardnum'];
        else $insertData['bank_code'] = '';
        if (isset($data['bankname'])) $insertData['bank_address'] = $data['bankname'];
        else $insertData['bank_address'] = '';
        if (isset($data['weixin'])) $insertData['wechat'] = $data['weixin'];
        else $insertData['wechat'] = $userInfo['nickname'];
        if ($data['extract_type'] == 'alipay') {
            if (!$data['alipay_code']) return self::setErrorInfo('请输入支付宝账号');
            $insertData['alipay_code'] = $data['alipay_code'];
            $mark = '使用支付宝提现' . $insertData['extract_price'] . '元';
        } else if ($data['extract_type'] == 'bank') {
            if (!$data['cardnum']) return self::setErrorInfo('请输入银行卡账号');
            if (!$data['bankname']) return self::setErrorInfo('请输入开户行信息');
            $mark = '使用银联卡' . $insertData['bank_code'] . '提现' . $insertData['extract_price'] . '元';
        } else if ($data['extract_type'] == 'weixin') {
            if (!$data['weixin']) return self::setErrorInfo('请输入微信账号');
            $mark = '使用微信提现' . $insertData['extract_price'] . '元';
        }
        self::beginTrans();
        try {
            $res1 = self::create($insertData);
            if (!$res1) return self::setErrorInfo('提现失败');
            $res2 = User::edit(['brokerage_price' => $balance], $userInfo['uid'], 'uid');
            $res3 = UserBill::expend('余额提现', $userInfo['uid'], 'now_money', 'extract', $data['money'], $res1['id'], $balance, $mark);
            $res = $res2 && $res3;
            if ($res) {
                self::commitTrans();
                try {
                    ChannelService::instance()->send('WITHDRAW', ['id' => $res1->id]);
                } catch (\Exception $e) {
                }
                event('AdminNewPush');
                //发送模板消息
                return true;
            } else return self::setErrorInfo('提现失败!');
        } catch (\Exception $e) {
            self::rollbackTrans();
            return self::setErrorInfo('提现失败!');
        }
    }

    /**
     * 获得用户最后一次提现信息
     * @param $openid
     * @return mixed
     */
    public static function userLastInfo($uid)
    {
        return self::where(compact('uid'))->order('add_time DESC')->find();
    }

    /**
     * 获得用户提现总金额
     * @param $uid
     * @return mixed
     */
    public static function userExtractTotalPrice($uid, $status = self::SUCCESS_STATUS)
    {
        return self::where('uid', $uid)->where('status', $status)->value('SUM(extract_price)') ?: 0;
    }

    public static function userExtractAdminTotalPrice($uid, $status = 1, $where = [])
    {
        return self::getModelTime($where, self::where('uid', 'in', $uid)->where('status', $status))->sum('extract_price') ?: 0;
    }

    /*
     * 用户提现记录列表
     * @param int $uid 用户uid
     * @param int $first 截取行数
     * @param int $limit 截取数
     * @return array
     * */
    public static function extractList($uid, $first = 0, $limit = 8)
    {
        $list = UserExtract::where('uid', $uid)->order('add_time desc')->limit($first, $limit)->select();
        foreach ($list as &$v) {
            $v['add_time'] = date('Y/m/d', $v['add_time']);
        }
        return $list;
    }

    /**
     * 获取累计已提现佣金
     * @param $uid
     * @return float
     */
    public static function extractSum($uid)
    {
        return self::where('uid', $uid)->where('status', 1)->sum('extract_price');
    }

    /**
     * 获取用户累计提现金额
     * @param int $uid
     * @return int|mixed
     */
    public static function getUserCountPrice($uid = 0)
    {
        if (!$uid) return 0;
        $price = self::where('uid', $uid)->where('status', 1)->sum('extract_price');
        return $price ? $price : 0;
    }

    /**
     * 后台提现统计
     * @return array
     */
    public static function extractStatistics($where)
    {
        $model = new self;
        $model = self::getModelTime($where, $model, 'add_time');
        //待提现金额
        $data['price'] = floatval($model->where('status', 0)->sum('extract_price'));
        //佣金总金额
        $data['brokerage_count'] = floatval(UserBill::getBrokerageCount($where));
        //已提现金额
        $data['priced'] = floatval($model->where('status', 1)->sum('extract_price'));
        //未提现金额
        $data['brokerage_not'] = bcsub(bcsub($data['brokerage_count'], $data['priced'], 2), $data['price'], 2);
        return compact('data');
    }

    /**
     * 提现申请列表
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        $model = self::getModelTime($where, $model, 'a.add_time');
        if ($where['status'] != '') $model = $model->where('a.status', $where['status']);
        if ($where['extract_type'] != '') $model = $model->where('a.extract_type', $where['extract_type']);
        if ($where['nireid'] != '') $model = $model->where('a.real_name|a.id|b.nickname|a.bank_code|a.alipay_code', 'like', "%$where[nireid]%");
        $model = $model->alias('a');
        $model = $model->where('a.extract_type', '<>', 'balance');
        $model = $model->field('a.*,b.nickname');
        $model = $model->join('user b', 'b.uid=a.uid', 'LEFT');
        $model = $model->order('a.id desc');
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])->select();
        return compact('count', 'list');
    }

    /**
     * 拒绝提现申请
     * @param $id
     * @param $fail_msg
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function changeFail($id, $fail_msg)
    {
        $fail_time = time();
        $data = self::get($id);
        $extract_number = $data['extract_price'];
        $mark = '提现失败,退回佣金' . $extract_number . '元';
        $uid = $data['uid'];
        $status = -1;
        $User = User::where('uid', $uid)->find()->toArray();
        UserBill::income('提现失败', $uid, 'now_money', 'extract', $extract_number, $id, bcadd($User['now_money'], $extract_number, 2), $mark);
        User::bcInc($uid, 'brokerage_price', $extract_number, 'uid');
        $extract_type = '未知方式';
        switch ($data['extract_type']) {
            case 'alipay':
                $extract_type = '支付宝';
                break;
            case 'bank':
                $extract_type = '银行卡';
                break;
            case 'weixin':
                $extract_type = '微信';
                break;
        }
        if (strtolower($User['user_type']) == 'wechat') {
            $wechatTemplate = new WechatTemplate();
            $wechatTemplate->sendUserBalanceChangeFial($uid, $extract_number, $fail_msg);
        } else if (strtolower($User['user_type']) == 'routine') {
            RoutineTemplate::sendExtractFail($uid, $fail_msg, $extract_number, $User['nickname']);
        }
        return self::edit(compact('fail_time', 'fail_msg', 'status'), $id);
    }

    /**
     * 通过提现申请
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function changeSuccess($id)
    {

        $data = self::get($id);
        $extractNumber = $data['extract_price'];
        $mark = '成功提现佣金' . $extractNumber . '元';
        $wechatUserInfo = \app\models\wechat\WechatUser::where('uid', $data['uid'])->field('openid,user_type,routine_openid')->find();
        $extract_type = '未知方式';
        switch ($data['extract_type']) {
            case 'alipay':
                $extract_type = '支付宝';
                break;
            case 'bank':
                $extract_type = '银行卡';
                break;
            case 'weixin':
                $extract_type = '微信';
                break;
        }
        if ($wechatUserInfo) {
            if (strtolower($wechatUserInfo->user_type) == 'routine') {
                RoutineTemplate::sendExtractSuccess($data['uid'], $extractNumber, $wechatUserInfo->nickname);
            } else if (strtolower($wechatUserInfo->user_type) == 'wechat') {
                $wechatTemplate = new WechatTemplate();
                $wechatTemplate->sendUserBalanceChangeSuccess($data['uid'], $extractNumber);
            }
        }
        return self::edit(['status' => 1], $id);
    }

    /**
     * 获取用户累计提现次数
     * @param int $uid
     * @return int|string
     */
    public static function getUserCountNum($uid = 0)
    {
        if (!$uid) return 0;
        return self::where('uid', $uid)->count();
    }
}