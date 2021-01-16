<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/13
 */

namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Cache;
use think\facade\Db;

/**
 * 身份管理 model
 * Class SystemRole
 * @package app\models\system
 */
class SystemRole extends BaseModel
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
    protected $name = 'system_role';

    /**
     * 所有权限缓存前缀
     */
    const ADMIN_RULES_ALL = 'Admin_rules_all_';

    /**
     * 当前管理员权限缓存前缀
     */
    const ADMIN_RULES_LEVEL = 'Admin_rules_level_';

    /**
     * 验证规则缓存
     * @var bool
     */
    protected static $isCache = true;

    use ModelTrait;

    public static function setRulesAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * 选择管理员身份
     * @param int $level
     * @return array
     */
    public static function getRole($level = 0)
    {

        return self::where('status', 1)->where('level', $level)->column('role_name', 'id');
    }

    public static function getAllRule(string $module, $cachePrefix = self::ADMIN_RULES_ALL): array
    {
        static $auth = null;
        if (Cache::has($cachePrefix) && ($auth = Cache::get($cachePrefix)) && !self::$isCache) return $auth;
        $auth === null && ($auth = SystemMenus::where('module', $module)->where('rule', '<>', '')->column('rule', 'rule'));
        if (is_null($auth))
            $auth = [];
        else
            Cache::set($cachePrefix, $auth);
        return $auth;
    }

    /**
     * 获取当前管理员可访问的权限
     * @param $rules
     * @return array
     */
    public static function getRolesByAuthRule($module, $rules, $cachePrefix = self::ADMIN_RULES_LEVEL)
    {
        $cacheName = $cachePrefix . $rules;
        if (empty($rules)) return [];
        if (Cache::has($cacheName) && ($_auth = Cache::get($cacheName)) && !self::$isCache) return $_auth;
        $rules = self::where('id', 'IN', $rules)->where('status', '1')->column('rules', 'id');
        $rules = array_unique(explode(',', implode(',', $rules)));
        $_auth = SystemMenus::where('module', $module)->whereIn('id', $rules)->column('rule', 'rule');
        if (is_null($_auth))
            $_auth = [];
        else
            Cache::set($cacheName, $_auth);
        return $_auth;
    }


    public static function rolesByAuth($rules, $type = 1, $cachePrefix = self::ADMIN_RULES_LEVEL)
    {
        $cacheName = $cachePrefix . $rules . '_' . $type;
        if (empty($rules)) return [];
        if (Cache::has($cacheName) && ($_auth = Cache::get($cacheName)) && !self::$isCache) return $_auth;
        $rules = self::where('id', 'IN', $rules)->where('status', '1')->column('rules', 'id');
        $rules = array_unique(explode(',', implode(',', $rules)));
        $_auth = SystemMenus::all(function ($query) use ($rules, $type) {
            $query->where('id', 'IN', $rules)->where('type', $type)
                ->where('controller|action', '<>', '')
                ->field('module,controller,action,params');
        });
        $_auth = self::tidyAuth($_auth ?: []);
        if (count($_auth)) Cache::set($cacheName, $_auth);
        return $_auth;
    }

    public static function getAllAuth($type = 1, $cachePrefix = self::ADMIN_RULES_ALL)
    {
        $cachePrefix = $cachePrefix . $type;
        static $auth = null;
        $auth === null && ($auth = SystemMenus::where('api_url', '<>', '')->where('auth_type', $type)->column('api_url', 'methods'));
        return $auth;
    }

    /**
     * 获取所有权限
     * @param int $type
     * @param string $cachePrefix
     * @return mixed
     */
    public function getAllRuleAuth(int $type = 1, string $cachePrefix = self::ADMIN_RULES_ALL)
    {
        $cachePrefix = $cachePrefix . $type;
        return Cache::remember($cachePrefix, function () use ($type) {
            return SystemMenus::where('api_url', '<>', '')->where('auth_type', $type)->field(['api_url', 'methods'])->select()->toArray();
        });
    }

    /**
     * 获取指定权限
     * @param array $rules
     * @param int $type
     * @param string $cachePrefix
     * @return mixed
     */
    public function getRolesByAuth(array $rules, int $type = 1, string $cachePrefix = self::ADMIN_RULES_LEVEL)
    {
        if (empty($rules)) return [];
        $cacheName = $cachePrefix . '_' . $type . '_' . implode('_', $rules);
        return Cache::remember($cacheName, function () use ($rules, $type) {
            return SystemMenus::whereIn('id', $this->getRoleIds($rules))->where('auth_type', $type)->field(['api_url', 'methods'])->select()->toArray();
        });
    }

    /**
     * 获取权限id
     * @param array $rules
     * @return array
     */
    public function getRoleIds(array $rules)
    {
        $rules = self::where('id', 'IN', $rules)->where('status', '1')->column('rules', 'id');
        return array_unique(explode(',', implode(',', $rules)));
    }

    protected static function tidyAuth($_auth)
    {
        $auth = [];
        foreach ($_auth as $k => $val) {
            $auth[] = SystemMenus::getAuthName($val['action'], $val['controller'], $val['module'], $val['params']);
        }
        return $auth;
    }

    public static function systemPage($where)
    {
        $model = new self;
        if (strlen(trim($where['role_name']))) $model = $model->where('role_name', 'LIKE', "%$where[role_name]%");
        if (strlen(trim($where['status']))) $model = $model->where('status', $where['status']);
        $model = $model->where('level', bcadd($where['level'], 1, 0));
        $model = $model->order('id desc');
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each((function ($item) {
                $item->rules = SystemMenus::where('id', 'IN', $item->rules)->column('menu_name', 'id');
            }));
        return compact('count', 'list');
    }

}