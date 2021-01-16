<?php

namespace app\adminapi\controller\v1\user;

use app\adminapi\controller\AuthController;
use app\models\user\{
    User as UserModel,
    UserLabelRelation,
    UserLabel,
    UserLevel,
    UserBill as UserBillAdmin,
    UserTaskFinish,
    UserGroup
};
use app\models\system\{
    SystemUserLevel, SystemUserTask
};
use app\models\store\{
    StoreOrder, StoreVisit, StoreCouponUser
};
use app\models\wechat\WechatMessage;
use crmeb\basic\BaseModel;
use crmeb\repositories\UserRepository;
use crmeb\services\{
    FormBuilder as Form, UtilService as Util
};
use think\Request;
use think\facade\Route as Url;

class User extends AuthController
{
    /**
     * 显示资源列表头部
     *
     * @return \think\Response
     */
    public function type_header()
    {
        //全部会员
        $all = UserModel::count();
        //小程序会员
        $routine = UserModel::alias('u')->join('WechatUser w', 'u.uid=w.uid')->where('w.routine_openid', '<>', '')->count();
        //公众号会员
        $wechat = UserModel::alias('u')->join('WechatUser w', 'u.uid=w.uid')->where('w.openid', '<>', '')->count();
        //H5会员
        $h5 = UserModel::alias('u')->join('WechatUser w', 'u.uid=w.uid')->where(['w.routine_openid' => '', 'w.openid' => ''])->where('u.user_type', 'h5')->count();
        $list = [
            ['user_type' => '', 'name' => '全部会员', 'count' => $all],
            ['user_type' => 'routine', 'name' => '小程序会员', 'count' => $routine],
            ['user_type' => 'wechat', 'name' => '公众号会员', 'count' => $wechat],
            ['user_type' => 'h5', 'name' => 'H5会员', 'count' => $h5],
        ];
        return $this->success(compact('list'));
    }


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['status', ''],
            ['pay_count', ''],
            ['is_promoter', ''],
            ['order', ''],
            ['data', ''],
            ['user_type', ''],
            ['country', ''],
            ['province', ''],
            ['city', ''],
            ['user_time_type', ''],
            ['user_time', ''],
            ['sex', ''],
            [['level', 0], 0],
            [['group_id', 'd'], 0],
            [['label_id', 'd'], 0]
        ]);
        return $this->success(UserModel::getUserList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        $info = [
            'uid' => $id,
            'userinfo' => UserModel::getUserDetailed($id),
            'is_layui' => true,
            'headerList' => UserModel::getHeaderList($id),
            'count' => UserModel::getCountInfo($id),
            'ps_info' => UserModel::where('uid', $id)->find()
        ];
        return $this->success($info);
    }

    /*
     * 赠送会员等级
     * @param int $uid
     * @return mixed
     * */
    public function give_level($id)
    {
        if (!$id) return $this->fail('缺少参数');
        $level = UserLevel::getUserLevel($id);
        //获取当前会员等级
        if ($level === false)
            $grade = 0;
        else
            $grade = UserLevel::getUserLevelInfo($level, 'grade');
        //查询高于当前会员的所有会员等级
        $systemLevelList = SystemUserLevel::where('grade', '>', $grade)->where(['is_show' => 1, 'is_del' => 0])->field(['name', 'id'])->select();
        $field[] = Form::select('level_id', '会员等级')->setOptions(function () use ($systemLevelList) {
            $menus = [];
            foreach ($systemLevelList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1);
        return $this->makePostForm('赠送会员', $field, Url::buildUrl('/user/save_give_level/' . $id), 'PUT');
    }

    /*
     * 执行赠送会员等级
     * @param int $uid
     * @return mixed
     * */
    public function save_give_level($id)
    {
        if (!$id) return $this->fail('缺少参数');
        list($level_id) = Util::postMore([
            ['level_id', 0],
        ], $this->request, true);
        //查询当前选择的会员等级
        $systemLevel = SystemUserLevel::where(['is_show' => 1, 'is_del' => 0, 'id' => $level_id])->find();
        if (!$systemLevel) return $this->fail('您选择赠送的会员等级不存在！');
        //检查是否拥有此会员等级
        $level = UserLevel::where(['uid' => $id, 'level_id' => $level_id, 'is_del' => 0])->field('valid_time,is_forever')->find();
        if ($level) {
            if ($level['is_forever']) {
                return $this->fail('此用户已有该会员等级，无法再次赠送');
            } else {
                if (time() < $level['valid_time'])
                    return $this->fail('此用户已有该会员等级，无法再次赠送');
            }
        }

        //设置会员过期时间
        $add_valid_time = (int)$systemLevel->valid_date * 86400;
        UserModel::commitTrans();
        try {
            //保存会员信息
            $res = UserLevel::setUserLevel($id, $level_id);
            //提取等级任务并记录完成情况
            $levelIds = [$level_id];
            $lowGradeLevelIds = SystemUserLevel::where('grade', '<', $systemLevel->grade)->where(['is_show' => 1, 'is_del' => 0])->column('id', 'id');
            if (count($lowGradeLevelIds)) $levelIds = array_merge($levelIds, $lowGradeLevelIds);
            $taskIds = SystemUserTask::where('level_id', 'in', $levelIds)->column('id', 'id');
            $inserValue = [];
            foreach ($taskIds as $id) {
                $inserValue[] = ['uid' => $id, 'task_id' => $id, 'status' => 1, 'add_time' => time()];
            }
            $res = $res && UserTaskFinish::insertAll($inserValue);
            if ($res) {
                UserModel::commitTrans();
                return $this->success('赠送成功');
            } else {
                UserModel::rollbackTrans();
                return $this->success('赠送失败');
            }
        } catch (\Exception $e) {
            UserModel::rollbackTrans();
            return $this->fail('赠送失败');
        }
    }

    /**
     * 清除会员等级
     * @param int $uid
     * @return json
     */
    public function del_level($id)
    {
        if (!$id) return $this->fail('缺少参数');
        if (UserLevel::cleanUpLevel($id))
            return $this->success('清除成功');
        else
            return $this->fail('清除失败');
    }

    /**
     * 设置会员分组
     * @param $id
     * @return mixed
     * @throws \FormBuilder\exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function set_group()
    {
        list($uids) = Util::postMore([
            ['uids', []],
        ], $this->request, true);
        $uid = implode(',', $uids);
        if (!$uid) return $this->fail('缺少参数');
        $userGroup = UserGroup::select();
        if (count($uids) == 1) {
            $user = UserModel::where('uid', $uids[0])->find();
            $field[] = Form::select('group_id', '会员分组', (string)$user->getData('group_id'))->setOptions(function () use ($userGroup) {
                $menus = [];
                foreach ($userGroup as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['group_name']];
                }
                return $menus;
            })->filterable(1);
        } else {
            $field[] = Form::select('group_id', '会员分组')->setOptions(function () use ($userGroup) {
                $menus = [];
                foreach ($userGroup as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['group_name']];
                }
                return $menus;
            })->filterable(1);
        }
        $field[] = Form::hidden('uids', $uid);
        return $this->makePostForm('设置会员分组', $field, Url::buildUrl('/user/save_set_group'), 'PUT');
    }

    /**
     * 保存会员分组
     * @param $id
     * @return mixed
     */
    public function save_set_group()
    {
        list($group_id, $uids) = Util::postMore([
            ['group_id', 0],
            ['uids', ''],
        ], $this->request, true);
        if (!$uids) return $this->fail('缺少参数');
        $uids = explode(',', $uids);
        $res = UserModel::whereIn('uid', $uids)->update(['group_id' => $group_id]);
        if ($res) {
            return $this->success('设置分组成功');
        } else {
            return $this->fail('设置分组失败或无改动');
        }
    }

    /**
     * 设置用户标签
     * @return mixed
     * @throws \FormBuilder\exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function set_label()
    {
        list($uids) = Util::postMore([
            ['uids', []],
        ], $this->request, true);
        $uid = implode(',', $uids);
        if (!$uid) return $this->fail('缺少参数');
        $userGroup = UserLabel::select();
        if (count($uids) == 1) {
            $lids = UserLabelRelation::where('uid',$uids[0])->column('label_id');
            $field[] = Form::select('label_id', '会员标签', $lids)->setOptions(function () use ($userGroup) {
                $menus = [];
                foreach ($userGroup as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['label_name']];
                }
                return $menus;
            })->filterable(1)->multiple(1);
        } else {
            $field[] = Form::select('label_id', '会员标签')->setOptions(function () use ($userGroup) {
                $menus = [];
                foreach ($userGroup as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['label_name']];
                }
                return $menus;
            })->filterable(1)->multiple(1);
        }
        $field[] = Form::hidden('uids', $uid);
        return $this->makePostForm('设置会员标签', $field, Url::buildUrl('/user/save_set_label'), 'PUT');
    }

    /**
     * 保存用户标签
     * @return mixed
     */
    public function save_set_label()
    {
        list($lables, $uids) = Util::postMore([
            ['label_id', ''],
            ['uids', ''],
        ], $this->request, true);
        if (!$uids) return $this->fail('缺少参数');
        $uids = explode(',', $uids);
        $res = UserLabelRelation::saveUserLabel($uids, $lables);
        if ($res) {
            return $this->success('设置标签成功');
        } else {
            return $this->fail('设置标签失败');
        }
    }

    /**
     * 编辑其他
     * @param $id
     * @return mixed
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit_other($id)
    {
        if (!$id) return $this->fail('数据不存在');
        $user = UserModel::get($id);
        if (!$user) return $this->fail('数据不存在!');
        $f = array();
        $f[] = Form::radio('money_status', '修改余额', 1)->options([['value' => 1, 'label' => '增加'], ['value' => 2, 'label' => '减少']]);
        $f[] = Form::number('money', '余额', 0)->min(0);
        $f[] = Form::radio('integration_status', '修改积分', 1)->options([['value' => 1, 'label' => '增加'], ['value' => 2, 'label' => '减少']]);
        $f[] = Form::number('integration', '积分', 0)->min(0);
        return $this->makePostForm('修改其他', $f, Url::buildUrl('/user/update_other/' . $id), 'PUT');
    }

    /**
     * 执行编辑其他
     * @param int $id
     * @return mixed
     */
    public function update_other($id)
    {
        $data = Util::postMore([
            ['money_status', 0],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
        ], $this->request);
        if (!$id) return $this->fail('数据不存在');
        $user = UserModel::get($id);
        if (!$user) return $this->fail('数据不存在!');
        BaseModel::beginTrans();
        $res1 = false;
        $res2 = false;
        $edit = array();
        if ($data['money_status'] && $data['money']) {//余额增加或者减少
            if ($data['money_status'] == 1) {//增加
                $edit['now_money'] = bcadd($user['now_money'], $data['money'], 2);
                $res1 = UserBillAdmin::income('系统增加余额', $user['uid'], 'now_money', 'system_add', $data['money'], $this->adminId, $edit['now_money'], '系统增加了' . floatval($data['money']) . '余额');
                try {
                    UserRepository::adminAddMoney($user, $data['money']);
                } catch (\Exception $e) {
                    BaseModel::rollbackTrans();
                    return $this->fail($e->getMessage());
                }
            } else if ($data['money_status'] == 2) {//减少
                $edit['now_money'] = bcsub($user['now_money'], $data['money'], 2);
                $res1 = UserBillAdmin::expend('系统减少余额', $user['uid'], 'now_money', 'system_sub', $data['money'], $this->adminId, $edit['now_money'], '系统扣除了' . floatval($data['money']) . '余额');
                try {
                    UserRepository::adminSubMoney($user, $data['money']);
                } catch (\Exception $e) {
                    BaseModel::rollbackTrans();
                    return $this->fail($e->getMessage());
                }
            }
        } else {
            $res1 = true;
        }
        if ($data['integration_status'] && $data['integration']) {//积分增加或者减少
            if ($data['integration_status'] == 1) {//增加
                $edit['integral'] = bcadd($user['integral'], $data['integration'], 2);
                $res2 = UserBillAdmin::income('系统增加积分', $user['uid'], 'integral', 'system_add', $data['integration'], $this->adminId, $edit['integral'], '系统增加了' . floatval($data['integration']) . '积分');
                try {
                    UserRepository::adminAddIntegral($user, $data['integration']);
                } catch (\Exception $e) {
                    BaseModel::rollbackTrans();
                    return $this->fail($e->getMessage());
                }
            } else if ($data['integration_status'] == 2) {//减少
                $edit['integral'] = bcsub($user['integral'], $data['integration'], 2);
                $res2 = UserBillAdmin::expend('系统减少积分', $user['uid'], 'integral', 'system_sub', $data['integration'], $this->adminId, $edit['integral'], '系统扣除了' . floatval($data['integration']) . '积分');
                try {
                    UserRepository::adminSubIntegral($user, $data['integration']);
                } catch (\Exception $e) {
                    BaseModel::rollbackTrans();
                    return $this->fail($e->getMessage());
                }
            }
        } else {
            $res2 = true;
        }
        if ($edit) $res3 = UserModel::edit($edit, $id);
        else $res3 = true;
        if ($res1 && $res2 && $res3) $res = true;
        else $res = false;
        BaseModel::checkTrans($res);
        if ($res) return $this->success('修改成功!');
        else return $this->fail('修改失败');
    }

    /**
     * 修改user表状态
     *
     * @return array
     */
    public function set_status($status, $id)
    {
        if ($status == '' || $id == 0) return $this->fail('参数错误');
        UserModel::where(['uid' => $id])->update(['status' => $status]);

        return $this->success($status == 0 ? '禁用成功' : '解禁成功');
    }

    /**
     * 编辑会员信息
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($id)
    {
        if (!$id) return $this->fail('数据不存在');
        $user = UserModel::get($id);
        if (!$user) return $this->fail('数据不存在!');
        $f = array();
        $f[] = Form::input('uid', '用户编号', $user->getData('uid'))->disabled(1);
        $f[] = Form::input('real_name', '真实姓名', $user->getData('real_name'));
        $f[] = Form::input('phone', '手机号码', $user->getData('phone'));
        $f[] = Form::date('birthday', '生日', $user->getData('birthday') ? date('Y-m-d', $user->getData('birthday')) : '');
        $f[] = Form::input('card_id', '身份证号', $user->getData('card_id'));
        $f[] = Form::input('addres', '用户地址', $user->getData('addres'));
        $f[] = Form::textarea('mark', '用户备注', $user->getData('mark'));
        $level = UserLevel::getUserLevel($id);
        //获取当前会员等级
        if ($level === false)
            $grade = 0;
        else
            $grade = UserLevel::getUserLevelInfo($level, 'grade');
        //查询高于当前会员的所有会员等级
        $systemLevelList = SystemUserLevel::where('grade', '>', $grade)->where(['is_show' => 1, 'is_del' => 0])->field(['name', 'id'])->select();
        $f[] = Form::select('level', '会员等级', (string)$user->getData('level'))->setOptions(function () use ($systemLevelList) {
            $menus = [];
            foreach ($systemLevelList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1);
        $systemGroupList = UserGroup::select();
        $f[] = Form::select('group_id', '会员分组', (string)$user->getData('group_id'))->setOptions(function () use ($systemGroupList) {
            $menus = [];
            foreach ($systemGroupList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['group_name']];
            }
            return $menus;
        })->filterable(1);
        $systemLabelList = UserLabel::select();
        $labels = UserLabelRelation::where('uid', $user->getData('uid'))->column('label_id');
        $f[] = Form::select('label_id', '会员标签', $labels)->setOptions(function () use ($systemLabelList) {
            $menus = [];
            foreach ($systemLabelList as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['label_name']];
            }
            return $menus;
        })->filterable(1)->multiple(1);
        $f[] = Form::radio('is_promoter', '推广员', $user->getData('is_promoter'))->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]);
        $f[] = Form::radio('status', '状态', $user->getData('status'))->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '锁定']]);
        return $this->makePostForm('编辑', $f, Url::buildUrl('/user/user/' . $id), 'PUT');
    }

    public function update($id)
    {
        $data = Util::postMore([
            ['money_status', 0],
            ['is_promoter', 1],
            ['real_name', ''],
            ['card_id', ''],
            ['birthday', ''],
            ['mark', ''],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
            ['status', 0],
            ['level', 0],
            ['phone', 0],
            ['addres', ''],
            ['label_id', ''],
            ['group_id', 0]
        ]);
        if ($data['phone']) {
            if (!preg_match("/^1[3456789]\d{9}$/", $data['phone'])) return $this->fail('手机号码格式不正确');
        }
        if ($data['card_id']) {
            if (!preg_match('/^[1-9]{1}\d{5}[1-9]{2}\d{9}[Xx0-9]{1}$/', $data['card_id'])) return $this->fail('请输入正确的身份证');
        }

        if (!$id) return $this->fail('数据不存在');
        $user = UserModel::get($id);
        if (!$user) return $this->fail('数据不存在!');
        BaseModel::beginTrans();
        $res1 = false;
        $res2 = false;
        $edit = array();
        if ($data['money_status'] && $data['money']) {//余额增加或者减少
            if ($data['money_status'] == 1) {//增加
                $edit['now_money'] = bcadd($user['now_money'], $data['money'], 2);
                $res1 = UserBillAdmin::income('系统增加余额', $user['uid'], 'now_money', 'system_add', $data['money'], $this->adminId, $edit['now_money'], '系统增加了' . floatval($data['money']) . '余额');
                try {
                    UserRepository::adminAddMoney($user, $data['money']);
                } catch (\Exception $e) {
                    BaseModel::rollbackTrans();
                    return $this->fail($e->getMessage());
                }
            } else if ($data['money_status'] == 2) {//减少
                $edit['now_money'] = bcsub($user['now_money'], $data['money'], 2);
                $res1 = UserBillAdmin::expend('系统减少余额', $user['uid'], 'now_money', 'system_sub', $data['money'], $this->adminId, $edit['now_money'], '系统扣除了' . floatval($data['money']) . '余额');
                try {
                    UserRepository::adminSubMoney($user, $data['money']);
                } catch (\Exception $e) {
                    BaseModel::rollbackTrans();
                    return $this->fail($e->getMessage());
                }
            }
        } else {
            $res1 = true;
        }
        if ($data['integration_status'] && $data['integration']) {//积分增加或者减少
            if ($data['integration_status'] == 1) {//增加
                $edit['integral'] = bcadd($user['integral'], $data['integration'], 2);
                $res2 = UserBillAdmin::income('系统增加积分', $user['uid'], 'integral', 'system_add', $data['integration'], $this->adminId, $edit['integral'], '系统增加了' . floatval($data['integration']) . '积分');
                try {
                    UserRepository::adminAddIntegral($user, $data['integration']);
                } catch (\Exception $e) {
                    BaseModel::rollbackTrans();
                    return $this->fail($e->getMessage());
                }
            } else if ($data['integration_status'] == 2) {//减少
                $edit['integral'] = bcsub($user['integral'], $data['integration'], 2);
                $res2 = UserBillAdmin::expend('系统减少积分', $user['uid'], 'integral', 'system_sub', $data['integration'], $this->adminId, $edit['integral'], '系统扣除了' . floatval($data['integration']) . '积分');
                try {
                    UserRepository::adminSubIntegral($user, $data['integration']);
                } catch (\Exception $e) {
                    BaseModel::rollbackTrans();
                    return $this->fail($e->getMessage());
                }
            }
        } else {
            $res2 = true;
        }
        UserLabelRelation::saveUserLabel([$id], $data['label_id']);
        $edit['status'] = $data['status'];
        $edit['real_name'] = $data['real_name'];
        $edit['card_id'] = $data['card_id'];
        $edit['birthday'] = strtotime($data['birthday']);
        $edit['mark'] = $data['mark'];
        $edit['is_promoter'] = $data['is_promoter'];
        $edit['level'] = $data['level'];
        $edit['phone'] = $data['phone'];
        $edit['addres'] = $data['addres'];
        $edit['group_id'] = $data['group_id'];
        if ($edit) $res3 = UserModel::edit($edit, $id);
        else $res3 = true;
        if ($res1 && $res2 && $res3) $res = true;
        else $res = false;
        BaseModel::checkTrans($res);
        if ($res) return $this->success('修改成功!');
        else return $this->fail('修改失败');
    }

    /**
     * TODO:需统一到数据统计模块 start
     * 用户图表
     * @return mixed
     */
    public function user_analysis()
    {
        $where = Util::getMore([
            ['nickname', ''],
            ['status', ''],
            ['is_promoter', ''],
            ['date', ''],
            ['user_type', ''],
            ['export', 0]
        ], $this->request);
        $user_count = UserModel::consume($where, '', true);
        //头部信息
        $header = [
            [
                'name' => '新增用户',
                'class' => 'fa-line-chart',
                'value' => $user_count,
                'color' => 'red'
            ],
            [
                'name' => '用户留存',
                'class' => 'fa-area-chart',
                'value' => $this->gethreaderValue(UserModel::consume($where, '', true), $where) . '%',
                'color' => 'lazur'
            ],
            [
                'name' => '新增用户总消费',
                'class' => 'fa-bar-chart',
                'value' => '￥' . UserModel::consume($where),
                'color' => 'navy'
            ],
            [
                'name' => '用户活跃度',
                'class' => 'fa-pie-chart',
                'value' => $this->gethreaderValue(UserModel::consume($where, '', true)) . '%',
                'color' => 'yellow'
            ],
        ];
        $name = ['新增用户', '用户消费'];
        $dates = $this->get_user_index($where, $name);
        $user_index = ['name' => json_encode($name), 'date' => json_encode($dates['time']), 'series' => json_encode($dates['series'])];
        //用户浏览分析
        $view = StoreVisit::getVisit($where['date'], ['', 'warning', 'info', 'danger']);
        $view_v1 = WechatMessage::getViweList($where['date'], ['', 'warning', 'info', 'danger']);
        $view = array_merge($view, $view_v1);
        $view_v2 = [];
        foreach ($view as $val) {
            $view_v2['color'][] = '#' . rand(100000, 339899);
            $view_v2['name'][] = $val['name'];
            $view_v2['value'][] = $val['value'];
        }
        $view = $view_v2;
        //消费会员排行用户分析
        $user_null = UserModel::getUserSpend($where['date']);
        //消费数据
        $now_number = UserModel::getUserSpend($where['date'], true);
        list($paren_number, $title) = UserModel::getPostNumber($where['date']);
        if ($paren_number == 0) {
            $rightTitle = [
                'number' => $now_number > 0 ? $now_number : 0,
                'icon' => 'fa-level-up',
                'title' => $title
            ];
        } else {
            $number = (float)bcsub($now_number, $paren_number, 4);
            if ($now_number == 0) {
                $icon = 'fa-level-down';
            } else {
                $icon = $now_number > $paren_number ? 'fa-level-up' : 'fa-level-down';
            }
            $rightTitle = ['number' => $number, 'icon' => $icon, 'title' => $title];
        }
        unset($title, $paren_number, $now_number);
        list($paren_user_count, $title) = UserModel::getPostNumber($where['date'], true, 'add_time', '');
        if ($paren_user_count == 0) {
            $count = $user_count == 0 ? 0 : $user_count;
            $icon = $user_count == 0 ? 'fa-level-down' : 'fa-level-up';
        } else {
            $count = (float)bcsub($user_count, $paren_user_count, 4);
            $icon = $user_count < $paren_user_count ? 'fa-level-down' : 'fa-level-up';
        }
        $leftTitle = [
            'count' => $count,
            'icon' => $icon,
            'title' => $title
        ];
        unset($count, $icon, $title);
        $consume = [
            'title' => '消费金额为￥' . UserModel::consume($where),
            'series' => UserModel::consume($where, 'xiaofei'),
            'rightTitle' => $rightTitle,
            'leftTitle' => $leftTitle,
        ];
        $form = UserModel::consume($where, 'form');
        $grouping = UserModel::consume($where, 'grouping');
        return $this->success(compact('header', 'user_index', 'view', 'user_null', 'consume', 'form', 'grouping', 'where'));
    }

    public function gethreaderValue($chart, $where = [])
    {
        if ($where) {
            switch ($where['date']) {
                case null:
                case 'today':
                case 'week':
                case 'year':
                    if ($where['date'] == null) {
                        $where['date'] = 'month';
                    }
                    $sum_user = UserModel::whereTime('add_time', $where['date'])->count();
                    if ($sum_user == 0) return 0;
                    $counts = bcdiv($chart, $sum_user, 4) * 100;
                    return $counts;
                    break;
                case 'quarter':
                    $quarter = UserModel::getMonth('n');
                    $quarter[0] = strtotime($quarter[0]);
                    $quarter[1] = strtotime($quarter[1]);
                    $sum_user = UserModel::where('add_time', 'between', $quarter)->count();
                    if ($sum_user == 0) return 0;
                    $counts = bcdiv($chart, $sum_user, 4) * 100;
                    return $counts;
                default:
                    //自定义时间
                    $quarter = explode('-', $where['date']);
                    $quarter[0] = strtotime($quarter[0]);
                    $quarter[1] = strtotime($quarter[1]);
                    $sum_user = UserModel::where('add_time', 'between', $quarter)->count();
                    if ($sum_user == 0) return 0;
                    $counts = bcdiv($chart, $sum_user, 4) * 100;
                    return $counts;
                    break;
            }
        } else {
            $num = UserModel::count();
            $chart = $num != 0 ? bcdiv($chart, $num, 5) * 100 : 0;
            return $chart;
        }
    }

    public function get_user_index($where, $name)
    {
        switch ($where['date']) {
            case null:
                $days = date("t", strtotime(date('Y-m', time())));
                $dates = [];
                $series = [];
                $times_list = [];
                foreach ($name as $key => $val) {
                    for ($i = 1; $i <= $days; $i++) {
                        if (!in_array($i . '号', $times_list)) {
                            array_push($times_list, $i . '号');
                        }
                        $time = $this->gettime(date("Y-m", time()) . '-' . $i);
                        if ($key == 0) {
                            $dates['data'][] = UserModel::where('add_time', 'between', $time)->count();
                        } else if ($key == 1) {
                            $dates['data'][] = UserModel::consume(true, $time);
                        }
                    }
                    $dates['name'] = $val;
                    $dates['type'] = 'line';
                    $series[] = $dates;
                    unset($dates);
                }
                return ['time' => $times_list, 'series' => $series];
            case 'today':
                $dates = [];
                $series = [];
                $times_list = [];
                foreach ($name as $key => $val) {
                    for ($i = 0; $i <= 24; $i++) {
                        $strtitle = $i . '点';
                        if (!in_array($strtitle, $times_list)) {
                            array_push($times_list, $strtitle);
                        }
                        $time = $this->gettime(date("Y-m-d ", time()) . $i);
                        if ($key == 0) {
                            $dates['data'][] = UserModel::where('add_time', 'between', $time)->count();
                        } else if ($key == 1) {
                            $dates['data'][] = UserModel::consume(true, $time);
                        }
                    }
                    $dates['name'] = $val;
                    $dates['type'] = 'line';
                    $series[] = $dates;
                    unset($dates);
                }
                return ['time' => $times_list, 'series' => $series];
            case "week":
                $dates = [];
                $series = [];
                $times_list = [];
                foreach ($name as $key => $val) {
                    for ($i = 0; $i <= 6; $i++) {
                        if (!in_array('星期' . ($i + 1), $times_list)) {
                            array_push($times_list, '星期' . ($i + 1));
                        }
                        $time = UserModel::getMonth('h', $i);
                        if ($key == 0) {
                            $dates['data'][] = UserModel::where('add_time', 'between', [strtotime($time[0]), strtotime($time[1])])->count();
                        } else if ($key == 1) {
                            $dates['data'][] = UserModel::consume(true, [strtotime($time[0]), strtotime($time[1])]);
                        }
                    }
                    $dates['name'] = $val;
                    $dates['type'] = 'line';
                    $series[] = $dates;
                    unset($dates);
                }
                return ['time' => $times_list, 'series' => $series];
            case 'year':
                $dates = [];
                $series = [];
                $times_list = [];
                $year = date('Y');
                foreach ($name as $key => $val) {
                    for ($i = 1; $i <= 12; $i++) {
                        if (!in_array($i . '月', $times_list)) {
                            array_push($times_list, $i . '月');
                        }
                        $t = strtotime($year . '-' . $i . '-01');
                        $arr = explode('/', date('Y-m-01', $t) . '/' . date('Y-m-', $t) . date('t', $t));
                        if ($key == 0) {
                            $dates['data'][] = UserModel::where('add_time', 'between', [strtotime($arr[0]), strtotime($arr[1])])->count();
                        } else if ($key == 1) {
                            $dates['data'][] = UserModel::consume(true, [strtotime($arr[0]), strtotime($arr[1])]);
                        }
                    }
                    $dates['name'] = $val;
                    $dates['type'] = 'line';
                    $series[] = $dates;
                    unset($dates);
                }
                return ['time' => $times_list, 'series' => $series];
            case 'quarter':
                $dates = [];
                $series = [];
                $times_list = [];
                foreach ($name as $key => $val) {
                    for ($i = 1; $i <= 4; $i++) {
                        $arr = $this->gettime('quarter', $i);
                        if (!in_array(implode('--', $arr) . '季度', $times_list)) {
                            array_push($times_list, implode('--', $arr) . '季度');
                        }
                        if ($key == 0) {
                            $dates['data'][] = UserModel::where('add_time', 'between', [strtotime($arr[0]), strtotime($arr[1])])->count();
                        } else if ($key == 1) {
                            $dates['data'][] = UserModel::consume(true, [strtotime($arr[0]), strtotime($arr[1])]);
                        }
                    }
                    $dates['name'] = $val;
                    $dates['type'] = 'line';
                    $series[] = $dates;
                    unset($dates);
                }
                return ['time' => $times_list, 'series' => $series];
            default:
                $list = UserModel::consume($where, 'default');
                $dates = [];
                $series = [];
                $times_list = [];
                foreach ($name as $k => $v) {
                    foreach ($list as $val) {
                        $date = $val['add_time'];
                        if (!in_array($date, $times_list)) {
                            array_push($times_list, $date);
                        }
                        if ($k == 0) {
                            $dates['data'][] = $val['num'];
                        } else if ($k == 1) {
                            $dates['data'][] = UserBillAdmin::where(['uid' => $val['uid'], 'type' => 'pay_product'])->sum('number');
                        }
                    }
                    $dates['name'] = $v;
                    $dates['type'] = 'line';
                    $series[] = $dates;
                    unset($dates);
                }
                return ['time' => $times_list, 'series' => $series];
        }
    }

    public function gettime($time = '', $season = '')
    {
        if (!empty($time) && empty($season)) {
            $timestamp0 = strtotime($time);
            $timestamp24 = strtotime($time) + 86400;
            return [$timestamp0, $timestamp24];
        } else if (!empty($time) && !empty($season)) {
            $firstday = date('Y-m-01', mktime(0, 0, 0, ($season - 1) * 3 + 1, 1, date('Y')));
            $lastday = date('Y-m-t', mktime(0, 0, 0, $season * 3, 1, date('Y')));
            return [$firstday, $lastday];
        }
    }

    /**
     * TODO:需统一到数据统计模块 end
     */

    /**
     * 获取单个用户信息
     * @param $id 用户id
     * @return mixed
     */
    public function oneUserInfo($id)
    {
        $data = Util::getMore([
            ['type', ''],
            ['page', 1],
            ['limit', 20]
        ]);
        if ($data['type'] == '') return $this->fail('缺少参数');
        switch ($data['type']) {
            case 'spread':
                return $this->getSpreadList($id, $data['page'], $data['limit']);
                break;
            case 'order':
                return $this->getOneOrderList($id, $data['page'], $data['limit']);
                break;
            case 'integral':
                return $this->getOneIntegralList($id, $data['page'], $data['limit']);
                break;
            case 'sign':
                return $this->getOneSignList($id, $data['page'], $data['limit']);
                break;
            case 'coupon':
                return $this->getOneCouponsList($id, $data['page'], $data['limit']);
                break;
            case 'balance_change':
                return $this->getOneBalanceChangList($id, $data['page'], $data['limit']);
                break;
            default:
                return $this->fail('type参数错误');
        }
    }

    /**
     * 获取某个用户的推广下线
     */
    public function getSpreadList($id, $page, $limit)
    {
        return $this->success(UserModel::getSpreadList($id, (int)$page, (int)$limit));
    }

    /**
     * 获取某用户的订单列表
     */
    public function getOneOrderList($id, $page, $limit)
    {
        return $this->success(StoreOrder::getOneorderList(compact('id', 'page', 'limit')));
    }

    /**
     * 获取某用户的积分列表
     */
    public function getOneIntegralList($id, $page = 1, $limit = 20)
    {
        return $this->success(UserBillAdmin::getOneIntegralList(compact('id', 'page', 'limit')));
    }

    /**
     * 获取某用户的签到明细
     */
    public function getOneSignList($id, $page = 1, $limit = 20)
    {
        return $this->success(UserBillAdmin::getOneSignList(compact('id', 'page', 'limit')));
    }

    /**
     * 获取某用户的持有优惠劵
     */
    public function getOneCouponsList($id, $page = 1, $limit = 20)
    {
        return $this->success(StoreCouponUser::getOneCouponsList(compact('id', 'page', 'limit')));
    }

    /**
     * 获取某用户的余额变动记录
     */
    public function getOneBalanceChangList($id, $page = 1, $limit = 20)
    {
        return $this->success(UserBillAdmin::getOneBalanceChangList(compact('id', 'page', 'limit')));
    }
}
