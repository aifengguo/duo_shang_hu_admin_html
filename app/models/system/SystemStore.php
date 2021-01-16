<?php


namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 门店自提 model
 * Class SystemStore
 * @package app\model\system
 */
class SystemStore extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_store';


    public static function getLatlngAttr($value, $data)
    {
        return $data['latitude'] . ',' . $data['longitude'];
    }

    public static function verificWhere()
    {
        return self::where('is_show', 1)->where('is_del', 0);
    }

    /**
     * 获取门店信息
     * @param int $id
     * @param string $felid
     * @return array|mixed|null|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getStoreDispose($id = 0, $felid = '')
    {
        if ($id)
            $storeInfo = self::verificWhere()->where('id', $id)->find();
        else
            $storeInfo = self::verificWhere()->find();
        if ($storeInfo) {
            $storeInfo['latlng'] = self::getLatlngAttr(null, $storeInfo);
            $storeInfo['dataVal'] = $storeInfo['valid_time'] ? explode(' - ', $storeInfo['valid_time']) : [];
            $storeInfo['timeVal'] = $storeInfo['day_time'] ? explode(' - ', $storeInfo['day_time']) : [];
            $storeInfo['address2'] = $storeInfo['address'] ? explode(',', $storeInfo['address']) : [];
            if ($felid) return $storeInfo[$felid] ?? '';
        }
        return $storeInfo;
    }

    /**
     * 获取排序sql
     * @param $latitude
     * @param $longitude
     * @return mixed
     */
    public static function distanceSql($latitude, $longitude)
    {
        $field = "(round(6367000 * 2 * asin(sqrt(pow(sin(((latitude * pi()) / 180 - ({$latitude} * pi()) / 180) / 2), 2) + cos(({$latitude} * pi()) / 180) * cos((latitude * pi()) / 180) * pow(sin(((longitude * pi()) / 180 - ({$longitude} * pi()) / 180) / 2), 2))))) AS distance";
        return $field;
    }

    /**
     * 门店列表
     * @return mixed
     */
    public static function lst($latitude, $longitude, $page, $limit, $keywords = '', $type = 0)
    {
        $model = new self();
        if ($type == 0) {
            $model = $model->where(['is_del' => 0, 'is_show' => 1]);
        } elseif ($type == 1) {
            $model = $model->where('is_show', 0);
        } else {
            $model = $model->where('is_del', 1);
        }
        if (isset($keywords) && $keywords != '') {
            $model = $model->where('id|name|introduction|phone', 'like', '%' . $keywords . '%');
        }
        if ($latitude && $longitude) {
            $model = $model->field(['*', self::distanceSql($latitude, $longitude)])->order('distance asc');
        }
        $count = $model->count('id');
        $list = $model->page((int)$page, (int)$limit)->select()->toArray();
        if ($latitude && $longitude) {
            foreach ($list as &$value) {
                //计算距离
                $value['distance'] = sqrt((pow((($latitude - $value['latitude']) * 111000), 2)) + (pow((($longitude - $value['longitude']) * 111000), 2)));
                //转换单位
                $value['range'] = bcdiv($value['distance'], 1000, 1);
            }
        }
        return compact('list', 'count');
    }

    /**
     * 导出数据
     * @return mixed
     */
    public static function exportData($where)
    {
        $model = new self();
        if ($where['type'] == 0) {
            $model = $model->where(['is_del' => 0, 'is_show' => 1]);
        } elseif ($where['type'] == 1) {
            $model = $model->where('is_show', 0);
        } else {
            $model = $model->where('is_del', 1);
        }
        if (isset($where['keywords']) && $where['keywords'] != '') {
            $model = $model->where('id|name|introduction', 'like', '%' . $where['keywords'] . '%');
        }
        $list = $model->select()->toArray();

        return $list;
    }

    /**
     * 店员添加门店列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function dropList()
    {
        return self::where(['is_show' => 1, 'is_del' => 0])->select()->toArray();
    }

}