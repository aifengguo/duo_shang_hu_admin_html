<?php
/**
 * Created by PhpStorm.
 * User: lofate
 * Date: 2019/12/18
 * Time: 12:24
 */

namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use app\models\store\StoreDescription;
use app\models\store\StoreProductAttr;
use crmeb\services\UtilService as Util;
use crmeb\traits\CurdControllerTrait;
use app\models\store\StoreBargain as StoreBargainModel;

/**
 * 砍价管理
 * Class StoreBargain
 * @package app\adminapi\controller\v1\marketing
 */
class StoreBargain extends AuthController
{
    use CurdControllerTrait;

    protected $bindModel = StoreBargainModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            [['page', 'd'], 1],
            [['limit', 'd'], 20],
            ['status', ''],
            ['store_name', ''],
            ['export', 0],
            ['data', ''],
        ], $this->request);
        $list = StoreBargainModel::systemPage($where);
        return $this->success($list);
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save($id)
    {
        $data = Util::postMore([
            ['title', ''],
            ['info', ''],
            ['unit_name', ''],
            ['section_time', []],
            ['image', ''],
            ['images', []],
            ['bargain_max_price', 0],
            ['bargain_min_price', 0],
            ['sort', 0],
            ['give_integral', 0],
            ['is_hot', 0],
            ['status', 0],
            ['product_id', 0],
            ['description', ''],
            ['attrs', []],
            ['items', []],
            ['temp_id', 0],
            ['rule', ''],
            ['num', 1]
        ]);
        $this->validate($data, \app\adminapi\validates\marketing\StoreBargainValidate::class, 'save');
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        $data['images'] = json_encode($data['images']);
        $data['stock'] = $detail[0]['stock'];
        $data['quota'] = $detail[0]['quota'];
        $data['quota_show'] = $detail[0]['quota'];
        $data['price'] = $detail[0]['price'];
        $data['min_price'] = $detail[0]['min_price'];
        unset($data['section_time'], $data['description'], $data['attrs'], $data['items'], $detail[0]['min_price'], $detail[0]['_index'], $detail[0]['_rowKey']);
        if ($id) {
            $product = StoreBargainModel::get($id);
            if (!$product) return $this->fail('数据不存在!');
            StoreBargainModel::edit($data, $id);
            StoreProductAttr::createProductAttr($items, $detail, $id, 2);
            StoreDescription::saveDescription($description, $id, 2);
            return $this->success('修改成功');
        } else {
            $data['add_time'] = time();
            $bargain_id = StoreBargainModel::insertGetId($data);
            StoreProductAttr::createProductAttr($items, $detail, $bargain_id, 2);
            StoreDescription::saveDescription($description, $bargain_id, 2);
            return $this->success('添加成功');
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
        $info = StoreBargainModel::getOne($id);
        return $this->success(compact('info'));
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
        $product = StoreBargainModel::get($id);
        if (!$product) return $this->fail('数据不存在!');
        if ($product['is_del']) return $this->fail('已删除!');
        $data['is_del'] = 1;
        if (StoreBargainModel::edit($data, $id))
            return $this->success('删除成功!');
        else
            return $this->fail(StoreBargainModel::getErrorInfo('删除失败,请稍候再试!'));
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
        StoreBargainModel::where(['id' => $id])->update(['status' => $status]);
        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }
}