<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/13
 * Time: 14:34
 */
namespace app\admin\middleware;

use service\NodeService;
use think\Db;
use think\Request;

class Auth
{
    /**
     * 中间件入口函数：检查是否授权
     * @param Request $request
     * @param \Closure $next
     * @return \think\response\Json|\think\response\Redirect
     */
    public function handle($request, \Closure $next)
    {
        list($module, $controller, $action) = [$request->module(), $request->controller(), $request->action()];
        $access = $this->buildAuth($node = NodeService::parseNodeStr("{$module}/{$controller}/{$action}"));
        //登陆状态检查
        if (!empty($access['is_login']) && !session('user')) {
            $msg = ['code' => 0, 'msg' => '抱歉，您还没有登陆获取访问权限！', 'url' => url('@admin/login')];
            return $request->isAjax() ? json($msg) : redirect($msg['url']);
        }
        //访问权限检查
        if (!empty($access['is_auth']) && !auth($node)) {
            return json(['code' => 0, 'msg' => '抱歉，您没有访问该模块的权限！']);
        }
        //模板常量声明
        app('view')->init(config('template.'))->assign(['classuri' => NodeService::parseNodeStr("{$module}/{$controller}")]);
        return $next($request);
    }

    /**
     * 根绝节点获取对应的权限配置
     * @param string $node 权限节点
     * @return array
     */
    private function buildAuth($node)
    {
        $info = Db::name('SystemNode')->cache(true, 30)->where(['node' => $node])->find();
        return [
            'is_menu' => intval(!empty($info['is_menu'])),
            'is_auth' => intval(!empty($info['is_auth'])),
            'is_login'=> empty($info['is_auth']) ? intval(!empty($info['is_login'])) : 1
        ];
    }
}