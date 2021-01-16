<?php

namespace app\adminapi\controller\v1\merchant;

use app\models\system\SystemConfig;
use app\adminapi\controller\AuthController;
use app\models\system\{SystemStore as SystemStoreModel};
use crmeb\services\{UtilService as Util};

/**
 * 门店管理控制器
 * Class SystemAttachment
 * @package app\admin\controller\system
 *
 */
class SystemStore extends AuthController
{

    /**
     * 获取门店列表1
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            [['page', 'd'], 1],
            [['limit', 'd'], 15],
            [['keywords', 's'], ''],
            [['type', 'd'], 0],
        ]);
        return $this->success(SystemStoreModel::lst(0, 0, $where['page'], $where['limit'], $where['keywords'], $where['type']));
    }

    /**
     * 获取门店头部
     * @return mixed
     */
    public function get_header()
    {
        $count['show']['name'] = '显示中的提货点';
        $count['hide']['name'] = '隐藏中的提货点';
        $count['recycle']['name'] = '回收站的提货点';
        $count['show']['num'] = SystemStoreModel::where('is_show', 1)->where('is_del', 0)->count('id');//显示中的门店
        $count['hide']['num'] = SystemStoreModel::where('is_show', 0)->count('id');//隐藏的门店
        $count['recycle']['num'] = SystemStoreModel::where('is_del', 1)->count('id');//删除的门店
        return $this->success(compact('count'));
    }

    /*
     * 门店设置
     * */
    public function get_info()
    {
        list($id) = Util::getMore([
            [['id', 'd'], 0],
        ], $this->request, true);
        $info = SystemStoreModel::getStoreDispose($id);
        return $this->success(compact('info'));
    }

    /*
     * 位置选择
     * */
    public function select_address()
    {
        $key = SystemConfig::getConfigValue('tengxun_map_key');
        if (!$key) return $this->fail('请前往设置->系统设置->物流配置 配置腾讯地图KEY');
        return $this->success(compact('key'));
    }

    /**
     * 设置单个门店是否显示
     * @param string $is_show
     * @param string $id
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && $this->fail('缺少参数');
        $res = SystemStoreModel::where(['id' => $id])->update(['is_show' => (int)$is_show]);
        if ($res) {
            return $this->success($is_show == 1 ? '设置显示成功' : '设置隐藏成功');
        } else {
            return $this->fail($is_show == 1 ? '设置显示失败' : '设置隐藏失败');
        }
    }

    /*
     * 保存修改门店信息
     * param int $id
     * */
    public function save($id = 0)
    {
        $data = Util::postMore([
            ['name', ''],
            ['introduction', ''],
            ['image', ''],
            ['phone', ''],
            ['address', ''],
            ['detailed_address', ''],
            ['latlng', ''],
            ['day_time', []],
        ]);
        $this->validate($data, \app\adminapi\validates\merchant\SystemStoreValidate::class, 'save');
        SystemStoreModel::beginTrans();
        try {
            $data['address'] = implode(',', $data['address']);
            $data['latlng'] = explode(',', $data['latlng']);
            if (!isset($data['latlng'][0]) || !isset($data['latlng'][1])) return $this->fail('请选择门店位置');
            $data['latitude'] = $data['latlng'][0];
            $data['longitude'] = $data['latlng'][1];
            $data['day_time'] = implode(' - ', $data['day_time']);
            unset($data['latlng']);
            if ($data['image'] && strstr($data['image'], 'http') === false) {
                $site_url = SystemConfig::getConfigValue('site_url');
                $data['image'] = $site_url . $data['image'];
            }
            if ($id) {
                if (SystemStoreModel::where('id', $id)->update($data)) {
                    SystemStoreModel::commitTrans();
                    return $this->success('修改成功');
                } else {
                    SystemStoreModel::rollbackTrans();
                    return $this->fail('修改失败或者您没有修改什么！');
                }
            } else {
                $data['add_time'] = time();
                $data['is_show'] = 1;
                if ($res = SystemStoreModel::create($data)) {
                    SystemStoreModel::commitTrans();
                    return $this->success('保存成功', ['id' => $res->id]);
                } else {
                    SystemStoreModel::rollbackTrans();
                    return $this->fail('保存失败！');
                }
            }
        } catch (\Exception $e) {
            SystemStoreModel::rollbackTrans();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除恢复门店
     * @param $id
     */
    public function delete($id)
    {
        if (!$id) return $this->fail('数据不存在');
        if (!SystemStoreModel::be(['id' => $id])) return $this->fail('数据不存在');
        if (SystemStoreModel::be(['id' => $id, 'is_del' => 1])) {
            $data['is_del'] = 0;
            if (!SystemStoreModel::edit($data, $id))
                return $this->fail(SystemStoreModel::getErrorInfo('恢复失败,请稍候再试!'));
            else
                return $this->success('恢复门店成功!');
        } else {
            $data['is_del'] = 1;
            if (!SystemStoreModel::edit($data, $id))
                return $this->fail(SystemStoreModel::getErrorInfo('删除失败,请稍候再试!'));
            else
                return $this->success('删除门店成功!');
        }
    }
}