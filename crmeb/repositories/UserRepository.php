<?php

namespace crmeb\repositories;

use app\models\user\User;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;
use think\db\exception\ModelNotFoundException;
use think\db\exception\DataNotFoundException;
use think\exception\DbException;

/**
 * Class UserRepository
 * @package crmeb\repositories
 */
class UserRepository
{
    /**
     * 管理员后台给用户添加金额
     * @param $user
     * $user 用户信息
     * @param $money
     * $money 添加的金额
     */
    public static function adminAddMoney($user, $money)
    {

    }

    /**
     * 管理员后台给用户减少金额
     * @param $user
     * $user 用户信息
     * @param $money
     * $money 减少的金额
     */
    public static function adminSubMoney($user, $money)
    {

    }

    /**
     * 管理员后台给用户增加的积分
     * @param $user
     * $user 用户信息
     * @param $integral
     * $integral 增加的积分
     */
    public static function adminAddIntegral($user, $integral)
    {

    }

    /**
     * 管理员后台给用户减少的积分
     * @param $user
     * $user 用户信息
     * @param $integral
     * $integral 减少的积分
     */
    public static function adminSubIntegral($user, $integral)
    {

    }

    /**
     * 获取授权信息
     * @param string $token
     * @return array
     * @throws AuthException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function parseToken($token): array
    {
        if (!$token || !$tokenData = CacheService::getTokenBucket($token))
            throw new AuthException('请登录', 410000);

        if (!is_array($tokenData) || empty($tokenData) || !isset($tokenData['uid'])) {
            throw new AuthException('请登录', 410000);
        }
        
        try {
            [$user, $type] = User::parseToken($token);
        } catch (\Throwable $e) {
            CacheService::clearToken($token);
            throw new AuthException('登录已过期,请重新登录', 410001);
        }

        if (!$user || $user->uid != $tokenData['uid']) {
            CacheService::clearToken($token);
            throw new AuthException('登录状态有误,请重新登录', 410002);
        }
        $tokenData['type'] = $type;
        return compact('user', 'tokenData');
    }


}