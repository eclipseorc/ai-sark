<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/23
 * Time: 21:08
 */
namespace validate;

class SarkRule
{
    /**
     * 验证规则
     * @var array
     */
    public static $addRules = [
        'sn'        => 'require',
        'own_id'    => 'require',
    ];

    /**
     * 提示信息
     * @var array
     */
    public static $addMessage = [
        'sn.require'    => '请输入柜子唯一序列号',
        'own_id.require'=> '请选择运营商',
    ];
}