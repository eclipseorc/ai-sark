<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use think\Db;
use service\DataService;
use service\NodeService;

/**
 * 获取系统配置
 * @param string $name  参数名称
 * @param null $value   参数值
 * @return bool|mixed|string
 */
function sysconf($name, $value = null)
{
    static $config = [];
    if ($value !== null) {
        list($config, $data) = [[], ['name' => $name, 'value' => $value]];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        $config = Db::name('SystemConfig')->column('name, value');
    }
    return isset($config['name']) ? $config['name'] : '';
}

/**
 * RBAC节点权限验证
 * @param $node
 * @return bool
 */
function auth($node)
{
    return NodeService::checkAuthNode($node);
}

/**
 * 日期格式标准输出
 * @param string $datetime  输入日期格式
 * @param string $format    输出日期格式
 * @return bool|string
 */
function format_datetime($datetime, $format = 'Y年m月d日 H:i:s')
{
    return date($format, strtotime($datetime));
}

/**
 * 获取用户ip地址
 * @param int $type
 * @return mixed
 */
function getIP($type = 0)
{
    $type    = $type ? 1 : 0;
    static $ip =  NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr  =  explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos  =  array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip   =  trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip   =  $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip   =  $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = ip2long($ip);
    $ip  = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}