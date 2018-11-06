<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/6/23
 * Time: 18:36
 */
namespace service;

use think\Db;

class DataService
{
	/**
	 * 数据增量保存
	 * @param - Query|string $dbQuery 数据查询对象
	 * @param $data
	 * @param string $key
	 * @param array $where
	 * @return bool
	 */
	public static function save($dbQuery, $data, $key = 'id', $where = [])
	{
		$db = is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery;
		list($table, $map) = [$db->getTable(), [$key => isset($data[$key]) ? $data[$key] : '']];
		if (Db::table($table)->where($where)->where($map)->count() > 0) {
			return Db::table($table)->where($where)->where($map)->update($data) !== false;
		}
		return Db::table($table)->insert($data) !== false;
	}

    /**
     * 更新数据表内容
     * @param resource|string $dbQuery 数据库查询对象
     * @param array $where      查询条件
     * @return bool
     */
	public static function update(&$dbQuery, $where = [])
    {
        $request    = app('request');
        $db         = is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery;
        $pk         = $db->getPk();
        $table      = $db->getTable();
        $map        = [];
        $field      = $request->post('field', '');
        $value      = $request->post('value', '');
        $map[]      = [empty($pk) ? 'id' : $pk, 'in', explode(',', $request->post('id', ''))];
        //删除模式，如果存在is_deleted字段使用软删除
        if ($field === 'delete') {
            if (method_exists($db, 'getTableFields') && in_array('is_deleted', $db->getTableFields())) {
                return Db::table($table)->where($where)->where($map)->update(['is_deleted' => '1']) !== false;
            }
            return Db::table($table)->where($where)->where($map)->delete() !== false;
        }
        //更新模式，更新指定字段
        return Db::table($table)->where($where)->where($map)->update([$field => $value]) !== false;
    }
}