<?php
/**
 * Created by PhpStorm.
 * User: lofate
 * Date: 2019/12/19
 * Time: 16:30
 */

namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use app\models\store\StoreDescription;
use app\models\store\StoreProductAttr;
use app\models\system\SystemGroupData;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\UtilService as Util;
use app\models\store\StoreSeckill as StoreSeckillModel;

/**
 * 限时秒杀  控制器
 * Class StoreSeckill
 * @package app\admin\controller\store
 */
class StoreSeckill extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = StoreSeckillModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            [['page','d'], 1],
            [['limit','d'], 20],
            [['status','s'], ''],
            [['store_name','s'], '']
        ]);
        $seckillList = StoreSeckillModel::systemPage($where);
        if (is_object($seckillList['list'])) $seckillList['list'] = $seckillList['list']->toArray();
        $data = $seckillList['list']['data'];
        foreach ($data as $k => $v) {
            $end_time = $v['stop_time'] ? date('Y/m/d', $v['stop_time']) : '';
            if ($end_time) {
                $config = SystemGroupData::get($v['time_id']);
                if ($config) {
                    $arr = json_decode($config->value, true);
                    $start_hour = $arr['time']['value'];
                    $continued = $arr['continued']['value'];
                    $end_hour = $start_hour + $continued;
                    $end_time = $end_time . ' ' . $end_hour . ':00:00';
                }
            }
            $data[$k]['_stop_time'] = $end_time;
        }
        $count = $seckillList['list']['total'];
        $list = $data;
        return $this->success(compact('count', 'list'));
    }

    /**
     * 详情
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        return $this->success(StoreSeckillModel::getOne($id));
    }

    /**
     * 保存秒杀商品
     * @param int $id
     */
    public function save($id)
    {
        $data = Util::postMore([
            [['product_id', 'd'], 0],
            [['title', 's'], ''],
            [['info', 's'], ''],
            [['unit_name', 's'], ''],
            ['image', ''],
            ['images', []],
            [['give_integral', 'd'], 0],
            ['section_time', []],
            [['is_hot', 'd'], 0],
            [['status', 'd'], 0],
            [['num', 'd'], 0],
            [['time_id', 'd'], 0],
            [['temp_id', 'd'], 0],
            [['sort', 'd'], 0],
            [['description', 's'], ''],
            ['attrs', []],
            ['items', []],
        ]);
        $this->validate($data, \app\adminapi\validates\marketing\StoreSeckillValidate::class, 'save');
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        $data['images'] = json_encode($data['images']);
        $data['price'] = min(array_column($detail, 'price'));
        $data['ot_price'] = min(array_column($detail, 'ot_price'));
        $data['quota'] = $data['quota_show'] = array_sum(array_column($detail, 'quota'));
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        unset($data['section_time'], $data['description'], $data['attrs'], $data['items']);
        if ($id) {
            $product = StoreSeckillModel::get($id);
            if (!$product) return $this->fail('数据不存在!');
            StoreSeckillModel::edit($data, $id);
            StoreProductAttr::createProductAttr($items, $detail, $id, 1);
            StoreDescription::saveDescription($description, $id, 1);
            return $this->success('编辑成功!');
        } else {
            $data['add_time'] = time();
            $seckill_id = StoreSeckillModel::insertGetId($data);
            StoreProductAttr::createProductAttr($items, $detail, $seckill_id, 1);
            StoreDescription::saveDescription($description, $seckill_id, 1);
            return $this->success('添加成功!');
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
        $product = StoreSeckillModel::get($id);
        if (!$product) return $this->fail('数据不存在!');
        if ($product['is_del']) return $this->fail('已删除!');
        $data['is_del'] = 1;
        if (!StoreSeckillModel::edit($data, $id))
            return $this->fail(StoreSeckillModel::getErrorInfo('删除失败,请稍候再试!'));
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
        StoreSeckillModel::where(['id' => $id])->update(['status' => $status]);
        return $this->success($status == 0 ? '关闭成功' : '开启成功');
    }

    /**
     * 秒杀时间段列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function time_list()
    {
        $list = SystemGroupData::getGroupData('routine_seckill_time');
        return $this->success(compact('list'));
    }
}
