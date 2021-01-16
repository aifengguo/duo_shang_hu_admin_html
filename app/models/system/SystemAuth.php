<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\models\system;

use crmeb\services\UtilService as Util;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Route as Url;

/**
 * 菜单  model
 * Class SystemMenus
 * @package app\models\system
 */
class SystemAuth extends BaseModel
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
    protected $name = 'system_auth';

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

    /**
     * @param $id
     * @return bool
     */
    public static function delMenu($id)
    {
        if (self::where('pid', $id)->where(['is_del' => 0])->count())
            return self::setErrorInfo('请先删除改菜单下的子菜单!');
        return self::where('id', $id)->update(['is_del' => 1]);
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
        return get_tree_children($list->toArray());
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

}