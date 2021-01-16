<?php
/**
 * @author: liaofei<136327134@qq.com>
 * @day: 2020/5/28
 */

namespace crmeb\basic;

use crmeb\interfaces\JobInterface;
use think\queue\Job;

class BaseJob implements JobInterface
{
    /**
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data): void
    {
        $action = $data['do'] ?? 'doJob';//任务名
        $infoData = $data['data'] ?? [];//执行数据
        $errorCount = $data['errorCount'] ?? 0;//最大错误次数
        $log = $data['log'] ?? null;
        var_dump($data);
        if ($this->{$action}($infoData)) {
            //删除任务
            $job->delete();
            //记录日志
            $this->info($log);
        } else {
            if ($job->attempts() > $errorCount && $errorCount) {
                //删除任务
                $job->delete();
                //记录日志
                $this->info($log);
            } else {
                //从新放入队列
                $job->release();
            }
        }
    }

    /**
     * 打印出成功提示
     * @param $log
     * @return bool
     */
    protected function info($log)
    {
        try {
            if (is_callable($log)) {
                print_r($log() . "\r\n");
            } else if (is_string($log) || is_array($log)) {
                print_r($log . "\r\n");
            }
        } catch (\Throwable $e) {
            print_r($e->getMessage());
        }
    }
}