<?php

namespace app\adminapi\controller\v1\application\wechat;

use app\adminapi\controller\AuthController;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\UtilService as Util;
use think\facade\Route as Url;
use app\models\wechat\StoreService as ServiceModel;
use app\models\wechat\StoreServiceLog as StoreServiceLog;
use app\models\wechat\WechatUser as UserModel;

/**
 * 客服管理
 * Class StoreService
 * @package app\admin\controller\store
 */
class StoreService extends AuthController
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
            ['limit', 20],
            ['mer_id', 0]
        ]);
        $list = ServiceModel::getList($where);
        return $this->success($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['data', ''],
            ['type', '']
//            ['tagid_list',''],
//            ['groupid','-1'],
//            ['sex',''],
//            ['export',''],
//            ['stair',''],
//            ['second',''],
//            ['order_stair',''],
//            ['order_second',''],
//            ['subscribe',''],
//            ['now_money',''],
//            ['is_promoter',''],
        ], $this->request);
        $list = UserModel::systemPage($where);
        return $this->success($list);
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        $params = Util::postMore([
            'uids'
        ]);
        if (count($params["uids"]) <= 0) return $this->fail('请选择要添加的用户!');
        if (ServiceModel::where('mer_id', 0)->where(array("uid" => array("in", implode(',', $params["uids"]))))->count()) return $this->fail('添加用户中存在已有的客服!');
        foreach ($params["uids"] as $key => $value) {
            $now_user = UserModel::get($value);
            $data[$key]["mer_id"] = 0;
            $data[$key]["uid"] = $now_user["uid"];
            $data[$key]["avatar"] = $now_user["headimgurl"];
            $data[$key]["nickname"] = $now_user["nickname"];
            $data[$key]["add_time"] = time();
        }
        ServiceModel::setAll($data);
        return $this->success('添加成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $service = ServiceModel::get($id);
        if (!$service) return $this->fail('数据不存在!');
        $f = array();
        $f[] = Form::frameImageOne('avatar', '客服头像', Url::buildUrl('admin/widget.images/index', array('fodder' => 'avatar')), $service['avatar'])->icon('ios-add')->width('60%')->height('435px');
        $f[] = Form::input('nickname', '客服名称', $service["nickname"]);
        $f[] = Form::radio('customer', '统计管理', $service['customer'])->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]);
        $f[] = Form::radio('notify', '订单通知', $service['notify'])->options([['value' => 1, 'label' => '开启'], ['value' => 0, 'label' => '关闭']]);
        $f[] = Form::radio('status', '客服状态', $service['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']]);
        return $this->makePostForm('编辑客服', $f, Url::buildUrl('/app/wechat/kefu/' . $id), 'PUT');
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function update($id)
    {
        $params = Util::postMore([
            ['avatar', ''],
            ['nickname', ''],
            ['status', 1],
            ['notify', 1],
            ['customer', 1]
        ]);
        if ($params["nickname"] == '') return $this->fail("客服名称不能为空！");
        $data = array("avatar" => $params["avatar"]
        , "nickname" => $params["nickname"]
        , 'status' => $params['status']
        , 'notify' => $params['notify']
        , 'customer' => $params['customer']
        );
        ServiceModel::edit($data, $id);
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
        if (!ServiceModel::del($id))
            return $this->fail(ServiceModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
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
        ServiceModel::where(['id' => $id])->update(['status' => $status]);

        return $this->success($status == 0 ? '隐藏成功' : '显示成功');
    }

    /**
     * 聊天记录
     *
     * @return \think\Response
     */
    public function chat_user($id)
    {
        $page = $this->request->param('page',1);
        $limit = $this->request->param('limit',20);
        $now_service = ServiceModel::get($id);
        if (!$now_service) return $this->fail('数据不存在!');
        $list = ServiceModel::getChatUser($now_service->toArray(), 0,$page,$limit);
        return $this->success(compact('list'));
    }

    /**
     * @param int $uid
     * @param int $to_uid
     * @return string
     */
    public function chat_list()
    {
        $where = Util::getMore([
            ['uid', 0],
            ['to_uid', 0],
            ['id', 0]
        ]);
        if ($where['uid']){
            $arr = $where;
            CacheService::set('admin_chat_list' . $this->adminId, $arr);
        }
        $where = CacheService::get('admin_chat_list' . $this->adminId);
        $list = StoreServiceLog::getChatList($where, 0);
        return $this->success($list);
    }
}
