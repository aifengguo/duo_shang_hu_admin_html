<?php

namespace app\adminapi\controller\v1\notification\sms;

use app\adminapi\controller\AuthController;
use app\models\system\SystemConfig;
use crmeb\services\{
    HttpService, sms\Sms, UtilService, CacheService
};

/**
 * 短信账号
 * Class SmsAdmin
 * @package app\adminapi\controller\v1\sms
 */
class SmsAdmin extends AuthController
{

    /**
     * 发送验证码
     * @return mixed
     */
    public function captcha()
    {
        if (!request()->isPost()) return $this->fail('发生失败');
        $phone = request()->param('phone');
        if (!trim($phone)) return $this->fail('请填写手机号');
        $sms = new Sms('yunxin');
        $res = json_decode(HttpService::getRequest($sms->getSmsUrl(), compact('phone')), true);
        if (!isset($res['status']) && $res['status'] !== 200)
            return $this->fail(isset($res['data']['message']) ? $res['data']['message'] : $res['msg']);
        return $this->success(isset($res['data']['message']) ? $res['data']['message'] : $res['msg']);
    }

    /**
     * 修改/注册短信平台账号
     */
    public function save()
    {
        list($account, $password, $phone, $code, $url, $sign) = UtilService::postMore([
            ['account', ''],
            ['password', ''],
            ['phone', ''],
            ['code', ''],
            ['url', ''],
            ['sign', ''],
        ], null, true);
        $signLen = mb_strlen(trim($sign));
        if (!strlen(trim($account))) return $this->fail('请填写账号');
        if (!strlen(trim($password))) return $this->fail('请填写密码');
        if (!$signLen) return $this->fail('请填写短信签名');
        if ($signLen > 8) return $this->fail('短信签名最长为8位');
        if (!strlen(trim($code))) return $this->fail('请填写验证码');
        if (!strlen(trim($url))) return $this->fail('请填写域名');
        $sms = new Sms('yunxin');
        $status = $sms->register($account, md5(trim($password)), $url, $phone, $code, $sign);
        if ($status['status'] == 400) return $this->fail('短信平台：' . $status['msg']);
        CacheService::clear();
        SystemConfig::setConfigSmsInfo($account, $password);
        return $this->success('短信平台：' . $status['msg']);
    }
}