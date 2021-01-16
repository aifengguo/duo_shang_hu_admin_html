<?php

namespace app\adminapi\controller\v1\system;

use app\adminapi\controller\AuthController;
use app\models\system\SystemFile as SystemFileModel;
use crmeb\services\FileService as FileClass;

/**
 * 文件校验控制器
 * Class SystemFile
 * @package app\admin\controller\system
 *
 */
class SystemFile extends AuthController
{
    /**
     * 文件校验记录
     * @return mixed
     */
    public function index()
    {
        $rootPath = app()->getRootPath();
        $app = $this->getDir($rootPath . 'app');
        $extend = $this->getDir($rootPath . 'crmeb');
        $public = $this->getDir($rootPath . 'public');
        $arr = array();
        $arr = array_merge($app, $extend);
        $arr = array_merge($arr, $public);
        $fileAll = array();//本地文件
        $cha = array();//不同的文件
        foreach ($arr as $k => $v) {
            $fp = fopen($v, 'r');
            if (filesize($v)) $ct = fread($fp, filesize($v));
            else $ct = null;
            fclose($fp);
            $cthash = md5($ct);
            $update_time = stat($v);
            $fileAll[$k]['cthash'] = $cthash;
            $fileAll[$k]['filename'] = substr($v, strlen($rootPath));
            $fileAll[$k]['atime'] = $update_time['atime'];
            $fileAll[$k]['mtime'] = $update_time['mtime'];
            $fileAll[$k]['ctime'] = $update_time['ctime'];
        }
        $file = SystemFileModel::all(function ($query) {
            $query->order('atime', 'desc');
        })->toArray();//数据库中的文件
        if (empty($file)) {
            $data_num = array_chunk($fileAll, 10);
            SystemFileModel::beginTrans();
            $res = true;
            foreach ($data_num as $k => $v) {
                $res = $res && SystemFileModel::insertAll($v);
            }
            SystemFileModel::checkTrans($res);
            if ($res) {
                $cha = array();//不同的文件
            } else {
                $cha = $fileAll;
            }
        } else {
            $cha = array();//差异文件
            foreach ($file as $k => $v) {
                foreach ($fileAll as $ko => $vo) {
                    if ($v['filename'] == $vo['filename']) {
                        if ($v['cthash'] != $vo['cthash']) {
//                            $cha[$k]['filename'] = $v['filename'];
//                            $cha[$k]['cthash'] = $v['cthash'];
//                            $cha[$k]['atime'] = $v['atime'];
//                            $cha[$k]['mtime'] = $v['mtime'];
//                            $cha[$k]['ctime'] = $v['ctime'];
//                            $cha[$k]['type'] = '已修改';
                            $cha[] = [
                                'filename' => $v['filename'],
                                'cthash' => $v['cthash'],
                                'atime' => date('Y-m-d H:i:s', $v['atime']),
                                'mtime' => date('Y-m-d H:i:s', $v['mtime']),
                                'ctime' => date('Y-m-d H:i:s', $v['ctime']),
                                'type' => '已修改',

                            ];
                        }
                        unset($fileAll[$ko]);
                        unset($file[$k]);
                    }
                }

            }
            foreach ($file as $k => $v) {
//                $cha[$k]['filename'] = $v['filename'];
//                $cha[$k]['cthash'] = $v['cthash'];
//                $cha[$k]['atime'] = $v['atime'];
//                $cha[$k]['mtime'] = $v['mtime'];
//                $cha[$k]['ctime'] = $v['ctime'];
//                $cha[$k]['type'] = '已删除';
                $cha[] = [
                    'filename' => $v['filename'],
                    'cthash' => $v['cthash'],
                    'atime' => date('Y-m-d H:i:s', $v['atime']),
                    'mtime' => date('Y-m-d H:i:s', $v['mtime']),
                    'ctime' => date('Y-m-d H:i:s', $v['ctime']),
                    'type' => '已删除',

                ];
            }
            foreach ($fileAll as $k => $v) {
//                $cha[$k]['filename'] = $v['filename'];
//                $cha[$k]['cthash'] = $v['cthash'];
//                $cha[$k]['atime'] = $v['atime'];
//                $cha[$k]['mtime'] = $v['mtime'];
//                $cha[$k]['ctime'] = $v['ctime'];
//                $cha[$k]['type'] = '新增的';
                $cha[] = [
                    'filename' => $v['filename'],
                    'cthash' => $v['cthash'],
                    'atime' => date('Y-m-d H:i:s', $v['atime']),
                    'mtime' => date('Y-m-d H:i:s', $v['mtime']),
                    'ctime' => date('Y-m-d H:i:s', $v['ctime']),
                    'type' => '新增的',

                ];
            }

        }
        array_multisort(array_column($cha, 'ctime'), SORT_DESC, $cha);
        return $this->success(['list' => $cha]);
    }

    /**
     * 获取文件夹中的文件 包括子文件 不能直接用  直接使用  $this->getDir()方法 P156
     * @param $path
     * @param $data
     */
    public function searchDir($path, &$data)
    {
        if (is_dir($path) && !strpos($path, 'uploads')) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..') {
                    $this->searchDir($path . '/' . $file, $data);
                }
            }
            $dp->close();
        }
        if (is_file($path)) {
            $data[] = $path;
        }
    }

    /**
     * 获取文件夹中的文件 包括子文件
     * @param $dir
     * @return array
     */
    public function getDir($dir)
    {
        $data = array();
        $this->searchDir($dir, $data);
        return $data;
    }

    //打开目录
    public function opendir()
    {
        $fileAll = array('dir' => [], 'file' => []);
        //根目录
        $rootdir = app()->getRootPath();
//        return $rootdir;
        //当前目录
        $request_dir = app('request')->param('dir');
        //防止查看站点以外的目录
        if (strpos($request_dir, $rootdir) === false) {
            $request_dir = $rootdir;
        }
        //判断是否是返回上级
        if (app('request')->param('superior') && !empty($request_dir)) {
            if (strpos(dirname($request_dir), $rootdir) !== false) {
                $dir = dirname($request_dir);
            } else {
                $dir = $rootdir;
            }

        } else {
            $dir = !empty($request_dir) ? $request_dir : $rootdir;
            $dir = rtrim($dir, DS) . DS . app('request')->param('filedir');
        }
        $list = scandir($dir);
        foreach ($list as $key => $v) {
            if ($v != '.' && $v != '..') {
                if (is_dir($dir . DS . $v)) {
                    $fileAll['dir'][] = FileClass::listInfo($dir . DS . $v);
                }
                if (is_file($dir . DS . $v)) {
                    $fileAll['file'][] = FileClass::listInfo($dir . DS . $v);
                }
            }
        }
        //兼容windows
        $uname = php_uname('s');
        if (strstr($uname, 'Windows') !== false) {
            $dir = ltrim($dir, '\\');
            $rootdir = str_replace('\\', '\\\\', $rootdir);
        }
        $list = array_merge($fileAll['dir'], $fileAll['file']);
        foreach ($list as $key => $value) {
            $list[$key]['real_path'] = str_replace($rootdir, '', $value['pathname']);
            $list[$key]['mtime'] = date('Y-m-d H:i:s',$value['mtime']);
        }
        return $this->success(compact('dir', 'list'));
    }

    //读取文件
    public function openfile()
    {
        $file = $this->request->param('filepath');
        if (empty($file)) return $this->fail('出现错误');
        $filepath = $file;
        $content = FileClass::readFile($filepath);//防止页面内嵌textarea标签
        $ext = FileClass::getExt($filepath);
        $extarray = [
            'js' => 'text/javascript'
            , 'php' => 'text/x-php'
            , 'html' => 'text/html'
            , 'sql' => 'text/x-mysql'
            , 'css' => 'text/x-scss'];
        $mode = empty($extarray[$ext]) ? '' : $extarray[$ext];
        return $this->success(compact('content', 'mode', 'filepath'));
    }

    //保存文件
    public function savefile()
    {
        $comment = $this->request->param('comment');
        $filepath = $this->request->param('filepath');
        if (!empty($comment) && !empty($filepath)) {
            //兼容windows
            $uname = php_uname('s');
            if (strstr($uname, 'Windows') !== false)
                $filepath = ltrim(str_replace('/', DS, $filepath), '.');
            if (FileClass::isWritable($filepath)) {
                $res = FileClass::writeFile($filepath, $comment);
                if ($res) {
                    return $this->success('保存成功!');
                } else {
                    return $this->fail('保存失败');
                }
            } else {
                return $this->fail('没有权限！');
            }

        } else {
            return $this->fail('出现错误');
        }

    }
}
