<?php

namespace app\adminapi\controller\v1\notification\sms;

use app\adminapi\controller\AuthController;
use crmeb\exceptions\AdminException;
use crmeb\services\{
    FormBuilder, sms\Sms, UtilService
};
use think\facade\Route as Url;

/**
 * 短信模板申请
 * Class SmsTemplateApply
 * @package app\admin\controller\sms
 */
class SmsTemplateApply extends AuthController
{
    /**
     * @var Sms
     */
    protected $smsHandle;

    /**
     * 构造函数 验证是否配置了短信
     * @return mixed|void
     */
    public function initialize()
    {
        parent::initialize();
        $this->smsHandle = new Sms('yunxin', [
            'sms_account' => sys_config('sms_account'),
            'sms_token' => sys_config('sms_token'),
            'site_url' => sys_config('site_url')
        ]);
        if (!$this->smsHandle->isLogin()) {
            throw new AdminException('请先填写短息配置');
        }
    }

    /**
     * 异步获取模板列表
     */
    public function index()
    {
        $where = UtilService::getMore([
            ['status', ''],
            ['title', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        $templateList = $this->smsHandle->template($where);
        if ($templateList['status'] == 400) return $this->fail($templateList['msg']);
        $arr = $templateList['data']['data'];
        foreach ($arr as $key => $value) {
            switch ($value['type']) {
                case 1:
                    $arr[$key]['type'] = '验证码';
                    break;
                case 2:
                    $arr[$key]['type'] = '通知';
                    break;
                case 3:
                    $arr[$key]['type'] = '推广';
                    break;
                default:
                    $arr[$key]['type'] = '';
                    break;
            }
        }
        $templateList['data']['data'] = $arr;
        return $this->success($templateList['data']);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return string
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function create()
    {
        $field = [
            FormBuilder::input('title', '模板名称'),
            FormBuilder::input('content', '模板内容')->type('textarea'),
            FormBuilder::radio('type', '模板类型', 1)->options([['label' => '验证码', 'value' => 1], ['label' => '通知', 'value' => 2], ['label' => '推广', 'value' => 3]])
        ];
        return $this->makePostForm('申请短信模板', $field, Url::buildUrl('/notify/sms/temp'), 'POST');
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        $data = UtilService::postMore([
            ['title', ''],
            ['content', ''],
            ['type', 0]
        ]);
        if (!strlen(trim($data['title']))) return $this->fail('请输入模板名称');
        if (!strlen(trim($data['content']))) return $this->fail('请输入模板内容');
        $applyStatus = $this->smsHandle->apply($data['title'], $data['content'], $data['type']);
        if ($applyStatus['status'] == 400) return $this->fail($applyStatus['msg']);
        return $this->success('申请成功');
    }
}