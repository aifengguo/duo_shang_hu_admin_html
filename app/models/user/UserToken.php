<?php


namespace app\models\user;


use crmeb\services\CacheService;
use think\Model;

class UserToken extends Model
{
    protected $name = 'user_token';

    protected $type = [
        'create_time' => 'datetime',
        'login_ip' => 'string'
    ];

    protected $autoWriteTimestamp = true;

    protected $updateTime = false;

    public static function onBeforeInsert(UserToken $token)
    {
        if (!isset($token['login_ip']))
            $token['login_ip'] = app()->request->ip();
    }

    public static function createToken(User $user, $type)
    {
        $tokenInfo = $user->getToken($type);
        $expires = $tokenInfo['params']['exp'] - time() + 60;
        $res = CacheService::setTokenBucket($tokenInfo['token'], [
            'token' => $tokenInfo['token'],
            'exp' => $expires,
            'uid' => $user->getData('uid'),
            'type' => $type
        ], $expires, 'api');
        if($res) return $tokenInfo;
        return false;
    }

    /**
     * 删除一天前的过期token
     * @return bool
     * @throws \Exception
     */
    public static function delToken()
    {
        //return self::where('expires_time', '<', date('Y-m-d H:i:s', strtotime('-1 day')))->delete();
    }
}