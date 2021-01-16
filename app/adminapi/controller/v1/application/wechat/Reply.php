<?php

namespace app\adminapi\controller\v1\application\wechat;

use app\models\wechat\WechatKey;
use app\models\wechat\WechatReply;
use app\adminapi\controller\AuthController;
use EasyWeChat\Core\Exceptions\HttpException;
use crmeb\services\{UtilService as Util};

/**
 * 关键字管理  控制器
 * Class Reply
 * @package app\admin\controller\wechat
 */
class Reply extends AuthController
{
    /**关注回复
     * @return mixed|void
     */
    public function reply()
    {
        $where = Util::getMore([
            ['key', ''],
        ]);
        if ($where['key'] == '') return $this->fail('请输入参数key');
        $info = WechatReply::getDataByKey($where['key']);
        return $this->success(compact('info'));
    }

    /**
     * 关键字回复列表
     * */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 15],
            ['key', ''],
            ['type', ''],
        ]);
        $list = WechatReply::getKeyAll($where);
        return $this->success($list);
    }

    /**
     * 关键字详情
     * */
    public function read($id)
    {
        $info = WechatReply::getKeyInfo($id);
        return $this->success(compact('info'));
    }

    /**
     * 保存关键字
     * */
    public function save($id = 0)
    {
        $data = Util::postMore([
            ['key',''],
            ['type',''],
            ['status', 0],
            ['data', []],
        ]);
        //file_put_contents('test.txt',serialize($data));
      
        try {
            if (!isset($data['key']) && empty($data['key']))
                return $this->fail('请输入关键字');
            if (!isset($data['type']) && empty($data['type']))
                return $this->fail('请选择回复类型');
            if (!in_array($data['type'], WechatReply::$reply_type))
                return $this->fail('回复类型有误!');

            if (!isset($data['data']) || !is_array($data['data']))
                return $this->fail('回复消息参数有误!');
            $res = WechatReply::redact($data['data'], $id, $data['key'], $data['type'], $data['status']);
            if (!$res)
                return $this->fail(WechatReply::getErrorInfo());
            else
                return $this->success('保存成功!', $data);
        } catch (HttpException $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除关键字
     * */
    public function delete($id)
    {
        if (!WechatReply::del($id))
            return $this->fail(WechatReply::getErrorInfo('删除失败,请稍候再试!'));
        else{
            $res = WechatKey::where('reply_id',$id)->delete();
            if (!$res){
                return $this->fail(WechatKey::getErrorInfo('删除失败,请稍候再试!'));
            }
        }
            return $this->success('删除成功!');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return $this->fail('参数错误');
        WechatReply::where(['id' => $id])->update(['status' => $status]);

        return $this->success($status == 0 ? '禁用成功' : '启用成功');
    }

}