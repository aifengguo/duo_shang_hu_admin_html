<?php

namespace crmeb\utils;

/**
 * 操作数组帮助类
 * Class Arr
 * @package crmeb\utils
 */
class Arr
{
    /**
     * 对数组增加默认值
     * @param array $keys
     * @return array
     */
    public static function getDefaultValue(array $keys, array $configList = [])
    {
        $value = [];
        foreach ($keys as $val) {
            if (is_array($val)) {
                $k = $val[0] ?? '';
                $v = $val[1] ?? '';
            } else {
                $k = $val;
                $v = '';
            }
            $value[$k] = $configList[$k] ?? $v;
        }
        return $value;
    }

    /**
     * 转化iviewUi需要的key值
     * @param $data
     * @return array
     */
    public static function toIviewUi($data)
    {
        $newData = [];
        foreach ($data as $k => $v) {
            $temp = [];
            $temp['path'] = $v['menu_path'];
            $temp['title'] = $v['menu_name'];
            $temp['icon'] = $v['icon'];
            $temp['header'] = $v['header'];
            $temp['is_header'] = $v['is_header'];
            if ($v['is_show_path']) {
                $temp['auth'] = ['hidden'];
            }
            if (!empty($v['children'])) {
                $temp['children'] = self::toIviewUi($v['children']);
            }
            $newData[] = $temp;
        }
        return $newData;
    }

    /**
     * 获取树型菜单
     * @param $data
     * @param int $pid
     * @param int $level
     * @return array
     */
    public static function getTree($data, $pid = 0, $level = 1)
    {
        $childs = self::getChild($data, $pid, $level);
        array_multisort(array_column($childs, 'sort'), SORT_DESC, $childs);
        foreach ($childs as $key => $navItem) {
            $resChild = self::getTree($data, $navItem['id']);
            if (null != $resChild) {
                $childs[$key]['children'] = $resChild;
            }
        }
        return $childs;
    }

    /**
     * 获取子菜单
     * @param $arr
     * @param $id
     * @param $lev
     * @return array
     */
    private static function getChild(&$arr, $id, $lev)
    {
        $child = [];
        foreach ($arr as $k => $value) {
            if ($value['pid'] == $id) {
                $value['level'] = $lev;
                $child[] = $value;
            }
        }
        return $child;
    }

    /**
     * 格式化数据
     * @param array $array
     * @param $value
     * @param int $default
     * @return mixed
     */
    public static function setValeTime(array $array, $value, $default = 0)
    {
        foreach ($array as $item) {
            if (!isset($value[$item]))
                $value[$item] = $default;
            else if (is_string($value[$item]))
                $value[$item] = (float)$value[$item];
        }
        return $value;
    }
}