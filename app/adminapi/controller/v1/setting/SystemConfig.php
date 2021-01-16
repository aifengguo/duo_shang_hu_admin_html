<?php

namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use think\Request;
use think\facade\Route as Url;
use crmeb\services\{
    FormBuilder as Form, UtilService as Util
};
use app\models\system\{
    SystemConfigTab as ConfigTabModel, SystemConfig as ConfigModel
};

class SystemConfig extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['tab_id', 0],
            ['page', 1],
            ['limit', 15]
        ]);
        if (!$where['tab_id']) return $this->fail('参数错误');
        $list = ConfigModel::getPageList($where);
        return $this->success($list);
    }

    /**
     * 显示创建资源表单页.
     * @param $type
     * @return \think\Response
     */
    public function create()
    {
        $data = Util::getMore(['type',]);//接收参数
        $tab_id = !empty(request()->param('tab_id')) ? request()->param('tab_id') : 1;
        $formbuider = array();
        switch ($data['type']) {
            case 0://文本框
                $formbuider = ConfigModel::createInputRule($tab_id);
                break;
            case 1://多行文本框
                $formbuider = ConfigModel::createTextAreaRule($tab_id);
                break;
            case 2://单选框
                $formbuider = ConfigModel::createRadioRule($tab_id);
                break;
            case 3://文件上传
                $formbuider = ConfigModel::createUploadRule($tab_id);
                break;
            case 4://多选框
                $formbuider = ConfigModel::createCheckboxRule($tab_id);
                break;
            case 5://下拉框
                $formbuider = ConfigModel::createSelectRule($tab_id);
                break;
        }
        return $this->makePostForm('添加字段', $formbuider, Url::buildUrl('adminapi/setting/config')->domain(true)->build(), 'POST');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'menu_name',
            'type',
            'input_type',
            'config_tab_id',
            'parameter',
            'upload_type',
            'required',
            'width',
            'high',
            'value',
            'info',
            'desc',
            'sort',
            'status',]);
        if (!$data['info']) return $this->fail('请输入配置名称');
        if (!$data['menu_name']) return $this->fail('请输入字段名称');
        if ($data['menu_name']) {
            $oneConfig = ConfigModel::getOneConfig('menu_name', $data['menu_name']);
            if (!empty($oneConfig)) return $this->fail('请重新输入字段名称,之前的已经使用过了');
        }
        if (!$data['desc']) return $this->fail('请输入配置简介');
        if ($data['sort'] < 0) {
            $data['sort'] = 0;
        }
        if ($data['type'] == 'text') {
            if (!ConfigModel::valiDateTextRole($data)) return $this->fail(ConfigModel::getErrorInfo());
        }
        if ($data['type'] == 'textarea') {
            if (!ConfigModel::valiDateTextareaRole($data)) return $this->fail(ConfigModel::getErrorInfo());
        }
        if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
            if (!$data['parameter']) return $this->fail('请输入配置参数');
            if (!ConfigModel::valiDateRadioAndCheckbox($data)) return $this->fail(ConfigModel::getErrorInfo());
            $data['value'] = json_encode($data['value']);
        }
        ConfigModel::create($data);
        \crmeb\services\CacheService::clear();
        return $this->success('添加配置成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (!$id) return $this->fail('参数错误，请重新打开');
        $info = ConfigModel::getAll($id);
        foreach ($info as $k => $v) {
            if (!is_null(json_decode($v['value'])))
                $info[$k]['value'] = json_decode($v['value'], true);
            if ($v['type'] == 'upload' && !empty($v['value'])) {
                if ($v['upload_type'] == 1 || $v['upload_type'] == 3) $info[$k]['value'] = explode(',', $v['value']);
            }
        }
        return $this->success(compact('info'));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $menu = ConfigModel::get($id)->getData();
        if (!$menu) return $this->fail('数据不存在!');
        $formbuider = array();
        $formbuider[] = Form::input('menu_name', '字段变量', $menu['menu_name'])->disabled(1);
        $formbuider[] = Form::hidden('type', $menu['type']);
        $formbuider[] = Form::select('config_tab_id', '分类', (string)$menu['config_tab_id'])->setOptions(ConfigModel::getConfigTabAll(-1));
        $formbuider[] = Form::input('info', '配置名称', $menu['info'])->autofocus(1);
        $formbuider[] = Form::input('desc', '配置简介', $menu['desc']);
        switch ($menu['type']) {
            case 'text':
                $menu['value'] = json_decode($menu['value'], true);
                $formbuider[] = Form::select('input_type', '类型', $menu['input_type'])->setOptions(ConfigModel::texttype());
                //输入框验证规则
                $formbuider[] = Form::input('value', '默认值', $menu['value']);
                if (!empty($menu['required'])) {
                    $formbuider[] = Form::number('width', '文本框宽(%)', $menu['width']);
                    $formbuider[] = Form::input('required', '验证规则', $menu['required'])->placeholder('多个请用,隔开例如：required:true,url:true');
                }
                break;
            case 'textarea':
                $menu['value'] = json_decode($menu['value'], true);
                //多行文本
                if (!empty($menu['high'])) {
                    $formbuider[] = Form::textarea('value', '默认值', $menu['value'])->rows(5);
                    $formbuider[] = Form::number('width', '文本框宽(%)', $menu['width']);
                    $formbuider[] = Form::number('high', '多行文本框高(%)', $menu['high']);
                } else {
                    $formbuider[] = Form::input('value', '默认值', $menu['value']);
                }
                break;
            case 'radio':
                $menu['value'] = json_decode($menu['value'], true);
                $parameter = explode("\n", $menu['parameter']);
                $options = [];
                if ($parameter) {
                    foreach ($parameter as $v) {
                        $data = explode("=>", $v);
                        $options[] = ['label' => $data[1], 'value' => $data[0]];
                    }
                    $formbuider[] = Form::radio('value', '默认值', $menu['value'])->options($options);
                }
                //单选和多选参数配置
                if (!empty($menu['parameter'])) {
                    $formbuider[] = Form::textarea('parameter', '配置参数', $menu['parameter'])->placeholder("参数方式例如:\n1=白色\n2=红色\n3=黑色");
                }
                break;
            case 'checkbox':
                $menu['value'] = json_decode($menu['value'], true) ?: [];
                $parameter = explode("\n", $menu['parameter']);
                $options = [];
                if ($parameter) {
                    foreach ($parameter as $v) {
                        $data = explode("=>", $v);
                        $options[] = ['label' => $data[1], 'value' => $data[0]];
                    }
                    $formbuider[] = Form::checkbox('value', '默认值', $menu['value'])->options($options);
                }
                //单选和多选参数配置
                if (!empty($menu['parameter'])) {
                    $formbuider[] = Form::textarea('parameter', '配置参数', $menu['parameter'])->placeholder("参数方式例如:\n1=白色\n2=红色\n3=黑色");
                }
                break;
            case 'upload':
                if ($menu['upload_type'] == 1) {
                    $menu['value'] = json_decode($menu['value'], true);
                    $formbuider[] = Form::frameImageOne('value', '图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'value')), (string)$menu['value'])->icon('ios-image')->width('60%')->height('435px');
                } elseif ($menu['upload_type'] == 2) {
                    $menu['value'] = json_decode($menu['value'], true) ?: [];
                    $formbuider[] = Form::frameImages('value', '多图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'value')), $menu['value'])->maxLength(5)->icon('ios-images')->width('60%')->height('435px')->spin(0);
                } else {
                    $menu['value'] = json_decode($menu['value'], true);
                    $formbuider[] = Form::uploadFileOne('value', '文件', Url::buildUrl('/adminapi/file/upload/1')->domain(true)->build(), $menu['value'])->name('file')->headers([
                        'Authori-zation' => $this->request->header('Authori-zation')
                    ]);
                }
                //上传类型选择
                if (!empty($menu['upload_type'])) {
                    $formbuider[] = Form::radio('upload_type', '上传类型', $menu['upload_type'])->options([['value' => 1, 'label' => '单图'], ['value' => 2, 'label' => '多图'], ['value' => 3, 'label' => '文件']]);
                }
                break;

        }
        $formbuider[] = Form::number('sort', '排序', $menu['sort']);
        $formbuider[] = Form::radio('status', '状态', $menu['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        return $this->makePostForm('编辑字段', $formbuider, Url::buildUrl('/setting/config/' . $id), 'PUT');
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
        $type = request()->post('type');
        if ($type == 'text' || $type == 'textarea' || $type == 'radio' || ($type == 'upload' && (request()->post('upload_type') == 1 || request()->post('upload_type') == 3))) {
            $value = request()->post('value');
        } else {
            $value = request()->post('value/a');
        }
        $data = Util::postMore(['status', 'info', 'desc', 'sort', 'config_tab_id', 'required', 'parameter', ['value', $value], 'upload_type', 'input_type']);
        $data['value'] = json_encode($data['value']);
        if (!ConfigModel::get($id)) return $this->fail('编辑的记录不存在!');
        ConfigModel::edit($data, $id);
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
        if (!ConfigModel::del($id))
            return $this->fail(ConfigModel::getErrorInfo('删除失败,请稍候再试!'));
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
        ConfigModel::where(['id' => $id])->update(['status' => $status]);
        \crmeb\services\CacheService::clear();
        return $this->success($status == 0 ? '隐藏成功' : '显示成功');
    }

    /**
     * 基础配置
     * */
    public function edit_basics()
    {
        $tab_id = $this->request->param('tab_id');
        if (!$tab_id) $tab_id = 1;
        $list = ConfigModel::getAll($tab_id);
        $title = ConfigTabModel::where('id', $tab_id)->value('title');
        $formbuider = [];
        foreach ($list as $data) {
            switch ($data['type']) {
                case 'text'://文本框
                    switch ($data['input_type']) {
                        case 'input':
                            $data['value'] = json_decode($data['value'], true) ?: '';
                            $formbuider[] = Form::input($data['menu_name'], $data['info'], $data['value'])->info($data['desc'])->placeholder($data['desc'])->col(13);
                            break;
                        case 'number':
                            $data['value'] = json_decode($data['value'], true) ?: 0;
                            $formbuider[] = Form::number($data['menu_name'], $data['info'], $data['value'])->info($data['desc']);
                            break;
                        case 'dateTime':
                            $formbuider[] = Form::dateTime($data['menu_name'], $data['info'], $data['value'])->info($data['desc']);
                            break;
                        case 'color':
                            $data['value'] = json_decode($data['value'], true) ?: '';
                            $formbuider[] = Form::color($data['menu_name'], $data['info'], $data['value'])->info($data['desc']);
                            break;
                        default:
                            $data['value'] = json_decode($data['value'], true) ?: '';
                            $formbuider[] = Form::input($data['menu_name'], $data['info'], $data['value'])->info($data['desc'])->placeholder($data['desc'])->col(13);
                            break;
                    }
                    break;
                case 'textarea'://多行文本框
                    $data['value'] = json_decode($data['value'], true) ?: '';
                    $formbuider[] = Form::textarea($data['menu_name'], $data['info'], $data['value'])->placeholder($data['desc'])->info($data['desc'])->rows(6)->col(13);
                    break;
                case 'radio'://单选框
                    $data['value'] = json_decode($data['value'], true) ?: '0';
                    $parameter = explode("\n", $data['parameter']);
                    $options = [];
                    if ($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formbuider[] = Form::radio($data['menu_name'], $data['info'], $data['value'])->options($options)->info($data['desc'])->col(13);
                    }
                    break;
                case 'upload'://文件上传
                    switch ($data['upload_type']) {
                        case 1:
                            $data['value'] = json_decode($data['value'], true) ?: '';
                            $formbuider[] = Form::frameImageOne($data['menu_name'], $data['info'], Url::buildUrl('admin/widget.images/index', array('fodder' => $data['menu_name'])), $data['value'])->icon('ios-image')->width('60%')->height('435px')->info($data['desc'])->col(13);
                            break;
                        case 2:
                            $data['value'] = json_decode($data['value'], true) ?: [];
                            $formbuider[] = Form::frameImages($data['menu_name'], $data['info'], Url::buildUrl('admin/widget.images/index', array('fodder' => $data['menu_name'])), $data['value'])->maxLength(5)->icon('ios-image')->width('60%')->height('435px')->info($data['desc'])->col(13);
                            break;
                        case 3:
                            $data['value'] = json_decode($data['value'], true);
                            $formbuider[] = Form::uploadFileOne($data['menu_name'], $data['info'], Url::buildUrl('/adminapi/file/upload/1')->domain(true)->build(), $data['value'])->name('file')->info($data['desc'])->col(13)->headers([
                                'Authori-zation' => $this->request->header('Authori-zation'),
                            ]);
                            break;
                    }

                    break;
                case 'checkbox'://多选框
                    $data['value'] = json_decode($data['value'], true) ?: [];
                    $parameter = explode("\n", $data['parameter']);
                    $options = [];
                    if ($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formbuider[] = Form::checkbox($data['menu_name'], $data['info'], $data['value'])->options($options)->info($data['desc'])->col(13);
                    }
                    break;
                case 'select'://多选框
                    $data['value'] = json_decode($data['value'], true) ?: [];
                    $parameter = explode("\n", $data['parameter']);
                    $options = [];
                    if ($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formbuider[] = Form::select($data['menu_name'], $data['info'], $data['value'])->options($options)->info($data['desc'])->col(13);
                    }
                    break;
            }
        }
        return $this->makePostForm($title, $formbuider, Url::buildUrl('/setting/config/save_basics'), 'POST');
    }

    /**
     * 保存数据    true
     * */
    public function save_basics()
    {
        $post = $this->request->post();
        foreach ($post as $k => $v) {
            if (is_array($v)) {
                $res = ConfigModel::where('menu_name', $k)->column('upload_type', 'type');
                foreach ($res as $kk => $vv) {
                    if ($kk == 'upload') {
                        if ($vv == 1 || $vv == 3) {
                            $post[$k] = $v[0];
                        }
                    }
                }
            }
        }
        foreach ($post as $k => $v) {
            ConfigModel::edit(['value' => json_encode($v)], $k, 'menu_name');
        }
        \crmeb\services\CacheService::clear();
        return $this->success('修改成功');

    }

    public function header_basics()
    {
        [$type] = Util::getMore([
            [['type', 'd'], 0],
        ], $this->request, true);
        if ($type == 3) {//其它分类
            $config_tab = [];
        } else {
            $config_tab = ConfigModel::getConfigTabAll($type);
            if (is_object($config_tab)) {
                $config_tab = $config_tab->toArray();
            }
            foreach ($config_tab as &$item) {
                $item['children'] = ConfigModel::getConfigChildrenTabAll($item['value']);
            }
        }
        return $this->success(compact('config_tab'));
    }

}
