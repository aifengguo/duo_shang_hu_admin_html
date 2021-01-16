<?php

namespace app\adminapi\controller\v1\application\wechat;

use app\models\wechat\WechatTemplate as WechatTemplateModel;
use app\adminapi\controller\AuthController;
use crmeb\services\{
    FormBuilder as Form, template\Template, UtilService as Util
};
use think\facade\Route as Url;
use think\Request;
use think\facade\Cache;

class WechatTemplate extends AuthController
{
    protected $cacheTag = '_system_wechat';

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
            ['name', ''],
            ['status', '']
        ], $this->request);
        $data = WechatTemplateModel::SystemPage($where);
        $industry = Cache::tag($this->cacheTag)->remember('_wechat_industry', function () {
            try {
                $cache = (new Template('wechat'))->getIndustry();
                if (!$cache) return [];
                Cache::tag($this->cacheTag, ['_wechat_industry']);
                return $cache->toArray();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }, 0) ?: [];
        !is_array($industry) && $industry = [];
        $industry['primary_industry'] = isset($industry['primary_industry']) ? $industry['primary_industry']['first_class'] . ' | ' . $industry['primary_industry']['second_class'] : '未选择';
        $industry['secondary_industry'] = isset($industry['secondary_industry']) ? $industry['secondary_industry']['first_class'] . ' | ' . $industry['secondary_industry']['second_class'] : '未选择';
        $lst = array(
            'industry' => $industry,
            'count' => $data['count'],
            'list' => $data['list']
        );
        return $this->success($lst);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $f = array();
        $f[] = Form::input('tempkey', '模板编号');
        $f[] = Form::input('tempid', '模板ID');
        $f[] = Form::input('name', '模板名');
        $f[] = Form::input('content', '回复内容')->type('textarea');
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return $this->makePostForm('添加模板消息', $f, Url::buildUrl('/app/wechat/template'), 'POST');
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
            'tempkey',
            'tempid',
            'name',
            'content',
            ['status', 0],
            ['type', 1]
        ]);
        if ($data['tempkey'] == '') return $this->fail('请输入模板编号');
        if ($data['tempkey'] != '' && WechatTemplateModel::be($data['tempkey'], 'tempkey'))
            return $this->fail('请输入模板编号已存在,请重新输入');
        if ($data['tempid'] == '') return $this->fail('请输入模板ID');
        if ($data['name'] == '') return $this->fail('请输入模板名');
        if ($data['content'] == '') return $this->fail('请输入回复内容');
        $data['add_time'] = time();
        WechatTemplateModel::create($data);
        return $this->success('添加模板消息成功!');
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
        if (!$id) return $this->fail('数据不存在');
        $product = WechatTemplateModel::get($id);
        if (!$product) return $this->fail('数据不存在!');
        $f = array();
        $f[] = Form::input('tempkey', '模板编号', $product->getData('tempkey'))->disabled(1);
        $f[] = Form::input('name', '模板名', $product->getData('name'))->disabled(1);
        $f[] = Form::input('tempid', '模板ID', $product->getData('tempid'));
        $f[] = Form::radio('status', '状态', $product->getData('status'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return $this->makePostForm('编辑模板消息', $f, Url::buildUrl('/app/wechat/template/' . $id), 'PUT');
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
            'tempid',
            ['status', 0],
            ['type', 1]
        ]);
        if ($data['tempid'] == '') return $this->fail('请输入模板ID');
        if (!$id) return $this->fail('数据不存在');
        $product = WechatTemplateModel::get($id);
        if (!$product) return $this->fail('数据不存在!');
        WechatTemplateModel::edit($data, $id);
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
        if (!$id) return $this->fail('数据不存在!');
        if (!WechatTemplateModel::del($id))
            return $this->fail(WechatTemplateModel::getErrorInfo('删除失败,请稍候再试!'));
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
        WechatTemplateModel::where(['id' => $id])->update(['status' => $status]);

        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }
}
