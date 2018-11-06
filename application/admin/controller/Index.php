<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/19
 * Time: 20:51
 */
namespace app\admin\controller;

use service\NodeService;
use service\ToolsService;
use think\App;
use think\Controller;
use think\Db;

class Index extends Controller
{
    /**
     * 后台框架布局
     * @return mixed
     */
    public function index()
    {
        NodeService::applyAuthNode();
        $list = Db::name('SystemMenu')->where(['status' => '1'])->order('sort asc, id asc')->select();
        $menus= $this->buildMenuData(ToolsService::arr2tree($list), NodeService::get(), (boolean)session('user'));
        if (empty($menus) && !session('user.id')) {
            $this->redirect('@admin/login');
        }
        return $this->fetch('', ['title' => '系统管理', 'menus' => $menus]);
    }

    /**
     * 后台主菜单权限过滤
     * @param $menus
     * @param $nodes
     * @param $isLogin
     * @return mixed
     */
    private function buildMenuData($menus, $nodes, $isLogin)
    {
        foreach ($menus as $key => &$menu) {
            !empty($menu['sub']) && $menu['sub'] = $this->buildMenuData($menu['sub'], $nodes, $isLogin);
            if (!empty($menu['sub'])) {
                $menu['url']    = '#';
            } elseif (preg_match('/^https?\:/i', $menu['url'])) {
                continue;
            } elseif ($menu['url'] !== '#') {
                $node = implode('/', array_slice(explode('/', preg_replace('/[\W]/', '/', $menu['url'])), 0, 3));
                $menu['url']    = url($menu['url']) . (empty($menu['params']) ? '' : "?{$menu['params']}");
                if (isset($nodes[$node]) && $nodes[$node]['is_login'] && empty($isLogin)) {
                    unset($menus[$key]);
                } elseif (isset($nodes[$node]) && $nodes[$node]['is_auth'] && $isLogin && !auth($node)) {
                    unset($menus[$key]);
                }
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }

    public function main()
    {
        $version = Db::query('select version() as ver');
        return $this->fetch('', [
            'title' => '后台首页',
            'think_ver' => App::VERSION,
            'mysql_ver' => array_pop($version)['ver']
        ]);

    }
}