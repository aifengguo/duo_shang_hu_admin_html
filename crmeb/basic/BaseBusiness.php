<?php
/**
 * @author: liaofei<136327134@qq.com>
 * @day: 2020/5/26
 */

namespace crmeb\basic;

/**
 *
 * Class BaseBusiness
 * @package crmeb\basic
 */
abstract class BaseBusiness
{
    /**
     * 模型
     * @var \think\Model
     */
    protected $model;

    /**
     * 主键
     * @var string
     */
    protected $pk;

    /**
     * 设置主键
     * @param string $pk
     * @return $this
     */
    public function pk(string $pk)
    {
        $this->pk = $pk;
        return $this;
    }

    /**
     * 修改某个key值
     * @param $id
     * @param string $field
     * @param $value
     * @return static
     */
    public function updateFiled($id, string $field, $value)
    {
        return $this->model->where($this->getPk(), $id)->update([$field => $value]);
    }

    /**
     * 获取主键
     * @return $this
     */
    protected function getPk()
    {
        return $this->pk ?: $this->model->getPk();
    }
}