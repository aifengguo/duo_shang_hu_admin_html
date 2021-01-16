<?php

namespace app\adminapi\controller;


use crmeb\basic\BaseController;

/**
 * 基类 所有控制器继承的类
 * Class AuthController
 * @package app\adminapi\controller
 */
class AuthController extends BaseController
{
    /**
     * 当前登陆管理员信息
     * @var
     */
    protected $adminInfo;

    /**
     * 当前登陆管理员ID
     * @var
     */
    protected $adminId;

    /**
     * 当前管理员权限
     * @var array
     */
    protected $auth = [];

    /**
     * 模型类名
     * @var null
     */
    protected $bindModel = null;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();

        $this->adminId = $this->request->adminId();
        $this->adminInfo = $this->request->adminInfo();

        $this->auth = $this->request->adminInfo['rule'] ?? [];

        event('AdminVisit', [$this->adminInfo, 'system']);
    }

}