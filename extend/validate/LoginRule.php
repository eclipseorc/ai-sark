<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/6/26
 * Time: 17:08
 */
namespace validate;

class LoginRule
{
	/**
	 * 验证规则
	 * @var array
	 */
	public static $rules = [
		'username'	=> 'require|min:4|max:25',
		'password'	=> 'require|min:4|max:6'
	];

	/**
	 * 提示信息
	 * @var array
	 */
	public static $message = [
		'username.require'	=> '登录账号不能为空！',
		'username.min'		=> '登录账号长度不能少于4位有效字符！',
		'username.max'		=> '登录账号长度不能大于25位有效字符！',
		'password.require'	=> '登录密码不能为空！',
		'password.between'	=> '登录密码长度保持在6-25位有效字符'
	];
}