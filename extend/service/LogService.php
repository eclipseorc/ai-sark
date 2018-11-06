<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/13
 * Time: 18:49
 */
namespace service;

use think\Db;

class LogService
{
    /**
     * 指定当前操作模型
     * @return \think\db\Query
     */
    protected static function db()
    {
        return Db::name('SystemLog');
    }

    /**
     * 写入操作日志
     * @param string $action
     * @param string $content
     * @return bool
     */
    public static function write($action = '行为', $content = '内容描述')
    {
        $request    = app('request');
        $node       = strtolower(implode('/', [$request->module(), $request->controller(), $request->action()]));
        $data       = [
            'ip'        => getIP(),
            'node'      => $node,
            'action'    => $action,
            'content'   => $content,
            'username'  => session('user.username') . ''
        ];
        return self::db()->insert($data) !== false;
    }































}