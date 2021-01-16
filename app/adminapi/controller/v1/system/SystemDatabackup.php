<?php

namespace app\adminapi\controller\v1\system;

use app\adminapi\controller\AuthController;
use \crmeb\services\MysqlBackupService as Backup;
use crmeb\services\UtilService;
use think\facade\Env;
use think\facade\Session;
use think\facade\Db;

/**
 * 数据备份
 * Class SystemDatabackup
 * @package app\admin\controller\system
 *
 */
class SystemDatabackup extends AuthController
{
    /**
     * @var Backup
     */
    protected $DB;

    public function initialize()
    {
        parent::initialize();
        $config = array(
            //数据库备份卷大小
            'compress' => 1,
            //数据库备份文件是否启用压缩 0不压缩 1 压缩
            'level' => 5,
        );
        $this->DB = new Backup($config);
    }

    /**
     * 获取数据库表
     */
    public function index()
    {
        $db = $this->DB;
        $list = $db->dataList();
        $count = count($list);
        return $this->success(compact('count', 'list'));
    }

    /**
     * 查看表结构 详情
     */
    public function read()
    {
        $database = Env::get("database.database");
        $tablename = request()->param('tablename', '', 'htmlspecialchars');
        $res = Db::query("select * from information_schema.columns where table_name = '" . $tablename . "' and table_schema = '" . $database . "'");
        $count = count($res);
        foreach ($res AS $key => $f) {
            $res[$key]['EXTRA'] = ($f['EXTRA'] == 'auto_increment' ? '是' : ' ');
        }
        return $this->success(['count' => $count, 'list' => $res]);
    }

    /**
     * 优化表
     */
    public function optimize()
    {
        $tables = $this->request->param('tables');
        $db = $this->DB;
        $res = $db->optimize($tables);
        return $this->success($res ? '优化成功' : '优化失败');
    }

    /**
     * 修复表
     */
    public function repair()
    {
        $tables = $this->request->param('tables');
        $db = $this->DB;
        $res = $db->repair($tables);
        return $this->success($res ? '修复成功' : '修复失败');
    }

    /**
     * 备份表
     */
    public function backup()
    {
        $tables = $this->request->param('tables');
        $tables = explode(',', $tables);
        $db = $this->DB;
        $data = '';
        foreach ($tables as $t) {
            $res = $db->backup($t, 0);
            if ($res == false && $res != 0) {
                $data .= $t . '|';
            }
        }
        return $this->success($data ? '备份失败' . $data : '备份成功');
    }

    /**
     * 获取备份记录表
     */
    public function fileList()
    {
        $db = $this->DB;
        $files = $db->fileList();
        $data = [];
        foreach ($files as $key => $t) {
            $data[$key]['filename'] = $t['filename'];
            $data[$key]['part'] = $t['part'];
            $data[$key]['size'] = $t['size'] . 'B';
            $data[$key]['compress'] = $t['compress'];
            $data[$key]['backtime'] = $key;
            $data[$key]['time'] = $t['time'];
        }
        krsort($data);//根据时间降序
        return $this->success(['count' => count($data), 'list' => array_values($data)]);
    }

    /**
     * 删除备份记录表
     */
    public function delFile()
    {
        $filename = intval(request()->post('filename'));
        $files = $this->DB->delFile($filename);
        return $this->success('删除成功');
    }

    /**
     * 导入备份记录表
     */
    public function import()
    {
        [$part, $start, $time] = UtilService::postMore([
            [['part', 'd'], 0],
            [['start', 'd'], 0],
            [['time', 'd'], 0],
        ], null, true);
        $db = $this->DB;
        if (is_numeric($time) && !$start) {
            $list = $db->getFile('timeverif', $time);
            if (is_array($list)) {
                session::set('backup_list', $list);
                return $this->success('初始化完成！', array('part' => 1, 'start' => 0));
            } else {
                return $this->fail('备份文件可能已经损坏，请检查！');
            }
        } else if (is_numeric($part) && is_numeric($start) && $part && $start) {
            $list = session::get('backup_list');
            $start = $db->setFile($list)->import($start);
            if (false === $start) {
                return $this->fail('还原数据出错！');
            } elseif (0 === $start) {
                if (isset($list[++$part])) {
                    $data = array('part' => $part, 'start' => 0);
                    return $this->success("正在还原...#{$part}", $data);
                } else {
                    session::delete('backup_list');
                    return $this->success('还原完成！');
                }
            } else {
                $data = array('part' => $part, 'start' => $start[0]);
                if ($start[1]) {
                    $rate = floor(100 * ($start[0] / $start[1]));
                    return $this->success("正在还原...#{$part}({$rate}%)", $data);
                } else {
                    $data['gz'] = 1;
                    return $this->success("正在还原...#{$part}", $data);
                }
                return $this->success("正在还原...#{$part}");
            }
        } else {
            return $this->fail('参数错误！');
        }
    }

    /**
     * 下载备份记录表
     */
    public function downloadFile()
    {
        $time = intval(request()->param('time'));
        $this->DB->downloadFile($time);
    }
}
