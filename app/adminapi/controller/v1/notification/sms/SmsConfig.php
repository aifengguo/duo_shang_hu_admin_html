<?php

namespace app\adminapi\controller\v1\notification\sms;

use app\models\sms\SmsRecord;

use crmeb\services\CacheService;
use crmeb\services\sms\Sms;
use crmeb\services\UtilService;
use app\adminapi\controller\AuthController;
use app\models\system\SystemConfig as ConfigModel;

/**
 * 短信配置
 * Class SmsConfig
 * @package app\admin\controller\sms
 */
class SmsConfig extends AuthController
{

    /**
     * @var Sms
     */
    protected $smsHandle;


    /**
     * 保存短信配置
     * @return mixed
     */
    public function save_basics()
    {
        [$account, $token] = UtilService::postMore([
            ['sms_account', ''],
            ['sms_token', '']
        ], $this->request, true);

        $this->validate(['sms_account' => $account, 'sms_token' => $token], \app\adminapi\validates\notification\SmsConfigValidate::class);

        $this->smsHandle = new Sms('yunxin', [
            'sms_account' => $account,
            'sms_token' => $token,
            'site_url' => sys_config('site_url')
        ]);

        ConfigModel::edit(['value' => json_encode($account)], 'sms_account', 'menu_name');
        ConfigModel::edit(['value' => json_encode($token)], 'sms_token', 'menu_name');

        //添加公共短信模板
        $templateList = $this->smsHandle->publictemp([]);
        if ($templateList['status'] != 400) {
            if ($templateList['data']['data']) {
                foreach ($templateList['data']['data'] as $v) {
                    if ($v['is_have'] == 0)
                        $this->smsHandle->use($v['id'], $v['templateid']);
                }
            }
            CacheService::clear();
            CacheService::redisHandler()->set('sms_account', $account);
            return $this->success('登录成功');
        } else {
            return $this->fail('账号或密码错误');
        }
    }

    /**
     * 检测登录
     * @return mixed
     */
    public function is_login()
    {
        $sms_info = CacheService::redisHandler()->get('sms_account');
        if ($sms_info) {
            return $this->success(['status' => true, 'info' => $sms_info]);
        } else {
            return $this->success(['status' => false]);
        }
    }

    /**
     * 退出
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function logout()
    {
        $res = CacheService::redisHandler()->delete('sms_account');
        if ($res) {
            ConfigModel::edit(['value' => json_encode('')], 'sms_account', 'menu_name');
            ConfigModel::edit(['value' => json_encode('')], 'sms_token', 'menu_name');
            CacheService::clear();
            return $this->success('退出成功');
        } else {
            return $this->fail('退出失败');
        }
    }

    /**
     * 短信发送记录
     * @return mixed
     */
    public function record()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 20],
            ['type', '']
        ]);
        return $this->success(SmsRecord::getRecordList($where));
    }

    /**
     * @return mixed
     */
    public function data()
    {
        $this->smsHandle = new Sms('yunxin', [
            'sms_account' => sys_config('sms_account'),
            'sms_token' => sys_config('sms_token'),
            'site_url' => sys_config('site_url')
        ]);
        $countInfo = $this->smsHandle->count();
        if ($countInfo['status'] == 400) {
            $info['number'] = 0;
            $info['total_number'] = 0;
        } else {
            $info['number'] = $countInfo['data']['number'];
            $info['total_number'] = $countInfo['data']['send_total'];
        }
        $info['record_number'] = SmsRecord::vaildWhere()->count();
        $info['sms_account'] = sys_config('sms_account');
        return $this->success($info);
    }
}