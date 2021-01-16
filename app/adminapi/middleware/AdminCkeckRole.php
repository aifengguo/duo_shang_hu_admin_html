<?php


namespace app\adminapi\middleware;

use app\Request;
use crmeb\exceptions\AuthException;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\repositories\AuthRepository;
use crmeb\utils\ApiErrorCode;

/**
 * 权限规则验证
 * Class AdminCkeckRole
 * @package app\http\middleware
 */
class AdminCkeckRole implements MiddlewareInterface
{

    public function handle(Request $request, \Closure $next)
    {
        if (!$request->adminId() || !$request->adminInfo())
            throw new AuthException(ApiErrorCode::ERR_ADMINID_VOID);

        if ($request->adminInfo()['level']) {
            AuthRepository::verifiAuth($request);
        }

        return $next($request);
    }
}