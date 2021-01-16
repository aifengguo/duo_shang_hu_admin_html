<?php

namespace app\models\sms;

use app\models\system\SystemConfig;
use crmeb\basic\BaseModel;
use crmeb\services\sms\Sms;

/**
 * @mixin think\Model
 */
class SmsRecord extends BaseModel
{
    /**
     * 短信状态
     * @var array
     */
    protected static $resultcode = ['100' => '成功', '130' => '失败', '131' => '空号', '132' => '停机', '133' => '关机', '134' => '无状态'];


    protected function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public static function vaildWhere(array $where = [])
    {
        $account = SystemConfig::getConfigValue('sms_account');
        if ($account) {
            $model = self::where('uid', $account);
        } else {
            $model = new static();
        }
        if (isset($where['type']) && $where['type']) $model = $model->where('resultcode', $where['type']);
        return $model;
    }

    /**
     * 获取短信记录列表
     * @param $where
     * @return array
     */
    public static function getRecordList($where)
    {
        $data = self::vaildWhere($where)->page((int)$where['page'], (int)$where['limit'])->select();
        $count = self::vaildWhere($where)->count();
        return compact('count', 'data');
    }

    /**
     * 发送记录
     * @param $phone
     * @param $content
     * @param $template
     * @param $record_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function sendRecord($phone, $content, $template, $record_id)
    {
        $map = [
            'uid' => SystemConfig::getConfigValue('sms_account'),
            'phone' => $phone,
            'content' => $content,
            'add_time' => time(),
            'template' => $template,
            'record_id' => $record_id
        ];
        $msg = SmsRecord::create($map);
        if ($msg)
            return true;
        else
            return false;
    }

    /**
     * 定时任务修改短信发送记录短信状态
     */
    public static function modifyResultCode()
    {
        $time = time() - 600;
        $recordIds = self::where('resultcode', null)->whereTime('add_time', '<=', $time)->column('record_id');
        if (count($recordIds)) {
            $smsHandle = new Sms('yunxin', [
                'sms_account' => sys_config('sms_account'),
                'sms_token' => sys_config('sms_token'),
                'site_url' => sys_config('site_url')
            ]);
            $codeLists = $smsHandle->getStatus($recordIds);
            foreach ($codeLists as $item) {
                if (isset($item['id'])) {
                    if ($item['resultcode'] == '' || $item['resultcode'] == null) $item['resultcode'] = 134;
                    self::where('record_id', $item['id'])->update(['resultcode' => $item['resultcode']]);
                }
            }
        }
    }
}
