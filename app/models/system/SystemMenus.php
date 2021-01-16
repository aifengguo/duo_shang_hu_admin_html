<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\utils\Arr;

/**
 * 菜单  model
 * Class SystemMenus
 * @package app\models\system
 */
class SystemMenus extends BaseModel
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
    protected $name = 'system_menus';

    use ModelTrait;

    public static $isShowStatus = [1 => '显示', 0 => '不显示'];

    public static $accessStatus = [1 => '管理员可用', 0 => '管理员不可用'];

    public static function legalWhere($where = [])
    {
        $where['is_show'] = 1;
    }

    public function setParamsAttr($value)
    {
        $value = $value ? explode('/', $value) : [];
        $params = array_chunk($value, 2);
        $data = [];
        foreach ($params as $param) {
            if (isset($param[0]) && isset($param[1])) $data[$param[0]] = $param[1];
        }
        return json_encode($data);
    }

    protected function setControllerAttr($value)
    {
        return lcfirst($value);
    }

    public function getParamsAttr($_value)
    {
        return json_decode($_value, true);
    }

    public function getPidAttr($value)
    {
        return !$value ? '顶级' : self::get($value)['menu_name'];
    }

    /**
     * @param string $field
     * @param bool $filter
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getParentMenu($field = '*', $filter = false)
    {
        $where = ['pid' => 0];
        $query = self::field($field);
        $query = $filter ? $query->where(self::legalWhere($where)) : $query->where($where);
        return $query->order('sort DESC')->select();
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function menuList()
    {
        $menusList = self::where('is_show', '1')->where('access', '1')->order('sort DESC')->select();
        return self::tidyMenuTier(true, $menusList);
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function ruleList()
    {
        $ruleList = self::where('is_del', 0)->order('sort DESC')->select();
        return self::tidyMenuTier(false, $ruleList);
    }

    /**
     * @param $rules
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rolesByRuleList($rules)
    {
        $res = SystemRole::where('id', 'IN', $rules)->field('GROUP_CONCAT(rules) as ids')->find();
        $ruleList = self::where('id', 'IN', $res['ids'])->where('is_del', 0)->whereOr('pid', 0)->order('sort DESC')->select();
        return self::tidyMenuTier(false, $ruleList);
    }

    /**
     * @param $action
     * @param $controller
     * @param $module
     * @param $route
     * @return string
     */
    public static function getAuthName($action, $controller, $module, $route)
    {
        return strtolower($module . '/' . $controller . '/' . $action . '/' . SystemMenus::paramStr($route));
    }

    /**
     * 截取掉版本号
     * @param $controller
     * @return string
     */
    public static function unEditionCode($controller)
    {
        $nowController = preg_replace('/^v[0-9]{1}+(\.|\\/)/', '', $controller);
        return is_null($nowController) ? $controller : $nowController;
    }

    /**
     * @param bool $adminFilter
     * @param $menusList
     * @param int $pid
     * @param array $navList
     * @return array
     * @throws \Exception
     */
    public static function tidyMenuTier($adminFilter = false, $menusList, $pid = 0, $navList = [])
    {
        static $allAuth = null;
        static $adminAuth = null;
        if ($allAuth === null) $allAuth = $adminFilter == true ? SystemRole::getAllAuth() : [];//所有的菜单
        if ($adminAuth === null) $adminAuth = $adminFilter == true ? SystemAdmin::activeAdminAuthOrFail() : [];//当前登录用户的菜单
        foreach ($menusList as $k => $menu) {
            $menu = $menu->getData();
            $menu['title'] = $menu['menu_name'];
            unset($menu['menu_name']);
            if ($menu['pid'] == $pid) {
                unset($menusList[$k]);
                $params = json_decode($menu['params'], true);//获取参数
                $authName = self::getAuthName($menu['action'], $menu['controller'], $menu['module'], $params);// 按钮链接
                if ($menu['auth_type'] == 1 && $pid != 0 && $adminFilter && in_array($authName, $allAuth) && !in_array($authName, $adminAuth)) continue;
                $menu['children'] = self::tidyMenuTier($adminFilter, $menusList, $menu['id']);
                if ($pid == 0 && !count($menu['children'])) continue;
                if ($menu['children']) $menu['expand'] = true;
                $navList[] = $menu;
            }
        }
        return $navList;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function delMenu($id)
    {
        if (self::where('pid', $id)->count())
            return self::setErrorInfo('请先删除改菜单下的子菜单!');
        return self::del($id);
    }

    /**
     * @param $params
     * @return array
     */
    public static function getAdminList($params)
    {
        $model = new self;
        if ($params['is_show'] !== '') $model = $model->where('is_show', $params['is_show']);
        if ($params['pid'] !== '' && !$params['keyword']) $model = $model->where('pid', $params['pid']);
        if ($params['keyword'] !== '') $model = $model->where('menu_name|id|pid', 'LIKE', "%$params[keyword]%");
        $list = $model->order('sort DESC,id ASC')->select();
        return count($list) ? $list->toArray() : [];
    }

    /**
     * @param $params
     * @return string
     */
    public static function paramStr($params)
    {
        if (!is_array($params)) $params = json_decode($params, true) ?: [];
        $p = [];
        foreach ($params as $key => $param) {
            $p[] = $key;
            $p[] = $param;
        }
        return implode('/', $p);
    }

    /**
     * @param $params
     * @return array
     */
    public static function getAuthList($params)
    {
        $model = new self;
        if ($params['is_show'] !== '') $model = $model->where('is_show', $params['is_show']);
        if ($params['pid'] !== '' && !$params['keyword']) $model = $model->where('pid', $params['pid']);
        if ($params['keyword'] !== '') $model = $model->where('menu_name|id|pid', 'LIKE', "%$params[keyword]%");
        $model = $model->where('is_del', 0);
        $list = $model->order('sort DESC,id ASC')->select();
        return get_tree_children(self::getMenusArray($list));
    }

    /**
     * @param $list
     * @return array
     */
    public static function getMenusArray($list)
    {
        $menusValue = [];
        foreach ($list as $item) {
            $menusValue[] = $item->getData();
        }
        return $menusValue;
    }

    /**
     * @param $action
     * @param $controller
     * @param $module
     * @param array $route
     * @return mixed
     */
    public static function getVisitName($action, $controller, $module, array $route = [])
    {
        $params = json_encode($route);
        return self::where('action', $action)
            ->where('controller', lcfirst($controller))
            ->where('module', lcfirst($module))
            ->where('params', $params)
            ->where("params = '$params' OR params = '[]'")
            ->order('id DESC')
            ->value('menu_name');
    }

    /**
     * 返回给前端权限唯一标识
     * @param $roule_id
     * @param $level
     * @return array
     */
    public function getUniqueAuth($roule_id, $level)
    {
        $rules = (new SystemRole())->getRoleIds($roule_id);
        if (!$rules) return [];
        $authModel = $this->where('is_del', 0);
        if ($level) $authModel = $authModel->whereIn('id', $rules);
        return array_values($authModel->column('unique_auth', 'unique_auth'));
    }

    /**
     * 获取admin端用户菜单
     * @param $roule_id
     * @param $level
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoute($roule_id, $level)
    {
        $rules = (new SystemRole())->getRoleIds($roule_id);
        if (!$rules) return [];
        $authModel = $this->where('auth_type', 1)->where('is_show', 1)->where('is_del', 0)->order('sort desc,id desc');
        if ($level) $authModel = $authModel->whereIn('id', $rules);
        $menus = $authModel->field('id,menu_name,icon,pid,sort,menu_path,is_show,header,is_header,is_show_path')->order('sort desc')->select();
        $data = [];
        foreach ($menus as $item) {
            $data[] = $item->getData();
        }
        return Arr::toIviewUi(Arr::getTree($data));
    }

}