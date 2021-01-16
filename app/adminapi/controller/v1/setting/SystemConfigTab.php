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

/**
 * 配置分类
 * Class SystemConfigTab
 * @package app\adminapi\controller\v1\setting
 */
class SystemConfigTab extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 15],
            ['status', ''],
            ['title', '']
        ], $this->request);
        $list = ConfigTabModel::getSystemConfigTabPage($where);
        return $this->success($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $form[] = Form::select('pid', '父级分类', 0)->setOptions(function () {
            $menuList = ConfigTabModel::field(['id', 'pid', 'title'])->select()->toArray();//var_dump($menuList);
            $list = sort_list_tier($menuList, '顶级', 'pid', 'id');//var_dump($list);
            $menus = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['title']];
            }
            return $menus;
        })->filterable(1);
        $form[] = Form::input('title', '分类昵称');
        $form[] = Form::input('eng_title', '分类字段英文');
        $form[] = Form::frameInputOne('icon', '图标', Url::buildUrl('admin/widget.widgets/icon', array('fodder' => 'icon')))->icon('ios-ionic')->height('435px');
        $form[] = Form::radio('type', '类型', 0)->options(self::getConfigType());
        $form[] = Form::radio('status', '状态', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        $form[] = Form::number('sort', '排序', 0);
        return $this->makePostForm('添加配置分类', $form, Url::buildUrl('/setting/config_class'), 'POST');
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
            'eng_title',
            'status',
            'title',
            'icon',
            ['type', 0],
            ['sort', 0],
            ['pid', 0],
        ]);
        if (!$data['title']) return $this->fail('请输入按钮名称');
        ConfigTabModel::create($data);
        return $this->success('添加配置分类成功!');
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
        $menu = ConfigTabModel::get($id)->getData();
        if (!$menu) return $this->fail('数据不存在!');
        $form[] = Form::select('pid', '父级分类', (string)$menu['pid'])->setOptions(function () {
            $menuList = ConfigTabModel::field(['id', 'pid', 'title'])->select()->toArray();//var_dump($menuList);
            $list = sort_list_tier($menuList, '顶级', 'pid', 'id');//var_dump($list);
            $menus = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['title']];
            }
            return $menus;
        })->filterable(1);
        $form[] = Form::input('title', '分类昵称', $menu['title']);
        $form[] = Form::input('eng_title', '分类字段英文', $menu['eng_title']);
        $form[] = Form::frameInputOne('icon', '图标', Url::buildUrl('admin/widget.widgets/icon', array('fodder' => 'icon')), $menu['icon'])->icon('ios-ionic')->height('435px');
        $form[] = Form::radio('type', '类型', $menu['type'])->options(self::getConfigType());
        $form[] = Form::radio('status', '状态', $menu['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        $form[] = Form::number('sort', '排序', $menu['sort']);
        return $this->makePostForm('编辑配置分类', $form, Url::buildUrl('/setting/config_class/' . $id), 'PUT');
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
        $data = Util::postMore([
            'title',
            'status',
            'eng_title',
            'icon',
            ['type', 0],
            ['sort', 0],
            ['pid', 0],
        ]);
        if (!$data['title']) return $this->fail('请输入分类昵称');
        if (!$data['eng_title']) return $this->fail('请输入分类字段');
        if (!ConfigTabModel::get($id)) return $this->fail('编辑的记录不存在!');
        ConfigTabModel::edit($data, $id);
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
        if (ConfigModel::where('config_tab_id', $id)->count()) return $this->fail('存在下级配置，无法删除！');
        if (!ConfigTabModel::del($id))
            return $this->fail(ConfigTabModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }

    /** 定义配置分类,需要添加分类可以手动添加
     * @return array
     */
    public function getConfigType()
    {
        return [
            ['value' => 0, 'label' => '系统']
            , ['value' => 1, 'label' => '应用']
            , ['value' => 2, 'label' => '支付']
            , ['value' => 3, 'label' => '其它']
        ];
//        return [
//            ['value'=>0,'label'=>'系统']
//            ,['value'=>1,'label'=>'公众号']
//            ,['value'=>2,'label'=>'小程序']
//            ,['value'=>3,'label'=>'其它']
//        ];
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
        ConfigTabModel::where(['id' => $id])->update(['status' => $status]);

        return $this->success($status == 0 ? '隐藏成功' : '显示成功');
    }
}
