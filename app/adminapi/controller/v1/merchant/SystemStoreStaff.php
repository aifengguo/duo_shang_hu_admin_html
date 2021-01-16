<?php

namespace app\adminapi\controller\v1\merchant;

use think\facade\Route as Url;
use app\adminapi\controller\AuthController;
use app\models\system\{
    SystemStore, SystemStoreStaff as StoreStaffModel
};
use crmeb\services\{
    FormBuilder as Form, UtilService as Util
};

class SystemStoreStaff extends AuthController
{
    /**
     * 获取店员列表
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            [['page', 'd'], 1],
            [['limit', 'd'], 15],
            [['store_id', 'd'], 0],
        ]);
        return $this->success(StoreStaffModel::getList($where));
    }

    /**
     * 门店列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function store_list()
    {
        return $this->success(SystemStore::verificWhere()->field(['id', 'name'])->select()->toArray());
    }

    /**
     * 店员新增表单
     * @return mixed
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function create()
    {
        $field = [
            Form::frameImageOne('image', '商城用户', Url::buildUrl('admin/system.User/list', array('fodder' => 'image')))->icon('ios-add')->width('50%')->height('500px')->setProps(['srcKey'=>'image']),
            Form::hidden('uid', 0),
            Form::hidden('avatar', ''),
            Form::select('store_id', '所属提货点')->setOptions(function () {
                $list = SystemStore::dropList();
                $menus = [];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('staff_name', '核销员名称')->col(Form::col(24)),
            Form::input('phone', '手机号码')->col(Form::col(24)),
            Form::radio('verify_status', '核销开关', 1)->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]),
            Form::radio('status', '状态', 1)->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']])
        ];
        return $this->makePostForm('添加核销员', $field, Url::buildUrl('/merchant/store_staff/save/0')->suffix(false));
    }

    /**
     * 店员修改表单
     * @return mixed
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit()
    {
        list($id) = Util::getMore([
            [['id', 'd'], 0],
        ], $this->request, true);
        $info = StoreStaffModel::get($id);
        if (!$info) return $this->fail('参数错误');
        $field = [
            Form::frameImageOne('image', '商城用户', Url::buildUrl('admin/system.User/list'), $info['avatar'])->icon('ios-add')->width('50%')->height('500px')->allowRemove(false),
            Form::hidden('uid', $info['uid']),
            Form::hidden('avatar', $info['avatar']),
            Form::select('store_id', '所属提货点', (string)$info->getData('store_id'))->setOptions(function () {
                $list = SystemStore::dropList();
                $menus = [];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('staff_name', '核销员名称', $info['staff_name'])->col(Form::col(24)),
            Form::input('phone', '手机号码', $info['phone'])->col(Form::col(24)),
            Form::radio('verify_status', '核销开关', $info['verify_status'])->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]),
            Form::radio('status', '状态', $info['status'])->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']])
        ];
        return $this->makePostForm('修改核销员', $field, Url::buildUrl('/merchant/store_staff/save/' . $info['id'])->suffix(false));
    }

    /**
     * 保存店员信息
     */
    public function save($id = 0)
    {
        $data = Util::postMore([
            ['image',''],
            ['uid', 0],
            ['avatar', ''],
            ['store_id', ''],
            ['staff_name', ''],
            ['phone', ''],
            ['verify_status', 1],
            ['status', 1],
        ]);
        if (!$id) {
            if($data['image']=='') return $this->fail('请选择用户');
            if (StoreStaffModel::where('uid', $data['uid'])->count()) return $this->fail('添加的核销员用户已存在!');
            $data['uid'] = $data['image']['uid'];
            $data['avatar'] = $data['image']['image'];
        }
        if ($data['uid'] == 0) return $this->fail('请选择用户');
        if ($data['store_id'] == '') return $this->fail('请选择所属提货点');
        unset($data['image']);
        if ($id) {
            $res = StoreStaffModel::edit($data, $id);
            if ($res) {
                return $this->success('编辑成功');
            } else {
                return $this->fail('编辑失败');
            }
        } else {
            $data['add_time'] = time();
            $res = StoreStaffModel::create($data);
            if ($res) {
                return $this->success('核销员添加成功');
            } else {
                return $this->fail('核销员添加失败，请稍后再试');
            }
        }
    }

    /**
     * 设置单个店员是否开启
     * @param string $is_show
     * @param string $id
     * @return mixed
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && $this->fail('缺少参数');
        $res = StoreStaffModel::where(['id' => $id])->update(['status' => (int)$is_show]);
        if ($res) {
            return $this->success($is_show == 1 ? '开启成功' : '关闭成功');
        } else {
            return $this->fail($is_show == 1 ? '开启失败' : '关闭失败');
        }
    }

    /**
     * 删除店员
     * @param $id
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('数据不存在');
        if (!StoreStaffModel::be(['id' => $id])) return $this->failed('数据不存在');
        if (!StoreStaffModel::del($id))
            return $this->fail(StoreStaffModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }
}