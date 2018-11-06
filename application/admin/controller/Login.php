<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/6/23
 * Time: 17:40
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\NodeService;
use think\Db;
use think\Validate;
use validate\LoginRule;

class Login extends BasicAdmin
{
	public function index()
	{
		if ($this->request->isGet()) {
			return $this->fetch('', ['title' => '用户登陆']);
		}

		//请求数据
		$requestData = [
			'username'	=> $this->request->post('username', ''),
			'password'	=> $this->request->post('password', '')
		];

		//输入数据校验
		$validate = Validate::make(LoginRule::$rules, LoginRule::$message);
		if (!$validate->check($requestData)) {
			$this->error($validate->getError());
		}

		$where		= [
			'username'		=> $requestData['username'],
			'is_deleted'	=> '0',
		];
		$userInfo = Db::name('SystemUser')->where($where)->find();
		if (empty($userInfo)) {
			$this->error('登陆账号不存在，请重新输入！');
		}
		if ($userInfo['status'] == '0') {
			$this->error('登陆账号已被禁用，请联系管理员！');
		}
		if ($userInfo['password'] !== md5($requestData['password'])) {
		    $this->error('登陆密码错误，请重新输入！');
        }

		//更新登陆信息
		$upData = [
			'ip'		=> getIP(),
			'login_at'	=> date('Y-m-d H:i:s', time()),
			'login_num'	=> $userInfo['login_num'] + 1
		];

		Db::name('SystemUser')->where(['id' => $userInfo['id']])->update($upData);
		if (!empty($userInfo['authorize'])) {
			NodeService::applyAuthNode();
		}
		session('user', $userInfo);
		LogService::write('系统管理', '用户登陆系统成功');
		$this->success('登陆成功，正在进入系统...', '@admin');
	}

    /**
     * 退出登陆
     */
	public function out()
    {
        session('user') && LogService::write('系统管理', '用户退出系统成功');
        !empty($_SESSION) && $_SESSION = [];
        session_unset();
        session_destroy();
        $this->success('退出登陆成功！', '@admin/login');
    }
}