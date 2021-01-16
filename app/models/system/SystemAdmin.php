<?php

namespace app\models\system;

use crmeb\services\CacheService;
use crmeb\traits\JwtAuthModelTrait;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Cache;

/**
 * Class SystemAdmin
 * @package app\models\system
 */
class SystemAdmin extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_admin';

    /**
     * token令牌桶名
     * @var string
     */
    protected static $tokenPrefix = 'admin_token';

    use ModelTrait;
    use JwtAuthModelTrait;

    protected $insert = ['add_time'];

    /**
     * 获取token缓存前缀
     * @return string
     */
    public static function getTokenPrefix()
    {
        return self::$tokenPrefix;
    }

    public static function getRolesAttr($value)
    {
        return explode(',', $value);
    }

    /**
     * 获取token并放入令牌桶
     * @param SystemAdmin $adminInfo
     * @param $type
     * @return array|bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function createToken(SystemAdmin $adminInfo, string $type, array $params = [])
    {
        $tokenInfo = $adminInfo->getToken($type, $params);
        $exp = (int)bcadd($tokenInfo['params']['exp'] - $tokenInfo['params']['iat'], 60, 0);
        $res = CacheService::setTokenBucket($tokenInfo['token'], ['token' => $tokenInfo['token'], 'exp' => $exp], (int)$exp);
        if (!$res) return self::setErrorInfo('保存token失败');
        return $tokenInfo;
    }

    /**
     * 从令牌桶中删除token
     * @param string $token
     * @param string $tokenPrefix
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function delTokenBucket(string $token)
    {
        if (Cache::has($token)) {
            return Cache::delete($token);
        }
        return true;
    }

    /**
     * 用户登陆
     * @param $account
     * @param $pwd
     * @return bool
     */
    public static function login($account, $pwd)
    {
        $adminInfo = self::get(compact('account'));
        if (!$adminInfo) return self::setErrorInfo('登陆的账号不存在!');
        if (!password_verify($pwd, $adminInfo['pwd'])) return self::setErrorInfo('账号或密码错误，请重新输入');
        if (!$adminInfo['status']) return self::setErrorInfo('该账号已被关闭!');
        event('SystemAdminLoginAfter', [$adminInfo]);
        return $adminInfo;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public static function activeAdminAuthOrFail($adminInfo)
    {
        if (is_object($adminInfo)) $adminInfo = $adminInfo->toArray();
        return $adminInfo['level'] === 0 ? SystemRole::getAllRule() : SystemRole::rolesByAuth($adminInfo['roles']);
    }

    /**
     * 获取当前管理员等级下的所有管理员
     * @param $name
     * @param $level
     * @param $roles
     * @param $page
     * @param $limit
     * @return array
     */
    public static function getAdminList($name, $level, $roles, $page, $limit)
    {
        $model = self::where('level', $level)->where('is_del', 0);
        if ($name) $model = $model->where('account|real_name', 'LIKE', "%$name%");
        if ($roles) $model = $model->where("CONCAT(',',roles,',')  LIKE '%,$roles,%'");
        $list = $model->hidden(['pwd'])->page($page, $limit)->select();
        $list = count($list) ? $list->toArray() : [];
        foreach ($list as &$item) {
            $roles = SystemRole::whereIn('id', $item['roles'])->column('role_name');
            $item['roles'] = implode(',', $roles);
            $item['_add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['_last_time'] = $item['last_time'] ? date('Y-m-d H:i:s', $item['last_time']) : '';
        }
        $count = self::where('level', $level)->where('is_del', 0)->count();
        return compact('list','count');
    }

    /**
     * @param string $field
     * @param int $level
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOrdAdmin($field = 'real_name,id', $level = 0)
    {
        return self::where('level', '>=', $level)->field($field)->select();
    }

}