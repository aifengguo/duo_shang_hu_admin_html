<?php


namespace app\adminapi\middleware;


use app\Request;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\repositories\AdminRepository;
use think\facade\Config;

class AdminAuthTokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        $authInfo = null;
        $token = trim(ltrim($request->header(Config::get('cookie.token_name','Authori-zation')), 'Bearer'));

        $adminInfo = AdminRepository::adminParseToken($token);

        Request::macro('isAdminLogin', function () use (&$adminInfo) {
            return !is_null($adminInfo);
        });
        Request::macro('adminId', function () use (&$adminInfo) {
            return $adminInfo['id'];
        });

        Request::macro('adminInfo', function () use (&$adminInfo) {
            return $adminInfo;
        });

        return $next($request);
    }
}