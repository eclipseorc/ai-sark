<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/6/26
 * Time: 18:00
 */
namespace service;

use cache\cacheKey;
use think\Db;

class NodeService
{
	/**
	 * 应用授权节点
	 * @return bool|mixed
	 */
	public static function applyAuthNode()
	{
		//清空缓存
		cache(cacheKey::NEED_ACCESS_NODE, null);
		if (($userid = session('user.id'))) {
			session('user', Db::name('SystemUser')->where(['id' => $userid])->find());
		}
		if (($authorize = session('user.authorize'))) {
			$where['status']	= 1;
			$where['id']		= ['in', $authorize];
			$authorizeIds       = Db::name('SystemAuth')->where($where)->column('id');
			if (empty($authorizeIds)) {
				return session('user.nodes', []);
			}
			$nodes = Db::name('SystemAuthNode')->where(['auth' => ['in', $authorizeIds]])->column('node');
			return session('user.nodes', $nodes);
		}
		return false;
	}

	/**
	 * 获取授权节点
	 * @return array|mixed
	 */
	public static function getAuthNode()
	{
	    //获取缓存
		$nodes = cache(cacheKey::NEED_ACCESS_NODE);
		if (empty($nodes)) {
			$nodes = Db::name('SystemNode')->where(['is_auth' => '1'])->column('node');
			cache(cacheKey::NEED_ACCESS_NODE, $nodes);
		}
		return $nodes;
	}

    /**
     * 检测用户节点权限
     * @param string $node 节点
     * @return bool
     */
	public static function checkAuthNode($node)
    {
        list($module, $controller, $action) = explode('/', str_replace(['?', '=', '&'], '/', $node . '///'));
        $currentNode = self::parseNodeStr("{$module}/{$controller}") . strtolower("/{$action}");
        if (session('user.username') === 'admin' || stripos($node, 'admin/index') === 0) {
            return true;
        }
        if (!in_array($currentNode, self::getAuthNode())) {
            return true;
        }
        return in_array($currentNode, (array)session('user.nodes'));
    }

    /**
     * 获取节点列表
     * @param string $dirPath   路径
     * @param array $nodes      额外数据
     * @return array
     */
	public static function getNodeTree($dirPath, $nodes = [])
    {
        foreach (self::scanDirFile($dirPath) as $filename) {
            $matches = [];
            if (!preg_match('|/(\w+)/controller/(\w+)|', str_replace(DIRECTORY_SEPARATOR, '/', $filename), $matches) || count($matches) !==3) {
                continue;
            }
            $className = env('app_namespace') . str_replace('/', '\\', $matches[0]);
            if (!class_exists($className)) {
                continue;
            }
            foreach (get_class_methods($className) as $funcName) {
                if (strpos($funcName, '_') !== 0 && $funcName !== 'initialize') {
                    $nodes[] = self::parseNodeStr("{$matches[1]}/{$matches[2]}") . '/' . strtolower($funcName);
                }
            }
        }
        return $nodes;
    }

    /**
     * 驼峰转下划线规则
     * @param $node
     * @return string
     */
    public static function parseNodeStr($node)
    {
        $tmp = [];
        foreach (explode('/', $node) as $name) {
            $tmp[] = strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
        return trim(implode('/', $tmp), '/');
    }

    /**
     * 获取所有php文件
     * @param string $dirPath   目录
     * @param array $data       额外数据
     * @param string $ext       文件后缀
     * @return array
     */
    public static function scanDirFile($dirPath, $data = [], $ext = 'php')
    {
        foreach (scandir($dirPath) as $dir) {
            if (strpos($dir, '.') === 0) {
                continue;
            }
            $tmpPath = realpath($dirPath . DIRECTORY_SEPARATOR . $dir);
            if (is_dir($tmpPath)) {
                $data = array_merge($data, self::scanDirFile($tmpPath));
            } elseif (pathinfo($tmpPath, 4) === $ext) {
                $data[] = $tmpPath;
            }
        }
        return $data;
    }

    /**
     * 获取系统节点代码
     * @param array $nodes
     * @return array
     */
	public static function get($nodes = [])
    {
        $list   = Db::name('SystemNode')->column('node, is_menu, is_auth, is_login, title');
        $ignore = ['index', 'admin/login', 'admin/index'];
        foreach (self::getNodeTree(env('app_path')) as $thr) {
            foreach ($ignore as $str) {
                if (strpos($thr, $str) === 0) {
                    continue 2;
                }
            }
            $tmp = explode('/', $thr);
            list($one, $two) = ["{$tmp[0]}", "{$tmp[0]}/{$tmp[1]}"];
            $nodes[$one]    = array_merge(isset($list[$one]) ? $list[$one] : ['node' => $one, 'title' => '', 'is_menu' => 0, 'is_auth' => 0, 'is_login' => 0], ['pnode' => '']);
            $nodes[$two]    = array_merge(isset($list[$two]) ? $list[$two] : ['node' => $two, 'title' => '', 'is_menu' => 0, 'is_auth' => 0, 'is_login' => 0], ['pnode' => $one]);
            $nodes[$thr]    = array_merge(isset($list[$thr]) ? $list[$thr] : ['node' => $thr, 'title' => '', 'is_menu' => 0, 'is_auth' => 0, 'is_login' => 0], ['pnode' => $two]);
        }
        foreach ($nodes as &$node) {
            $node['is_auth']    = intval($node['is_auth']);
            $node['is_menu']    = intval($node['is_menu']);
            $node['is_login']   = empty($node['is_auth']) ? intval($node['is_login']) : 1;
        }
        return $nodes;
    }
}