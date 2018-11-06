<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/13
 * Time: 16:53
 */
namespace app\admin\controller;

use controller\BasicAdmin;

class Plugs extends BasicAdmin
{
    /**
     * 字体图表选择器
     * @return mixed
     */
    public function icon()
    {
        $field = $this->request->get('field', 'icon');
        return $this->fetch('', ['field' => $field]);
    }
}