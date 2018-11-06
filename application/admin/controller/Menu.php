<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/13
 * Time: 11:16
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;

class Menu extends BasicAdmin
{
    /**
     * 绑定操作模型
     * @return array|mixed
     */
    public $table = 'SystemMenu';

    public function index()
    {
        $this->title = '后台菜单管理';
        $db = Db::name($this->table)->order('sort asc, id asc');
        return parent::_list($db, false);
    }

    /**
     * @param $data
     */
    protected function _index_data_filter(&$data)
    {
        foreach ($data as &$vo) {
            if ($vo['url'] != '#') {
                $vo['url']  = url($vo['url']) . (empty($vo['params']) ? '' : "?{$vo['params']}");
            }
            $vo['ids']      = join(',', ToolsService::getArrSubIds($data, $vo['id']));
        }
        $data = ToolsService::arr2table($data);
    }

    /**
     * 添加菜单
     * @return array|mixed
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 表单数据前缀方法
     * @param $vo
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isGet()) {
            //上级菜单处理
            $_menus     = Db::name($this->table)->where(['status' => '1'])->order('sort asc, id asc')->select();
            $_menus[]   = ['title' => '顶级菜单', 'id' => '0', 'pid' => '-1'];
            $menus      = ToolsService::arr2table($_menus);
            foreach ($menus as $key => &$menu) {
                if (substr_count($menu['path'], '-') > 3) {
                    unset($menus[$key]);
                    continue;
                }
                if (isset($vo['pid'])) {
                    $currentPath = "-{$vo['pid']}-{$vo['id']}";
                    if ($vo['pid'] !== '' && (strpos("{$menu['path']}-", "{$currentPath}-") !== false || $menu['path'] === $currentPath)) {
                        unset($menus[$key]);
                        continue;
                    }
                }
            }
            //读取系统节点
            $nodes = NodeService::get();
            foreach ($nodes as $key => $node) {
                if (empty($node['is_menu'])) {
                    unset($nodes[$key]);
                }
            }
            //设置上级菜单
            if (!isset($vo['pid']) && $this->request->get('pid', '0')) {
                $vo['pid']  = $this->request->get('pid', '0');
            }
            $this->assign(['nodes' => array_column($nodes, 'node'), 'menus' => $menus]);
        }
    }

    /**
     * 删除菜单
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success('菜单删除成功', '');
        }
        $this->error('菜单删除失败');
    }

    /**
     * 菜单禁用
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success('菜单禁用成功！', '');
        }
        $this->error('菜单禁用失败，请稍后再试');
    }

    /**
     * 菜单启用
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success('菜单启用成功！', '');
        }
        $this->error('菜单启动失败，请稍后再试！');
    }
}