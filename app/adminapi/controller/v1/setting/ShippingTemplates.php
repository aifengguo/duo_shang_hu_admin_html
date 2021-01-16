<?php

namespace app\adminapi\controller\v1\setting;

use app\models\system\{ShippingTemplates as STModel, SystemCity, ShippingTemplatesRegion, ShippingTemplatesFree};
use app\adminapi\controller\AuthController;
use crmeb\services\UtilService;

class ShippingTemplates extends AuthController
{
    /**
     * 运费模板列表
     * @return mixed
     */
    public function temp_list()
    {
        $where = UtilService::getMore([
            [['page', 'd'], 1],
            [['limit', 'd'], 20],
            [['name', 's'], '']
        ]);
        return $this->success(STModel::getList($where));
    }

    /**
     * 修改
     * @return string
     * @throws \Exception
     */
    public function edit($id = 0)
    {
        $templates = STModel::get($id);
        if (!$templates) {
            return $this->fail('修改的模板不存在');
        }
        $data['appointList'] = ShippingTemplatesFree::getFreeList($id);
        $data['templateList'] = ShippingTemplatesRegion::getRegionList($id);
        if (!isset($data['templateList'][0]['region'])) {
            $data['templateList'][0]['region'] = ['city_id' => 0, 'name' => '默认全国'];
        }
        $data['formData'] = [
            'name' => $templates->name,
            'type' => $templates->getData('type'),
            'appoint_check' => intval($templates->getData('appoint')),
            'sort' => intval($templates->getData('sort')),
        ];
        return $this->success($data);
    }

    /**
     * 保存或者修改
     * @param int $id
     */
    public function save($id = 0)
    {
        $data = UtilService::postMore([
            [['region_info', 'a'], []],
            [['appoint_info', 'a'], []],
            [['sort', 'd'], 0],
            [['type', 'd'], 0],
            [['name', 's'], ''],
            [['appoint', 'd'], 0],
        ]);
        $this->validate($data, \app\adminapi\validates\setting\ShippingTemplatesValidate::class, 'save');
        $temp['name'] = $data['name'];
        $temp['type'] = $data['type'];
        $temp['appoint'] = $data['appoint'];
        $temp['sort'] = $data['sort'];
        $temp['add_time'] = time();
        STModel::beginTrans();
        $res = true;
        try {
            if ($id) {
                $res = STModel::where('id', $id)->update($temp);
            } else {
                $id = STModel::insertGetId($temp);
            }
            //设置区域配送
            $res = $res && ShippingTemplatesRegion::saveRegion($data['region_info'], $data['type'], $id);
            if (!$res) {
                STModel::rollbackTrans();
                return $this->fail(ShippingTemplatesRegion::getErrorInfo());
            }
            //设置指定包邮
            if ($data['appoint']) {
                $res = $res && ShippingTemplatesFree::saveFree($data['appoint_info'], $data['type'], $id);
            }
            if ($res) {
                STModel::commitTrans();
                return $this->success('保存成功');
            } else {
                STModel::rollbackTrans();
                return $this->fail(ShippingTemplatesFree::getErrorInfo('保存失败'));
            }
        } catch (\Throwable $e) {
            STModel::rollbackTrans();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除运费模板
     */
    public function delete()
    {
        $data = UtilService::getMore([
            [['id', 'd'], 0],
        ]);
        if ($data['id'] == 1) {
            return $this->fail('默认模板不能删除');
        } else {
            STModel::del($data['id']);
            ShippingTemplatesRegion::where('temp_id', $data['id'])->delete();
            ShippingTemplatesFree::where('temp_id', $data['id'])->delete();
            return $this->success('删除成功');
        }
    }

    /**
     * 城市数据
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function city_list()
    {
        $list = SystemCity::with('children')->where('parent_id', 0)->order('id asc')->select();
        return $this->success($list->toArray());
    }
}