<?php

namespace app\adminapi\controller\v1\product;

use app\adminapi\controller\AuthController;
use app\models\store\StoreProductRule as ProductRuleModel;
use crmeb\services\UtilService;
use think\Request;

/**
 * 规则管理
 * Class StoreProductRule
 * @package app\adminapi\controller\v1\product
 */
class StoreProductRule extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 15],
            ['rule_name','']
        ]);
        $list = ProductRuleModel::sysPage($where);
        return $this->success($list);
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request,$id)
    {
        $data = UtilService::postMore([
            ['rule_name',''],
            ['spec',[]]
        ]);
        if ($data['rule_name'] == '') return $this->fail('请输入规则名称');
        if (!$data['spec']) return $this->fail('缺少规则值');
        $data['rule_value'] = json_encode($data['spec']);
        unset($data['spec']);
        if ($id){
            $rule = ProductRuleModel::get($id);
            if (!$rule) return $this->fail('数据不存在');
            ProductRuleModel::edit($data, $id);
            return $this->success('编辑成功!');
        }else{
            ProductRuleModel::create($data);
            return $this->success('规则添加成功!');
        }
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        $info = ProductRuleModel::sysInfo($id);
        return $this->success($info);
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete()
    {
        $data = UtilService::postMore([
            ['ids','']
        ]);
        if ($data['ids']=='') return $this->fail('请至少选择一条数据');
        $ids = strval($data['ids']);
        $res = ProductRuleModel::whereIn('id',$ids)->delete();
        if (!$res)
            return $this->fail(ProductRuleModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }
}
