<?php

namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use think\facade\Route as Url;
use crmeb\services\{CacheService, FormBuilder as Form, UtilService as Util};
use app\models\system\SystemCity as CityModel;

class SystemCity extends AuthController
{
    /**
     * 城市列表
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $params = Util::getMore([
            [['parent_id','d'], 0]
        ], $this->request);
        return $this->success(CityModel::getList($params));
    }

    /**
     * 添加城市
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $data = Util::getMore([
            [['parent_id','d'], 0]
        ]);
        if ($data['parent_id'] != 0) {
            $info = CityModel::where('city_id', $data['parent_id'])->find()->toArray();
        } else {
            $info = [
                "level" => 0,
                "city_id" => 0,
                "name" => '中国'
            ];
        }
        $field[] = Form::hidden('level', $info['level']);
        $field[] = Form::hidden('parent_id', $info['city_id']);
        $field[] = Form::input('parent_name', '上级名称', $info['name'])->readonly(true);
        $field[] = Form::input('name', '名称')->required('请填写城市名称');
//        $field[] = Form::input('merger_name', '合并名称')->placeholder('格式:陕西,西安,雁塔')->required('请填写合并名称');
//        $field[] = Form::input('area_code', '区号');
//        $field[] = Form::input('lng', '经度');
//        $field[] = Form::input('lat', '纬度');
        return $this->makePostForm('添加城市', $field, Url::buildUrl('/setting/city/save')->suffix(false));
    }

    /**
     * 保存
     */
    public function save()
    {
        $data = Util::postMore([
            [['id','d'], 0],
            [['name','s'], ''],
            [['merger_name','s'], ''],
            [['area_code','s'], ''],
            [['lng','s'], ''],
            [['lat','s'], ''],
            [['level','d'], 0],
            [['parent_id','d'], 0],
        ]);
        $this->validate($data, \app\adminapi\validates\setting\SystemCityValidate::class, 'save');
        if($data['parent_id'] == 0){
            $data['merger_name'] = $data['name'];
        } else {
            $data['merger_name'] = CityModel::where('id',$data['parent_id'])->value('name').','.$data['name'];
        }
        if ($data['id'] == 0) {
            unset($data['id']);
            $data['level'] = $data['level'] + 1;
            $data['city_id'] = intval(CityModel::max('city_id') + 1);
            CityModel::create($data);
            return $this->success('添加城市成功!');
        } else {
            unset($data['level']);
            unset($data['parent_id']);
            CityModel::where('id', $data['id'])->update($data);
            return $this->success('修改城市成功!');
        }
    }

    /**
     * 修改城市
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $data = Util::getMore([
            [['id','d'], 0]
        ]);
        $info = CityModel::get($data['id'])->toArray();
        $info['parent_name'] = CityModel::where('city_id', $info['parent_id'])->value('name') ?: '中国';
        $field[] = Form::hidden('id', $info['id']);
        $field[] = Form::hidden('level', $info['level']);
        $field[] = Form::hidden('parent_id', $info['parent_id']);
        $field[] = Form::input('parent_name', '上级名称', $info['parent_name'])->readonly(true);
        $field[] = Form::input('name', '名称', $info['name'])->required('请填写城市名称');
        $field[] = Form::input('merger_name', '合并名称', $info['merger_name'])->placeholder('格式:陕西,西安,雁塔')->required('请填写合并名称');
//        $field[] = Form::input('area_code', '区号', $info['area_code']);
//        $field[] = Form::input('lng', '经度', $info['lng']);
//        $field[] = Form::input('lat', '纬度', $info['lat']);
        return $this->makePostForm('修改城市', $field, Url::buildUrl('/setting/city/save')->suffix(false));
    }

    /**
     * 删除城市
     * @throws \Exception
     */
    public function delete()
    {
        $data = Util::getMore([
            [['city_id','d'], 0]
        ]);
        CityModel::where('city_id', $data['city_id'])->whereOr('parent_id', $data['city_id'])->delete();
        return $this->success('删除成功!');
    }
    
    /**
     * 清除城市缓存
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function clean_cache()
    {
        $res = CacheService::delete('CITY_LIST');
        if ($res) {
            return $this->success('清除成功!');
        } else {
            return $this->fail('清除失败或缓存未生成!');
        }
    }
}