<?php

namespace app\adminapi\controller\v1\agent;

use app\adminapi\controller\AuthController;
use app\models\user\{
    User, UserBill
};
use app\models\routine\{
    RoutineCode, RoutineQrcode
};
use app\models\store\StoreOrder;
use app\models\system\SystemAttachment;
use app\models\wechat\WechatUser as UserModel;
use crmeb\services\{
    QrcodeService, UploadService, UtilService as Util
};

/**
 * 分销商管理控制器
 * Class AgentManage
 * @package app\adminapi\controller\v1\agent
 */
class AgentManage extends AuthController
{
    /**
     * 分销管理列表
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['nickname', ''],
            ['data', ''],
            ['excel', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        return $this->success(UserModel::agentSystemPage($where));
    }

    /**
     * 分销头部统计
     * @return mixed
     */
    public function get_badge()
    {
        $where = Util::getMore([
            ['data', ''],
            ['nickname', ''],
        ]);
        $res = UserModel::getSpreadBadge($where);
        return $this->success(compact('res'));
    }

    /**
     * 推广人列表
     * @return mixed
     */
    public function get_stair_list()
    {
        $where = Util::getMore([
            ['uid', 0],
            ['data', ''],
            ['nickname', ''],
            ['type', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        return $this->success(UserModel::getStairList($where));
    }

    /**
     * 推广人列表头部统计
     * @return mixed
     */
    public function get_stair_badge()
    {
        $where = Util::getMore([
            ['uid', ''],
            ['data', ''],
            ['nickname', ''],
            ['type', ''],
        ]);
        $res = UserModel::getSairBadge($where);
        return $this->success(compact('res'));
    }

    /*
    *  统计推广订单列表
    * @param int $uid
    * */
    public function get_stair_order_list()
    {
        $where = Util::getMore([
            ['uid', 0],
            ['data', ''],
            ['order_id', ''],
            ['type', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        return $this->success(UserModel::getStairOrderList($where));
    }

    /**
     * 统计推广订单头部统计
     * @return mixed
     */
    public function get_stair_order_badge()
    {
        $where = Util::getMore([
            ['uid', ''],
            ['data', ''],
            ['order_id', ''],
            ['type', ''],
        ]);
        return $this->success(UserModel::getStairOrderBadge($where));
    }

    /**
     * 二级推荐人页面
     * @return mixed
     */
    public function stair_two($uid = '')
    {
        if ($uid == '') return $this->fail('参数错误');
        $spread_uid = User::where('spread_uid', $uid)->column('uid', 'uid');
        if (count($spread_uid))
            $spread_uid_two = User::where('spread_uid', 'in', $spread_uid)->column('uid', 'uid');
        else
            $spread_uid_two = [0];
        $list = User::alias('u')
            ->where('u.uid', 'in', $spread_uid_two)
            ->field('u.avatar,u.nickname,u.now_money,u.spread_time,u.uid')
            ->where('u.status', 1)
            ->order('u.add_time DESC')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) $list[$key]['orderCount'] = StoreOrder::getOrderCount($value['uid']) ?: 0;
        return $this->success(compact('list'));
    }

    /*
     * 批量清除推广权限
     * */
    public function delete_promoter()
    {
        list($uids) = Util::postMore([
            ['uids', []]
        ], $this->request, true);
        if (!count($uids)) return $this->fail('请选择需要解除推广权限的用户！');
        User::beginTrans();
        try {
            if (User::where('uid', 'in', $uids)->update(['is_promoter' => 0])) {
                User::commitTrans();
                return $this->success('解除成功');
            } else {
                User::rollbackTrans();
                return $this->fail('解除失败');
            }
        } catch (\PDOException $e) {
            User::rollbackTrans();
            return $this->fail('数据库操作错误', ['line' => $e->getLine(), 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            User::rollbackTrans();
            return $this->fail('系统错误', ['line' => $e->getLine(), 'message' => $e->getMessage()]);
        }

    }

    /*
     * 查看公众号推广二维码
     * @param int $uid
     * @return json
     * */
    public function look_code($uid = '', $action = '')
    {
        if (!$uid || !$action) return $this->fail('缺少参数');
        try {
            if (method_exists($this, $action)) {
                $res = $this->$action($uid);
                if ($res)
                    return $this->success($res);
                else
                    return $this->fail(isset($res['msg']) ? $res['msg'] : '获取失败，请稍后再试！');
            } else
                return $this->fail('暂无此方法');
        } catch (\Exception $e) {
            return $this->fail('获取推广二维码失败，请检查您的微信配置', ['line' => $e->getLine(), 'messag' => $e->getMessage()]);
        }
    }

    /*
     * 获取小程序二维码
     * */
    public function routine_code($uid)
    {
        $userInfo = User::getUserInfos($uid);
        $name = $userInfo['uid'] . '_' . $userInfo['is_promoter'] . '_user.jpg';
        $imageInfo = SystemAttachment::getInfo($name, 'name');
        if (!$imageInfo) {
            $res = RoutineCode::getShareCode($uid, 'spread', '', '');
            if (!$res) throw new \think\Exception('二维码生成失败');
            $upload = UploadService::init();
            if ($upload->to('routine/spread/code')->stream($res['res'], $name) === false) {
                return $upload->getError();
            }
            $imageInfo = $upload->getUploadInfo();
            SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time']);
            RoutineQrcode::setRoutineQrcodeFind($res['id'], ['status' => 1, 'time' => time(), 'qrcode_url' => $imageInfo['dir']]);
            $urlCode = $imageInfo['dir'];
        } else $urlCode = $imageInfo['att_dir'];
        return ['code_src' => $urlCode];
    }

    /*
     * 获取公众号二维码
     * */
    public function wechant_code($uid)
    {
        $qr_code = QrcodeService::getTemporaryQrcode('spread', $uid);
        if (isset($qr_code['url']))
            return ['code_src' => $qr_code['url']];
        else
            throw new \think\Exception('获取失败，请稍后再试！');
    }

    /**
     * TODO 查看小程序推广二维码
     * @param string $uid
     */
    public function look_xcx_code($uid = '')
    {
        if (!strlen(trim($uid))) return $this->fail('缺少参数');
        try {
            $userInfo = User::getUserInfos($uid);
            $name = $userInfo['uid'] . '_' . $userInfo['is_promoter'] . '_user.jpg';
            $imageInfo = SystemAttachment::getInfo($name, 'name');
            if (!$imageInfo) {
                $res = RoutineCode::getShareCode($uid, 'spread', '', '');
                if (!$res) return $this->fail('二维码生成失败');
                $upload = UploadService::init();
                if ($upload->to('routine/spread/code')->stream($res['res'], $name) === false) {
                    return $upload->getError();
                }
                $imageInfo = $upload->getUploadInfo();
                SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time']);
                RoutineQrcode::setRoutineQrcodeFind($res['id'], ['status' => 1, 'time' => time(), 'qrcode_url' => $imageInfo['dir']]);
                $urlCode = $imageInfo['dir'];
            } else $urlCode = $imageInfo['att_dir'];
            return $this->success(['code_src' => $urlCode]);
        } catch (\Exception $e) {
            return $this->fail('查看推广二维码失败！', ['line' => $e->getLine(), 'meassge' => $e->getMessage()]);
        }
    }

    /**
     * 查看H5推广二维码
     * @param string $uid
     * @return mixed|string
     */
    public function look_h5_code($uid = '')
    {
        if (!strlen(trim($uid))) return $this->fail('缺少参数');
        try {
            $userInfo = User::getUserInfos($uid);
            $name = $userInfo['uid'] . '_h5_' . $userInfo['is_promoter'] . '_user.jpg';
            $imageInfo = SystemAttachment::getInfo($name, 'name');
            if (!$imageInfo) {
                $urlCode = QrcodeService::getWechatQrcodePath($uid . '_h5_' . $userInfo['is_promoter'] . '_user.jpg','');
            } else $urlCode = $imageInfo['att_dir'];
            return $this->success(['code_src' => $urlCode]);
        } catch (\Exception $e) {
            return $this->fail('查看推广二维码失败！', ['line' => $e->getLine(), 'meassge' => $e->getMessage()]);
        }
    }

    /*
     * 解除单个用户的推广权限
     * @param int $uid
     * */
    public function delete_spread($uid)
    {
        $res = User::where('uid', $uid)->update(['spread_uid' => 0]);
        if ($res !== false)
            return $this->success('解除成功');
        else
            return $this->fail('解除失败');
    }

    /*
     * 清除推广人
     * */
    public function empty_spread($uid = 0)
    {
        if (!$uid) return $this->fail('缺少参数');
        $res = true;
        $spread_uid = User::where('spread_uid', $uid)->column('uid', 'uid');
        if (count($spread_uid)) $res = $res && false !== User::where('spread_uid', 'in', $spread_uid)->update(['spread_uid' => 0]);
        $res = $res && false !== User::where('spread_uid', $uid)->update(['spread_uid' => 0]);
        if ($res)
            return $this->success('清除成功');
        else
            return $this->fail('清除失败');
    }

    /**
     * 个人资金详情页面
     * @return mixed
     */
    public function now_money($uid = '')
    {
        if ($uid == '') return $this->fail('参数错误');
        $list = UserBill::where('uid', $uid)->where('category', 'now_money')
            ->field('mark,pm,number,add_time')
            ->where('status', 1)->order('add_time DESC')->select()->toArray();
        foreach ($list as &$v) {
            $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        }
        return $this->success($list);
    }

}
