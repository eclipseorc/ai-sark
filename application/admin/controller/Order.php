<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/25
 * Time: 20:18
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use think\Db;

class Order extends BasicAdmin
{
    /**
     * 指定当前操作模型
     * @var string
     */
    public $table = 'Order';

    /**
     * 订单列表页
     * @return array|mixed
     */
    public function index()
    {
        $this->title = '订单列表';
        $db          = Db::name($this->table);
        return parent::_list($db);
    }

    /**
     * 列表数据过滤
     * @param $data
     */
    protected function _index_data_filter(&$data)
    {
        foreach ($data as $key => &$value) {
            $value['money'] = number_format($value['money'] / 100, 2);
        }
    }
}