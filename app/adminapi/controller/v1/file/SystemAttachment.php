<?php

namespace app\adminapi\controller\v1\file;

use app\adminapi\controller\AuthController;
use think\facade\Route as Url;
use crmeb\services\{
    UtilService as Util, FormBuilder as Form
};
use app\models\system\{
    SystemAttachment as SystemAttachmentModel, SystemAttachmentCategory as Category
};
use crmeb\services\UploadService;

/**
 * 图片管理类
 * Class SystemAttachment
 * @package app\adminapi\controller\v1\file
 */
class SystemAttachment extends AuthController
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
            ['limit', 18],
            ['pid', 0]
        ]);
        return $this->success(SystemAttachmentModel::getImageList($where));
    }

    /**
     * 删除指定资源
     *
     * @param string $ids
     * @return \think\Response
     */
    public function delete()
    {
        $request = app('request');
        $ids = $request->post('ids');
        $ids = explode(',', $ids);
        if (empty($ids))
            return $this->fail('请选择要删除的图片');
        foreach ($ids as $v) {
            self::deleteImg($v);
        }
        return $this->success('删除成功');
    }

    /**
     * 图片管理上传图片
     * @return \think\response\Json
     */
    public function upload($upload_type = 0)
    {
        [$pid, $file] = Util::postMore([
            ['pid', 0],
            ['file', 'file']
        ], $this->request, true);
        if ($upload_type == 0) {
            $upload_type = sys_config('upload_type', 1);
        }
        try {
            $path = make_path('attach', 2, true);
            $upload = UploadService::init($upload_type);
            $res = $upload->to($path)->validate()->move($file);
            if ($res === false) {
                return $this->fail($upload->getError());
            } else {
                $fileInfo = $upload->getUploadInfo();
                if ($fileInfo) {
                    SystemAttachmentModel::attachmentAdd($fileInfo['name'], $fileInfo['size'], $fileInfo['type'], $fileInfo['dir'], $fileInfo['thumb_path'], $pid, $upload_type, $fileInfo['time']);
                }
                return $this->success('上传成功', ['src' => $res->filePath]);
            }
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**删除图片和数据记录
     * @param $att_id
     */
    public function deleteImg($att_id)
    {
        $attinfo = SystemAttachmentModel::get($att_id);
        if ($attinfo) {
            try {
                $upload = UploadService::init($attinfo['image_type']);
                if ($attinfo['image_type'] == 1) {
                    if (strpos($attinfo['att_dir'], '/') == 0) {
                        $attinfo['att_dir'] = substr($attinfo['att_dir'], 1);
                    }
                    $upload->delete($attinfo['att_dir']);
                } else {
                    $upload->delete($attinfo['name']);
                }
            } catch (\Throwable $e) {
            }
            SystemAttachmentModel::where('att_id', $att_id)->delete();
        }
    }

    /**
     * 移动图片分类显示
     */
    public function move($images)
    {
        $formbuider = [];
        $formbuider[] = Form::hidden('images', $images);
        $formbuider[] = Form::select('pid', '选择分类')->setOptions(function () {
            $list = Category::getCateList();
            $options = [['value' => 0, 'label' => '所有分类']];
            foreach ($list as $id => $cateName) {
                $options[] = ['label' => $cateName['html'] . $cateName['name'], 'value' => $cateName['id']];
            }
            return $options;
        })->filterable(1);
        return $this->makePostForm('编辑分类', $formbuider, Url::buildUrl('/file/do_move'), 'PUT');
    }

    /**
     * 移动图片分类操作
     */
    public function moveImageCate()
    {
        $data = Util::postMore([
            'pid',
            'images'
        ]);
        if ($data['images'] == '') return $this->fail('请选择图片');
        if (!$data['pid']) return $this->fail('请选择分类');
        $res = SystemAttachmentModel::where('att_id', 'in', $data['images'])->update(['pid' => $data['pid']]);
        if ($res)
            return $this->success('移动成功');
        else
            return $this->fail('移动失败！');
    }

}
