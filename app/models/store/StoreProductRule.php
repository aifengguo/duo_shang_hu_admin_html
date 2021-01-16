<?php

namespace app\models\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 商品规则
 * @mixin think\Model
 */
class StoreProductRule extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_rule';

    use ModelTrait;

    /**
     * 列表
     * @param $where
     * @return array
     */
    public static function sysPage($where)
    {
        $model = new self;
        if ($where['rule_name']) $model = $model->where('rule_name','LIKE','%'.$where['rule_name'].'%');
        $model=$model->order('id desc');
        $count = $model->count();
        $list = $model->page((int)$where['page'], (int)$where['limit'])
            ->select()
            ->each(function ($item) {
                if ($item['rule_value']) {
                    $specs = json_decode($item['rule_value'],true);
                    if($specs){
                        foreach ($specs as $key=>$value){
                            $attr_name[] = $value['value'];
                            $attr_value[] = implode(',',$value['detail']);
                        }
                    }else{
                        $attr_name[] = '';
                        $attr_value[] = '';
                    }
                    $item['attr_name'] = implode(',',$attr_name);
                    $item['attr_value'] = $attr_value;
                }
            });
        return compact('count', 'list');
    }

    /**
     * 详情
     * @param $id
     * @return array
     */
    public static function sysInfo($id)
    {
        $info = self::get($id);
        $info['spec'] =json_decode($info['rule_value'],true);
        return compact('info');
    }
}
