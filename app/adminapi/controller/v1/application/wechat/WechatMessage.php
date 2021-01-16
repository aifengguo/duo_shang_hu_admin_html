<?php

namespace app\adminapi\controller\v1\application\wechat;


use app\models\wechat\WechatMessage as MessageModel;
use app\adminapi\controller\AuthController;
use crmeb\services\UtilService as Util;

/**
 * 用户扫码点击事件
 * Class SystemMessage
 * @package app\admin\controller\system
 */
class WechatMessage extends AuthController

{

    /**
     * 显示操作记录
     */
    public function index(){
        $where = Util::getMore([
            ['page',1],
            ['limit',20],
            ['nickname',''],
            ['type',''],
            ['data',''],
        ],$this->request);
        $list = MessageModel::systemPage($where);
        return $this->success($list);
    }

    /**
     * 操作名称列表
     * @return mixed
     */
    public function operate(){
        $operate = MessageModel::$mold;
        return $this->success(compact('operate'));
    }

}

