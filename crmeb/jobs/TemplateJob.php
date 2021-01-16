<?php
/**
 * @author: liaofei<136327134@qq.com>
 * @day: 2020/5/21
 */

namespace crmeb\jobs;


use crmeb\basic\BaseJob;
use crmeb\interfaces\JobInterface;
use think\facade\Db;
use think\queue\Job;

/**
 * Class TemplateJob
 * @package crmeb\jobs
 */
class TemplateJob extends BaseJob
{
    public function doJob()
    {
        Db::name('cache')->insert(['key' => 'wx' . rand(1, 999), 'result' => '[sads]']);
        return true;
    }
}