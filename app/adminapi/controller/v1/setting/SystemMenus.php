<?php

namespace app\adminapi\controller\v1\setting;


use app\adminapi\controller\AuthController;
use crmeb\services\{FormBuilder as Form, UtilService, UtilService as Util};
use app\models\system\SystemMenus as SystemMenusAdmin;
use think\facade\Route as Url;
use think\Request;

class SystemMenus extends AuthController
{


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = UtilService::getMore([
            ['is_show', ''],
            ['pid', ''],
            ['keyword', ''],
        ]);
        return $this->success(SystemMenusAdmin::getAuthList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {

        $field[] = Form::input('menu_name', '按钮名称')->required('按钮名称必填');
        $field[] = Form::select('pid', '父级id')->setOptions(function () {
            $list = sort_list_tier(SystemMenusAdmin::getMenusArray(SystemMenusAdmin::all(function ($m) {
                $m->order('sort DESC,id ASC')->where('is_del', 0);
            })), '0', 'pid', 'id');
            $menus = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['menu_name']];
            }
            return $menus;
        })->filterable(1);
        $field[] = Form::select('module', '模块名')->options([['label' => '总后台', 'value' => 'admin']]);
        $field[] = Form::input('controller', '控制器名');
        $field[] = Form::input('action', '方法名');
        $field[] = Form::input('menu_path', '路由名称')->placeholder('请输入前台跳转路由地址')->required('请填写前台路由地址');
        $field[] = Form::input('unique_auth', '权限标识')->placeholder('不填写则后台自动生成');
        $field[] = Form::input('params', '参数')->placeholder('举例:a/123/b/234');
        $field[] = Form::frameInputOne('icon', '图标', Url::buildUrl('admin/widget.widgets/icon', array('fodder' => 'icon')))->icon('md-add')->height('500px');
        $field[] = Form::number('sort', '排序', 0);
        $field[] = Form::radio('auth_type', '类型', 1)->options([['value' => 2, 'label' => '接口'], ['value' => 1, 'label' => '菜单(菜单只显示三级)']]);
        $field[] = Form::radio('is_header', '是否顶部菜单', 0)->options([['value' => 0, 'label' => '否'], ['value' => 1, 'label' => '是']]);
        $field[] = Form::radio('is_show', '状态', 0)->options([['value' => 0, 'label' => '关闭'], ['value' => 1, 'label' => '开启']]);
        $field[] = Form::radio('is_show_path', '是否为前端隐藏菜单', 0)->options([['value' => 1, 'label' => '是'], ['value' => 0, 'label' => '否']]);
        $field[] = Form::select('header', '顶部菜单')->setOptions(function () {
            $menulist = SystemMenusAdmin::all(function ($m) {
                $m->where(['is_header' => 1])->order('sort DESC,id ASC');
            })->toArray();
            $list = sort_list_tier($menulist, '顶级', 'pid', 'id');
            $menus = [['value' => '', 'label' => '请选择']];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['header'], 'label' => $menu['menu_name']];
            }
            return $menus;
        })->filterable(1);
        return $this->makePostForm('添加权限', $field, Url::buildUrl('/setting/save')->suffix(false));
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = UtilService::getMore([
            ['menu_name', ''],
            ['controller', ''],
            ['module', 'admin'],
            ['action', ''],
            ['icon', ''],
            ['params', ''],
            ['menu_path', ''],
            ['api_url', ''],
            ['methods', ''],
            ['unique_auth', ''],
            ['header', ''],
            ['is_header', 0],
            ['pid', 0],
            ['sort', 0],
            ['auth_type', 0],
            ['access', 1],
            ['is_show', 0],
            ['is_show_path', 0],
        ], $request);

        if (!$data['menu_name'])
            return $this->fail('请填写按钮名称');
        if (SystemMenusAdmin::create($data))
            return $this->success('添加成功');
        else
            return $this->fail('添加失败');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (!$id || !($menu = SystemMenusAdmin::get($id)))
            return $this->fail('数据不存在');
        $menu = $menu->getData();
        $menu['pid'] = (string)$menu['pid'];
        $menu['auth_type'] = (string)$menu['auth_type'];
        $menu['is_header'] = (string)$menu['is_header'];
        $menu['is_show'] = (string)$menu['is_show'];
        $menu['is_show_path'] = (string)$menu['is_show_path'];
        return $this->success($menu);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id || !($menu = SystemMenusAdmin::get($id)))
            return $this->fail('数据不存在');
        $field[] = Form::input('menu_name', '按钮名称', $menu['menu_name']);
        $field[] = Form::select('pid', '父级id', (string)$menu->getData('pid'))->setOptions(function () use ($id) {
            $list = (Util::sortListTier(SystemMenusAdmin::all(function ($m) {
                $m->order('sort DESC,id ASC')->where('is_del', 0);
            })->toArray(), '顶级', 'pid', 'menu_name'));
            $menus = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $menu) {
                $menus[] = ['value' => (int)$menu['id'], 'label' => $menu['html'] . $menu['menu_name']];
            }
            return $menus;
        })->filterable(1);
        $field[] = Form::select('module', '模块名', $menu['module'])->options([['label' => '总后台', 'value' => 'admin']]);
        $field[] = Form::input('controller', '控制器名', $menu['controller']);
        if (!empty($menu['controller'])) {
            $controller = preg_replace_callback('/([.]+([a-z]{1}))/i', function ($matches) {
                return '\\' . strtoupper($matches[2]);
            }, $menu['controller']);
            if (class_exists('\app\adminapi\v1\controller\\' . $controller)) {
                $list = get_this_class_methods('\app\adminapi\v1\controller\\' . $controller);

                $field[] = Form::select('action', '方法名', (string)$menu->getData('action'))->setOptions(function () use ($list) {
                    $menus = [['value' => 0, 'label' => '默认函数']];
                    foreach ($list as $menu) {
                        $menus[] = ['value' => $menu, 'label' => $menu];
                    }
                    return $menus;
                })->filterable(1);
            } else {
                $field[] = Form::input('action', '方法名', $menu['action']);
            }
        } else {
            $field[] = Form::input('action', '方法名');
        }
        $field[] = Form::input('params', '参数', SystemMenusAdmin::paramStr($menu['params']))->placeholder('举例:a/123/b/234');
        $field[] = Form::input('menu_path', '路由名称', $menu['menu_path'])->placeholder('请输入前台跳转路由地址')->required('请填写前台路由地址');
        $field[] = Form::input('unique_auth', '权限标识', $menu['unique_auth'])->placeholder('不填写则后台自动生成');
        $field[] = Form::frameInputOne('icon', '图标', Url::buildUrl('admin/widget.widgets/icon', array('fodder' => 'icon')), $menu['icon'])->icon('ionic')->height('500px');
        $field[] = Form::number('sort', '排序', $menu['sort']);
        $field[] = Form::radio('auth_type', '是否菜单', $menu['is_show'])->options([['value' => 0, 'label' => '隐藏'], ['value' => 1, 'label' => '显示(菜单只显示三级)']]);
        $field[] = Form::radio('is_header', '是否顶部菜单', $menu['is_header'])->options([['value' => 0, 'label' => '否'], ['value' => 1, 'label' => '是']]);
        $field[] = Form::radio('is_show_path', '是否为前端隐藏菜单', $menu['is_show_path'])->options([['value' => 0, 'label' => '否'], ['value' => 1, 'label' => '是']]);
        $field[] = Form::select('header', '顶部菜单', (string)$menu->getData('header'))->setOptions(function () use ($id) {
            $list = (Util::sortListTier(SystemMenusAdmin::all(function ($m) {
                $m->where(['is_header' => 1]);
            })->toArray(), '顶级', 'header', 'menu_name'));
            $list = SystemMenusAdmin::where(['is_header' => 1])->select()->toArray();
            dd($list);
            $menus = [['value' => 0, 'label' => '请选择']];
            foreach ($list as $menu) {
                $menus[] = ['value' => (int)$menu['header'], 'label' => $menu['html'] . $menu['menu_name']];
            }
            return $menus;
        })->filterable(1);
        return $this->makePostForm('修改权限', $field, Url::buildUrl('/setting/update/' . $id)->suffix(false));
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
        if (!$id || !($menu = SystemMenusAdmin::get($id)))
            return $this->fail('数据不存在');
        $data = UtilService::postMore([
            'menu_name',
            'controller',
            ['module', 'admin'],
            'action',
            'params',
            ['icon', ''],
            ['menu_path', ''],
            ['api_url', ''],
            ['methods', ''],
            ['unique_auth', ''],
            ['sort', 0],
            ['pid', 0],
            ['is_header', 0],
            ['header', ''],
            ['auth_type', 0],
            ['access', 1],
            ['is_show', 0],
            ['is_show_path', 0],
        ], $request);
        if (!$data['menu_name'])
            return $this->fail('请输入按钮名称');
        if (SystemMenusAdmin::edit($data, $id))
            return $this->success('修改成功');
        else
            return $this->fail('修改失败');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id)
            return $this->fail('参数错误，请重新打开');
        $res = SystemMenusAdmin::delMenu($id);
        if (!$res)
            return $this->fail(SystemMenusAdmin::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }

    /**
     * 显示和隐藏
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        if (!$id)
            return $this->fail('参数错误，请重新打开');
        [$show] = UtilService::postMore([['is_show', 0]], $this->request, true);
        if (SystemMenusAdmin::edit(['is_show' => $show], $id))
            return $this->success('修改成功');
        else
            return $this->fail('修改失败');
    }
}
