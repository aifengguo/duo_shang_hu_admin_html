<?php
/**
 * Created by PhpStorm.
 * User: lofate
 * Date: 2019/12/19
 * Time: 09:30
 */

namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\UtilService as Util;
use app\models\store\{
    StoreDescription, StorePink, StoreCombination as StoreCombinationModel, StoreProductAttr
};

/**
 * 拼团管理
 * Class StoreCombination
 * @package app\admin\controller\store
 */
class StoreCombination extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = StoreCombinationModel::class;

    /**
     * 拼团列表
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['is_show', ''],
            ['store_name', '']
        ]);
        $list = StoreCombinationModel::systemPage($where);
        return $this->success($list);
    }

    /**
     * 拼团统计
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function statistics()
    {
        $info = StoreCombinationModel::getStatistics();
        return $this->success($info);
    }

    /**
     * 详情
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        $info = StoreCombinationModel::getOne($id);
        return $this->success(compact('info'));
    }

    /**
     * 保存新建的资源
     * @param int $id
     */
    public function save($id = 0)
    {
        $data = Util::postMore([
            [['product_id', 'd'], 0],
            [['title', 's'], ''],
            [['info', 's'], ''],
            [['unit_name', 's'], ''],
            ['image', ''],
            ['images', []],
            ['section_time', []],
            [['is_host', 'd'], 0],
            [['is_show', 'd'], 0],
            [['num', 'd'], 0],
            [['temp_id', 'd'], 0],
            [['effective_time', 'd'], 0],
            [['people', 'd'], 0],
            [['description', 's'], ''],
            ['attrs', []],
            ['items', []],
            ['num', 1],
            ['sort', 0]
        ]);
        $this->validate($data, \app\adminapi\validates\marketing\StoreCombinationValidate::class, 'save');
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        $data['images'] = json_encode($data['images']);
        $data['price'] = min(array_column($detail, 'price'));
        $data['quota'] = $data['quota_show'] = array_sum(array_column($detail, 'quota'));
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        unset($data['section_time'], $data['description'], $data['attrs'], $data['items']);
        if ($id) {
            $product = StoreCombinationModel::get($id);
            if (!$product) return $this->fail('数据不存在!');
            $data['product_id'] = $product['product_id'];
            StoreCombinationModel::edit($data, $id);
            StoreProductAttr::createProductAttr($items, $detail, $id, 3);
            StoreDescription::saveDescription($description, $id, 3);
            return $this->success('编辑成功!');
        } else {
            $data['add_time'] = time();
            $combination_id = StoreCombinationModel::insertGetId($data);
            StoreProductAttr::createProductAttr($items, $detail, $combination_id, 3);
            StoreDescription::saveDescription($description, $combination_id, 3);
            return $this->success('添加拼团成功!');
        }
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('数据不存在');
        $product = StoreCombinationModel::get($id);
        if (!$product) return $this->fail('数据不存在!');
        if ($product['is_del']) return $this->fail('已删除!');
        $data['is_del'] = 1;
        if (!StoreCombinationModel::edit($data, $id))
            return $this->fail(StoreCombinationModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return $this->success('删除成功!');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if ($status == '' || $id == 0) return $this->fail('参数错误');
        StoreCombinationModel::where(['id' => $id])->update(['is_show' => $status]);
        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }

    /**拼团列表
     * @return mixed
     */
    public function combine_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['status', ''],
            ['data', ''],
        ], $this->request);
        $list = StorePink::systemPage($where);
        return $this->success($list);
    }

    /**拼团人列表
     * @return mixed
     */
    public function order_pink($id)
    {
        $StorePink = StorePink::getPinkUserOne($id);
        if (!$StorePink) return $this->fail('数据不存在!');
        $list = StorePink::getPinkMember($id);
        $list[] = $StorePink;
        return $this->success(compact('list'));
    }

}
