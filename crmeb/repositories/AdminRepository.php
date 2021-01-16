<?php

namespace crmeb\repositories;


use app\models\system\SystemAdmin;
use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;
use Firebase\JWT\ExpiredException;
use think\exception\DbException;
use Firebase\JWT\JWT;

/**
 * Class UserRepository
 * @package crmeb\repositories
 */
class AdminRepository
{

    /**
     * 获取Admin授权信息
     * @param $token
     * @param int $expires
     * @param string $prefix
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function adminParseToken($token): array
    {
        if (!$token || !CacheService::hasToken($token) || !($cacheToken = CacheService::getTokenBucket($token)))
            throw new AuthException('Please login', 410000);

        if (isset($cacheToken['max']) && $cacheToken['max'] >= 3) {
            CacheService::clearToken($token);
            throw new AuthException('Landing overdue', 410001);
        }

        try {
            [$adminInfo, $type] = SystemAdmin::parseToken($token);
            CacheService::setTokenBucket($cacheToken['token'], $cacheToken, $cacheToken['exp']);
        } catch (ExpiredException $e) {
            list($headb64, $bodyb64, $cryptob64) = explode('.', $token);
            $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
            $type = $payload->jti->type;
            $adminInfo = SystemAdmin::where('id', $payload->jti->id)->find();
            if (!$adminInfo) {
                CacheService::clearToken($token);
                throw new AuthException('Landing overdue', 410001);
            }
            if (isset($cacheToken['max'])) {
                $cacheToken['max'] = bcadd($cacheToken['max'], 1, 0);
            } else {
                $cacheToken['max'] = 1;
            }
            CacheService::setTokenBucket($cacheToken['token'], $cacheToken, $cacheToken['exp']);
        } catch (\Throwable $e) {
            CacheService::clearToken($token);
            throw new AuthException('Landing overdue', 410001);
        }

        if (!isset($adminInfo) || !$adminInfo || !$adminInfo->id) {
            CacheService::clearToken($token);
            throw new AuthException('The login status is incorrect. Please login again.', 410002);
        }
        $adminInfo->type = $type;
        return $adminInfo->toArray();

    }
}