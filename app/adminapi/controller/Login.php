<?php

namespace app\adminapi\controller;


use app\models\system\SystemAdmin;
use app\models\system\SystemMenus;
use crmeb\basic\BaseController;
use crmeb\repositories\AdminRuleRepositories;
use crmeb\services\UtilService;
use crmeb\traits\RestFulTrait;
use crmeb\utils\Captcha;
use think\facade\Cache;

/**
 * 后台登陆
 * Class Login
 * @package app\adminapi\controller
 */
class Login extends BaseController
{

    /**
     * 验证码
     * @return $this|\think\Response
     */
    public function captcha()
    {
        return (new Captcha())->create();
    }

    /**
     * 登陆
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login()
    {
        [$account, $pwd, $imgcode] = UtilService::postMore([
            'account', 'pwd', ['imgcode', '']
        ], $this->request, true);

        if (!(new Captcha)->check($imgcode)) {
            return app('json')->fail('验证码错误，请重新输入');
        }

        $this->validate(['account' => $account, 'pwd' => $pwd], \app\adminapi\validates\setting\SystemAdminValidata::class, 'get');

        $error = Cache::get('login_error') ?: ['num' => 0, 'time' => time()];
        $error['num'] = 0;
        if ($error['num'] >= 5 && $error['time'] > strtotime('- 5 minutes'))
            return $this->fail('错误次数过多,请稍候再试!');
        $adminInfo = SystemAdmin::login($account, $pwd);
        if ($adminInfo) {
            $token = SystemAdmin::createToken($adminInfo, 'admin');
            if ($token === false) {
                return app('json')->fail(SystemAdmin::getErrorInfo());
            }
            Cache::set('login_error', null);
            //获取用户菜单
            $menusModel = new SystemMenus();
            $menus = $menusModel->getRoute($adminInfo->roles, $adminInfo['level']);
            $unique_auth = $menusModel->getUniqueAuth($adminInfo->roles, $adminInfo['level']);
            return app('json')->success([
                'token' => $token['token'],
                'expires_time' => $token['params']['exp'],
                'menus' => $menus,
                'unique_auth' => $unique_auth,
                'user_info' => [
                    'id' => $adminInfo->getData('id'),
                    'account' => $adminInfo->getData('account'),
                    'head_pic' => $adminInfo->getData('head_pic'),
                ],
                'logo' => sys_config('site_logo'),
                'logo_square' => sys_config('site_logo_square'),
                'version' => get_crmeb_version(),
                'newOrderAudioLink' => get_file_link(sys_config('new_order_audio_link'))
            ]);
        } else {
            $error['num'] += 1;
            $error['time'] = time();
            Cache::set('login_error', $error);
            return app('json')->fail(SystemAdmin::getErrorInfo('用户名错误，请重新输入'));
        }
    }

    /**
     * 获取后台登录页轮播图以及LOGO
     * @return mixed
     */
    public function info()
    {
        $data['slide'] = sys_data('admin_login_slide') ?? [];
        $data['logo_square'] = sys_config('site_logo_square');//透明
        $data['logo_rectangle'] = sys_config('site_logo');//方形
        $data['login_logo'] = sys_config('login_logo');//登陆
        return app('json')->success($data);
    }
}