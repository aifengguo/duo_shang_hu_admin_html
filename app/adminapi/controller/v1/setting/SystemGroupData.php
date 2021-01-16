<?php

namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use think\Request;
use crmeb\services\{
    FormBuilder as Form, UtilService as Util
};
use think\facade\Route as Url;
use app\models\system\{
    SystemGroup as GroupModel, SystemGroupData as GroupDataModel
};

/**
 * 数据管理
 * Class SystemGroupData
 * @package app\adminapi\controller\v1\setting
 */
class SystemGroupData extends AuthController
{
    /**
     * 获取数据列表头
     * @return mixed
     */
    public function header()
    {
        $gid = $this->request->param('gid');
        if (!$gid) return $this->fail('参数错误');
        $header = GroupDataModel::getHeader($gid);
        return $this->success($header);
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['gid', 0],
            ['page', 1],
            ['limit', 15],
            ['status', ''],
        ], $this->request);
        $list = GroupDataModel::getList($where);
        return $this->success($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $gid = $this->request->param('gid');
        $Fields = GroupModel::getField($gid);
        $f = array();
        $f[] = Form::hidden('gid', $gid);
        foreach ($Fields["fields"] as $key => $value) {
            $info = [];
            if (isset($value["param"])) {
                $value["param"] = str_replace("\r\n", "\n", $value["param"]);//防止不兼容
                $params = explode("\n", $value["param"]);
                if (is_array($params) && !empty($params)) {
                    foreach ($params as $index => $v) {
                        $vl = explode('=>', $v);
                        if (isset($vl[0]) && isset($vl[1])) {
                            $info[$index]["value"] = $vl[0];
                            $info[$index]["label"] = $vl[1];
                        }
                    }
                }
            }

            switch ($value["type"]) {
                case 'input':
                    $f[] = Form::input($value["title"], $value["name"]);
                    break;
                case 'textarea':
                    $f[] = Form::input($value["title"], $value["name"])->type('textarea')->placeholder($value['param']);
                    break;
                case 'radio':
                    $f[] = Form::radio($value["title"], $value["name"], $info[0]["value"] ?? '')->options($info);
                    break;
                case 'checkbox':
                    $f[] = Form::checkbox($value["title"], $value["name"], $info[0] ?? '')->options($info);
                    break;
                case 'select':
                    $f[] = Form::select($value["title"], $value["name"], $info[0] ?? '')->options($info)->multiple(false);
                    break;
                case 'upload':
                    $f[] = Form::frameImageOne($value["title"], $value["name"], Url::buildUrl('admin/widget.images/index', array('fodder' => $value["title"], 'big' => 1)))->icon('ios-image')->width('60%')->height('435px');
                    break;
                case 'uploads':
                    $f[] = Form::frameImages($value["title"], $value["name"], Url::buildUrl('admin/widget.images/index', array('fodder' => $value["title"], 'big' => 1)))->maxLength(5)->icon('ios-images')->width('60%')->height('435px')->spin(0);
                    break;
                default:
                    $f[] = Form::input($value["title"], $value["name"]);
                    break;

            }
        }
        $f[] = Form::number('sort', '排序', 1);
        $f[] = Form::radio('status', '状态', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        return $this->makePostForm('添加数据', $f, Url::buildUrl('/setting/group_data'), 'POST');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $params = request()->post();
        $Fields = GroupModel::getField($params['gid']);
        foreach ($params as $key => $param) {
            foreach ($Fields['fields'] as $index => $field) {
                if ($key == $field["title"]) {
//                    if($param == "" || count($param) == 0)
                    if ($param == "")
                        return $this->fail($field["name"] . "不能为空！");
                    else {
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $param;
                    }
                }
            }
        }
        $data = array("gid" => $params['gid'], "add_time" => time(), "value" => json_encode($value), "sort" => $params["sort"], "status" => $params["status"]);
        GroupDataModel::create($data);
        \crmeb\services\CacheService::clear();
        return $this->success('添加数据成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $gid = $this->request->param('gid');
        $GroupData = GroupDataModel::get($id);
        $GroupDataValue = json_decode($GroupData["value"], true);
        $Fields = GroupModel::getField($gid);
        $f = array();
        $f[] = Form::hidden('gid', $gid);
        if (!isset($Fields['fields'])) return $this->fail('数据解析失败！');
        foreach ($Fields['fields'] as $key => $value) {
            $info = [];
            if (isset($value["param"])) {
                $value["param"] = str_replace("\r\n", "\n", $value["param"]);//防止不兼容
                $params = explode("\n", $value["param"]);
                if (is_array($params) && !empty($params)) {
                    foreach ($params as $index => $v) {
                        $vl = explode('=>', $v);
                        if (isset($vl[0]) && isset($vl[1])) {
                            $info[$index]["value"] = $vl[0];
                            $info[$index]["label"] = $vl[1];
                        }
                    }
                }
            }
            $fvalue = isset($GroupDataValue[$value['title']]['value']) ? $GroupDataValue[$value['title']]['value'] : '';
            switch ($value['type']) {
                case 'input':
                    $f[] = Form::input($value['title'], $value['name'], $fvalue);
                    break;
                case 'textarea':
                    $f[] = Form::input($value['title'], $value['name'], $fvalue)->type('textarea');
                    break;
                case 'radio':

                    $f[] = Form::radio($value['title'], $value['name'], $fvalue)->options($info);
                    break;
                case 'checkbox':
                    $f[] = Form::checkbox($value['title'], $value['name'], $fvalue)->options($info);
                    break;
                case 'upload':
                    if (!empty($fvalue)) {
                        $image = is_string($fvalue) ? $fvalue : $fvalue[0];
                    } else {
                        $image = '';
                    }
                    $f[] = Form::frameImageOne($value['title'], $value['name'], Url::buildUrl('admin/widget.images/index', array('fodder' => $value['title'], 'big' => 1)), $image)->icon('ios-image')->width('60%')->height('435px');
                    break;
                case 'uploads':
                    if (is_string($fvalue)) $fvalue = [$fvalue];
                    $images = !empty($fvalue) ? $fvalue : [];
                    $f[] = Form::frameImages($value['title'], $value['name'], Url::buildUrl('admin/widget.images/index', array('fodder' => $value['title'], 'big' => 1, 'maxLength' => 5)), $images)->maxLength(5)->icon('ios-images')->width('60%')->height('435px')->spin(0);
                    break;
                case 'select':
                    $f[] = Form::select($value['title'], $value['name'], $fvalue)->setOptions($info);
                    break;
                default:
                    $f[] = Form::input($value['title'], $value['name'], $fvalue);
                    break;

            }
        }
        $f[] = Form::number('sort', '排序', $GroupData["sort"]);
        $f[] = Form::radio('status', '状态', $GroupData["status"])->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        return $this->makePostForm('编辑数据', $f, Url::buildUrl('/setting/group_data/' . $id), 'PUT');
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $GroupData = GroupDataModel::get($id);
        $Fields = GroupModel::getField($GroupData["gid"]);
        $params = request()->post();
        foreach ($params as $key => $param) {
            foreach ($Fields['fields'] as $index => $field) {
                if ($key == $field["title"]) {
                    if ($param == '')
                        return $this->fail($field["name"] . "不能为空！");
                    else {
                        $value[$key]["type"] = $field["type"];
                        $value[$key]["value"] = $param;
                    }
                }
            }
        }
        $data = ["value" => json_encode($value), "sort" => $params["sort"], "status" => $params["status"]];
        GroupDataModel::edit($data, $id);
        \crmeb\services\CacheService::clear();
        return $this->success('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!GroupDataModel::del($id))
            return $this->fail(GroupDataModel::getErrorInfo('删除失败,请稍候再试!'));
        else {
            \crmeb\services\CacheService::clear();
            return $this->success('删除成功!');
        }
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return $this->fail('参数错误');
        GroupDataModel::where(['id' => $id])->update(['status' => $status]);
        \crmeb\services\CacheService::clear();
        return $this->success($status == 0 ? '隐藏成功' : '显示成功');
    }
}
