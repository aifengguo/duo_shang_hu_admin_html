<?php

namespace crmeb\repositories;

use app\models\system\SystemRole;
use crmeb\utils\ApiErrorCode;
use crmeb\exceptions\AuthException;
use app\Request;

class AuthRepository
{
    /**
     * 验证权限
     * @param Request $request
     */
    public static function verifiAuth(Request $request)
    {
        $auth = (new SystemRole())->getRolesByAuth($request->adminInfo()['roles'], 2);
        $rule = trim(strtolower($request->rule()->getRule()));
        $method = trim(strtolower($request->method()));
        if ($rule == 'setting/admin/logout') {
            return true;
        }
        //验证访问接口是否存在
        if (!in_array($rule, array_map(function ($item) {
            return trim(strtolower(str_replace(' ', '', $item)));
        }, array_column($auth, 'api_url')))) {
            throw new AuthException(ApiErrorCode::ERR_RULE);
        }
        //验证访问接口是否有权限
        if (empty(array_filter($auth, function ($item) use ($rule, $method) {
            if (trim(strtolower($item['api_url'])) === $rule && $method === trim(strtolower($item['methods'])))
                return true;
        }))) {
            throw new AuthException(ApiErrorCode::ERR_AUTH);
        }
    }

}