<?php

namespace app\adminapi\controller\v1\user;

use app\adminapi\controller\AuthController;
use app\models\system\{SystemUserLevel, SystemUserTask};
use crmeb\services\{FormBuilder as Form, UtilService};
use think\facade\Route as Url;
use crmeb\traits\CurdControllerTrait;

/**
 * 会员设置
 * Class UserLevel
 * @package app\adminapi\controller\v1\user
 */
class UserLevel extends AuthController
{
    use CurdControllerTrait;

    /**
     * 会员详情
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        $info = SystemUserLevel::get($id);
        return $this->success(compact('info'));
    }

    /*
     * 获取添加资源表单
     * */
    public function create()
    {
        $where = UtilService::getMore(
            ['id', 0]
        );
        if ($where['id']) {
            $vipinfo = SystemUserLevel::get($where['id']);
            $field[] = Form::hidden('id', $where['id']);
            $msg = '编辑会员等级';
        } else {
            $msg = '添加会员等级';
        }
        $field[] = Form::input('name', '等级名称', isset($vipinfo) ? $vipinfo->name : '')->col(Form::col(24));
//        $field[] = Form::radio('is_forever', '是否为永久', isset($vipinfo) ? $vipinfo->is_forever : 0)->options([['label' => '永久', 'value' => 1], ['label' => '非永久', 'value' => 0]])->col(24);
//        $field[] = Form::number('money', '等级价格', isset($vipinfo) ? $vipinfo->money : 0)->min(0)->col(24);
//        $field[] = Form::radio('is_pay', '是否需要购买', isset($vipinfo) ? $vipinfo->is_pay : 0)->options([['label' => '需要', 'value' => 1], ['label' => '免费', 'value' => 0]])->col(24);
        $field[] = Form::number('valid_date', '有效时间(天)', isset($vipinfo) ? $vipinfo->valid_date : 0)->min(0)->col(8);
        $field[] = Form::number('grade', '等级', isset($vipinfo) ? $vipinfo->grade : 0)->min(0)->col(8);
        $field[] = Form::number('discount', '享受折扣', isset($vipinfo) ? $vipinfo->discount : 0)->min(0)->col(8);
        $field[] = Form::frameImageOne('icon', '图标', Url::buildUrl('admin/widget.images/index', array('fodder' => 'icon')), isset($vipinfo) ? $vipinfo->icon : '')->icon('ios-add')->width('60%')->height('435px');
        $field[] = Form::frameImageOne('image', '会员背景', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), isset($vipinfo) ? $vipinfo->image : '')->icon('ios-add')->width('60%')->height('435px');
        $field[] = Form::radio('is_show', '是否显示', isset($vipinfo) ? $vipinfo->is_show : 0)->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]])->col(24);
        $field[] = Form::textarea('explain', '等级说明', isset($vipinfo) ? $vipinfo->explain : '');
        return $this->makePostForm($msg, $field, Url::buildUrl('/user/user_level'), 'POST');
    }

    /*
     * 会员等级添加或者修改
     * @param $id 修改的等级id
     * @return json
     * */
    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['name', ''],
            ['is_forever', 0],
            ['money', 0],
            ['is_pay', 0],
            ['valid_date', 0],
            ['grade', 0],
            ['discount', 0],
            ['icon', ''],
            ['image', ''],
            ['is_show', ''],
            ['explain', ''],
        ]);
        if ($data['valid_date'] == 0) $data['is_forever'] = 1;//有效时间为0的时候就是永久
        $id = $data['id'];
        if (!$data['name']) return $this->fail('请输入等级名称');
        if (!$data['grade']) return $this->fail('请输入等级');
        if (!$data['explain']) return $this->fail('请输入等级说明');
//        if ($data['is_forever'] == 0 && !$data['valid_date']) return $this->fail('请输入有效时间(天)');
//        if ($data['is_pay']) return $this->fail('会员等级购买功能正在开发中，暂时关闭可购买功能！');
//        if ($data['is_pay'] && !$data['money']) return $this->fail('请输入购买金额');
        if (!$data['icon']) return $this->fail('请上传等级图标');
        if (!$data['image']) return $this->fail('请上传等级背景图标');
        if (!$id && SystemUserLevel::be(['is_del' => 0, 'grade' => $data['grade']])) return $this->fail('已检测到您设置过的会员等级，此等级不可重复');
        SystemUserLevel::beginTrans();
        try {
            //修改
            if ($id) {
                if (SystemUserLevel::edit($data, $id)) {
                    SystemUserLevel::commitTrans();
                    return $this->success('修改成功');
                } else {
                    SystemUserLevel::rollbackTrans();
                    return $this->fail('修改失败');
                }
            } else {
                //新增
                $data['add_time'] = time();
                if (SystemUserLevel::create($data)) {
                    SystemUserLevel::commitTrans();
                    return $this->success('添加成功');
                } else {
                    SystemUserLevel::rollbackTrans();
                    return $this->fail('添加失败');
                }
            }
        } catch (\Exception $e) {
            SystemUserLevel::rollbackTrans();
            return $this->fail($e->getMessage());
        }
    }

    /*
     * 获取系统设置的vip列表
     * @param int page
     * @param int limit
     * */
    public function get_system_vip_list()
    {
        $where = UtilService::getMore([
            ['page', 0],
            ['limit', 10],
            ['title', ''],
            ['is_show', ''],
        ]);
        return $this->success(SystemUserLevel::getSytemList($where));
    }

    /*
     * 删除会员等级
     * @param int $id
     * */
    public function delete($id)
    {
        if (SystemUserLevel::edit(['is_del' => 1], $id))
            return $this->success('删除成功');
        else
            return $this->fail('删除失败');
    }

    /**
     * 设置会员等级显示|隐藏
     *
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {
        if ($is_show == '' || $id == '') return $this->fail('缺少参数');
        $res = SystemUserLevel::where(['id' => $id])->update(['is_show' => (int)$is_show]);
        if ($res) {
            return $this->success($is_show == 1 ? '显示成功' : '隐藏成功');
        } else {
            return $this->fail($is_show == 1 ? '显示失败' : '隐藏失败');
        }
    }

    /**
     * 等级列表快速编辑
     * field:value name:钻石会员/grade:8/discount:92.00
     * @return json
     */
    public function set_value($id)
    {
        $data = UtilService::postMore([
            ['field', ''],
            ['value', '']
        ], $this->request);
        if ($data['field'] == '' || $data['value'] == '') return $this->fail('缺少参数');
        if (SystemUserLevel::where(['id' => $id])->update([$data['field'] => $data['value']]))
            return $this->success('保存成功');
        else
            return $this->fail('保存失败');
    }

    /*
 * 异步获取等级任务列表
 * @param int $level_id 会员id
 * @param int $page 分页
 * @param int $limit 显示条数
 * @return json
 * */
    public function get_task_list($level_id)
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 10],
            ['name', ''],
            ['is_show', '']
        ], $this->request);
        return $this->success(SystemUserTask::taskList($level_id, $where));
    }

    /**
     * 快速编辑等级任务 目前仅排序 sort:2
     * $id 等级任务id
     * @return json
     */
    public function set_task_value($id)
    {
        $data = UtilService::postMore([
            ['field', ''],
            ['value', '']
        ]);
        if ($data['field'] == '' || $data['value'] == '') return $this->fail('缺少参数');
        if (SystemUserTask::where(['id' => $id])->update([$data['field'] => $data['value']]))
            return $this->success('保存成功');
        else
            return $this->fail('保存失败');
    }

    /**
     * 设置等级任务显示|隐藏
     *
     * @return json
     */
    public function set_task_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && $this->fail('缺少参数');
        $res = SystemUserTask::where(['id' => $id])->update(['is_show' => (int)$is_show]);
        if ($res) {
            return $this->success($is_show == 1 ? '显示成功' : '隐藏成功');
        } else {
            return $this->fail($is_show == 1 ? '显示失败' : '隐藏失败');
        }
    }

    /**
     * 设置任务是否务必达成
     *
     * @return json
     */
    public function set_task_must($is_must = '', $id = '')
    {
        ($is_must == '' || $id == '') && $this->fail('缺少参数');
        $res = SystemUserTask::where(['id' => $id])->update(['is_must' => (int)$is_must]);
        if ($res) {
            return $this->success('设置成功');
        } else {
            return $this->fail('设置失败');
        }
    }

    /*
     * 生成任务表单
     * @param int $id 任务id
     * @param int $vip_id 会员id
     * @return html
     * */
    public function create_task()
    {
        $where = UtilService::getMore([
            ['id', 0],
            ['level_id', 0]
        ]);
        if ($where['id']) $tash = SystemUserTask::get($where['id']);
        $field[] = Form::select('task_type', '任务类型', isset($tash) ? $tash->task_type : '')->setOptions(function () {
            $list = SystemUserTask::getTaskTypeAll();
            $menus = [];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['type'], 'label' => $menu['name'] . '----单位[' . $menu['unit'] . ']'];
            }
            return $menus;
        })->filterable(1);
        if ($where['id']) $field[] = Form::hidden('id', $where['id']);
        $field[] = Form::hidden('level_id', $where['level_id']);
        $field[] = Form::number('number', '限定数量', isset($tash) ? $tash->number : 0)->min(0)->col(24);
        $field[] = Form::number('sort', '排序', isset($tash) ? $tash->sort : 0)->min(0)->col(24);
        $field[] = Form::radio('is_show', '是否显示', isset($tash) ? $tash->is_show : 1)->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]])->col(24);
        $field[] = Form::radio('is_must', '是否务必达成', isset($tash) ? $tash->is_must : 1)->options([['label' => '务必达成', 'value' => 1], ['label' => '完成其一', 'value' => 0]])->col(24);
        $field[] = Form::textarea('illustrate', '任务说明', isset($tash) ? $tash->illustrate : '');
        return $this->makePostForm('添加任务', $field, Url::buildUrl('/user/user_level/save_task'), 'POST');
    }

    /*
     * 保存或者修改任务
     * @param int $id 任务id
     * @param int $vip_id 会员id
     * */
    public function save_task()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['level_id', 0],
            ['task_type', ''],
            ['number', 0],
            ['is_show', 0],
            ['sort', 0],
            ['is_must', 0],
            ['illustrate', ''],
        ]);
        if (!$data['level_id']) return $this->fail('缺少参数');
        if (!$data['task_type']) return $this->fail('请选择任务类型');
        if ($data['number'] < 1) return $this->fail('请输入限定数量,数量不能小于1');
        $tash = SystemUserTask::getTaskType($data['task_type']);
        if ($tash['max_number'] != 0 && $data['number'] > $tash['max_number']) return $this->fail('您设置的限定数量超出最大限制,最大限制为:' . $tash['max_number']);
        $data['name'] = SystemUserTask::setTaskName($data['task_type'], $data['number']);
        try {
            if ($data['id']) {
                SystemUserTask::edit($data, $data['id']);
                return $this->success('修改成功');
            } else {
                $data['add_time'] = time();
                $data['real_name'] = $tash['real_name'];
                if (SystemUserTask::create($data))
                    return $this->success('添加成功');
                else
                    return $this->fail('添加失败');
            }
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /*
 * 删除任务
 * @param int 任务id
 * */
    public function delete_task($id)
    {
        if (!$id) return $this->fail('缺少参数');
        if (SystemUserTask::del($id))
            return $this->success('删除成功');
        else
            return $this->fail('删除失败');
    }

}