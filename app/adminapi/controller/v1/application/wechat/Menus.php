<?php

namespace app\adminapi\controller\v1\application\wechat;


use app\adminapi\controller\AuthController;
use app\models\system\Cache;
use crmeb\services\WechatService;

/**
 * 微信菜单  控制器
 * Class Menus
 * @package app\admin\controller\wechat
 */
class Menus extends AuthController
{
    public function index()
    {
        $menus = Cache::where('key', 'wechat_menus')->value('result');
        $menus = empty($menus) ? [] : json_decode($menus, true);
//        $menus = $menus ?: [];
        return $this->success(compact('menus'));
    }

    public function save()
    {
        $buttons = request()->post('button/a', []);
        if (!count($buttons)) return $this->fail('请添加至少一个按钮');
        try {
            WechatService::menuService()->add($buttons);
            $count = Cache::where('key', 'wechat_menus')->count();
            if ($count) {
                $count = Cache::where('key', 'wechat_menus')->where('result', json_encode($buttons))->count();
                if (!$count)
                    Cache::where('key', 'wechat_menus')->update(['result' => json_encode($buttons), 'add_time' => time()]);
            } else
                Cache::insert(['key' => 'wechat_menus', 'result' => json_encode($buttons), 'add_time' => time()], true);
            return $this->success('修改成功!');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}