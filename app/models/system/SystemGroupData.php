<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/13
 */

namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\models\system\SystemGroup as GroupModel;

/**
 * 数据列表 model
 * Class SystemGroupData
 * @package app\models\system
 */
class SystemGroupData extends BaseModel
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
    protected $name = 'system_group_data';

    use ModelTrait;

    /**
     * 根据where条件获取当前表中的前20条数据
     * @param $params
     * @return array
     */
    public static function getList($params)
    {
        $model = new self;
        if ($params['gid'] !== '') $model = $model->where('gid', $params['gid']);
        if ($params['status'] !== '') $model = $model->where('status', $params['status']);
        $count = $model->count();
        $model = $model->order('sort desc,id ASC');
        $list = $model->page((int)$params['page'], (int)$params['limit'])
            ->select()
            ->each(function ($item) {
                $info = json_decode($item->value, true);
                foreach ($info as $index => $value) {
                    if ($value["type"] == "checkbox") $info[$index]["value"] = implode(",", $value["value"]);
//                if($value["type"] == "upload" || $value["type"] == "uploads"){
//                    $html_img = '';
//                    if(is_array($value["value"])){
//                        foreach ($value["value"] as $img) {
//                            $html_img .= '<img class="image" data-image="'.$img.'" width="45" height="45" src="'.$img.'" /><br>';
//                        }
//                    }else{
//                        $html_img = '<img class="image" data-image="'.$value["value"].'" width="45" height="45" src="'.$value["value"].'" />';
//                    }
//                    $info[$index]["value"] = $html_img;
//                }
                }
                $item['value'] = $info;
            });
        $header = json_decode(GroupModel::where('id', $params['gid'])->value("fields"), true);
        foreach ($list as $key => $value) {
            foreach ($header as $item) {
                if ($item['type']=='upload'||$item['type']=='uploads'){
                    $list[$key][$item['title']] = [];
                }else{
                    $list[$key][$item['title']] = '';
                }
            }
            foreach ($value['value'] as $k => $v) {
                if($v['type']=='upload'){
                    $list[$key][$k] = [$v['value']];
                }else{
                    $list[$key][$k] = $v['value'];
                }
            }
            unset($list[$key]['value']);
        }
        $type = '';
        $Fields = GroupModel::getField($params['gid'])['fields'];
        foreach ($Fields as $item) {
            if ($item['type'] === 'upload') {
                $type = $item['title'];
            }
        }
        return compact('count', 'list', 'type');
    }

    /**
     * 获得组合数据信息+组合数据列表
     * @param $config_name
     * @param int $limit
     * @return array|bool|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getGroupData($config_name, $limit = 0)
    {
        $group = SystemGroup::where('config_name', $config_name)->field('name,info,config_name')->find();
        if (!$group) return false;
        $group['data'] = self::getAllValue($config_name, $limit);
        return $group;
    }

    /**
     * 获取单个值
     * @param $config_name
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAllValue($config_name, $limit = 0)
    {
        $model = self::alias('a')->field('a.*,b.config_name')->join('system_group b', 'a.gid = b.id')
            ->where("b.config_name", $config_name)->where("a.status", 1)
            ->order('sort desc,id ASC');
        if ($limit > 0) $model->limit($limit);
        $data = [];
        $result = $model->select();
        if (!$result) return $data;
        foreach ($result as $key => $value) {
            $data[$key]["id"] = $value["id"];
            $fields = json_decode($value["value"], true);
            foreach ($fields as $index => $field) {
//                $data[$key][$index] = $field['type'] == 'upload' ? (isset($field["value"][0]) ? $field["value"][0]: ''):$field["value"];
                $data[$key][$index] = $field["value"];
            }
        }
        return $data;
    }

    /**
     * @param $result
     * @return array
     */
    public static function tidyList($result)
    {
        $data = [];
        if (!$result) return $data;
        foreach ($result as $key => $value) {
            $data[$key]["id"] = $value["id"];
            $fields = json_decode($value["value"], true);
            foreach ($fields as $index => $field) {
                $data[$key][$index] = $field['type'] == 'upload' ? (isset($field["value"][0]) ? $field["value"][0] : '') : $field["value"];
            }
        }
        return $data;
    }


    /**
     * 根据id获取当前记录中的数据
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDateValue($id)
    {
        $value = self::alias('a')->where(array("id" => $id))->find();
        $data["id"] = $value["id"];
        $fields = json_decode($value["value"], true);
        foreach ($fields as $index => $field) {
            $data[$index] = $field["value"];
        }
        return $data;
    }

    /**
     * 获取列表头
     * @param $gid
     * @return array
     */
    public static function getHeader($gid)
    {
        $data = json_decode(GroupModel::where('id', $gid)->value("fields"), true) ?: [];
        foreach ($data as $key => $item) {
            if ($item['type'] == 'upload' || $item['type'] == 'uploads') {
                $header[$key]['key'] = $item['title'];
                $header[$key]['minWidth'] = 60;
                $header[$key]['type'] = 'img';
            } elseif ($item['title'] == 'url' || $item['title'] == 'wap_url' || $item['title'] == 'link' || $item['title'] == 'wap_link') {
                $header[$key]['key'] = $item['title'];
                $header[$key]['minWidth'] = 200;
            } else {
                $header[$key]['key'] = $item['title'];
                $header[$key]['minWidth'] = 100;
            }
            $header[$key]['title'] = $item['name'];

        }
        array_unshift($header, ['key' => 'id', 'title' => '编号', 'minWidth' => 35]);
        array_push($header, ['slot' => 'status', 'title' => '是否可用', 'minWidth' => 80], ['slot' => 'action', 'fixed' => 'right', 'title' => '操作', 'minWidth' => 120]);
        return compact('header');
    }
}