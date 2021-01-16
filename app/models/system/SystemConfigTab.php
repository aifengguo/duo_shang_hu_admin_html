<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Db;

/**
 * 配置分类model
 * Class SystemConfigTab
 * @package app\models\system
 */
class SystemConfigTab extends BaseModel
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
    protected $name = 'system_config_tab';

    use ModelTrait;

    /**
     * @param int $type
     * @return \think\Collection
     */
    public static function getChildrenTab($pid)
    {
        $model = new self;
        $where['status'] = 1;
        $where['pid'] = $pid;
        return $model::where($where)->order('sort desc,id asc')->select();
    }

    /**
     * 获取单选按钮或者多选按钮的显示值
     * @param $menu_name
     * @param $value
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRadioOrCheckboxValueInfo($menu_name, $value)
    {
        $parameter = array();
        $option = array();
        $config_one = SystemConfig::getOneConfig('menu_name', $menu_name);
        $parameter = explode("\n", $config_one['parameter']);
        foreach ($parameter as $k => $v) {
            if (isset($v) && strlen($v) > 0) {
                $data = explode('=>', $v);
                $option[$data[0]] = $data[1];
            }
        }
        $str = '';
        if (is_array($value)) {
            foreach ($value as $v) {
                $str .= $option[$v] . ',';
            }
        } else {
            $str .= !empty($value) ? $option[$value] : $option[0];
        }
        return $str;
    }

    /**
     * 获取全部
     * @param int $type
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAll($type = 0)
    {
        $where['status'] = 1;
        if ($type > -1) {
            $where['type'] = $type;
            $where['pid'] = 0;
            return self::where($where)->order('sort desc,id asc')->select();
        }else{
            return sort_list_tier(self::where($where)->order('sort desc,id asc')->select()->toArray());
        }
    }

    /**
     * 获取配置分类
     * @param array $where
     * @return array
     */
    public static function getSystemConfigTabPage($where = array())
    {
        $model = new self;
        if ($where['title'] != '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        $count = $model->count();
        $list = $model->order('sort desc')->select();
        $list = get_tree_children(self::getMenusArray($list));
        return compact('count', 'list');
    }

    public static function getMenusArray($list)
    {
        $menusValue = [];
        foreach ($list as $item) {
            $menusValue[] = $item->getData();
        }
        return $menusValue;
    }
}