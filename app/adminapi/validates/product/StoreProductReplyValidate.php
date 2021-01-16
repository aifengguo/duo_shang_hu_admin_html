<?php

namespace app\adminapi\validates\product;

use think\Validate;

class StoreProductReplyValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'product_id' => 'require',
        'nickname' => 'require',
        'comment' => 'require',
        'avatar' => 'require',
        'product_score' => ['require', 'In:1,2,3,4,5'],
        'service_score' => ['require', 'In:1,2,3,4,5'],
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'product_id.require' => '请选择商品',
        'nickname.require' => '请填写用户昵称',
        'comment.require' => '请填写评论内容',
        'avatar.require' => '请选择用户头像',
        'product_score.require' => '请填写商品分数',
        'product_score.In:1,2,3,4,5' => '商品分数必须为1-5之间的整数',
        'service_score.require' => '请填写服务分数',
        'service_score.In:1,2,3,4,5' => '服务分数必须为1-5之间的整数',
    ];

    protected $scene = [
        'save' => ['product_id', 'nickname', 'comment', 'avatar', 'product_score', 'service_score'],
    ];
}