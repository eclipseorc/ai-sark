<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/22
 * Time: 19:47
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;
use think\Validate;
use validate\SarkRule;

class Sark extends BasicAdmin
{
    /**
     * 指定当前操作模型
     * @var string
     */
    public $table = 'Sark';

    /**
     * 智能柜管理
     * @return array|mixed
     */
    public function index()
    {
        $this->title = '智能柜管理';
        $db          = Db::name($this->table);
        return parent::_list($db);
    }

    /**
     * 添加按钮
     * @return array|mixed
     */
    public function add()
    {
        $this->title = '添加智能柜';

        // 获取运行商列表
        $carrierList = Db::name('SystemUser')->where(['status' => 1])->select();
        if (empty($carrierList)) {
            $this->error('请先添加运营商后再添加柜子！');
        }
        $this->assign('carrierList', $carrierList);

        // 获取开门方式
        $openTypeList = Db::name('OpenType')->where(['status' => 1])->select();
        if (empty($openTypeList)) {
            $this->error('请先添加柜子开门方式后再添加柜子！');
        }
        $this->assign('openTypeList', $openTypeList);

        if ($this->request->isPost()) {

            // 请求数据
            $requestData = [
                'sn'        => $this->request->post('sn', '', 'string'),
                'own_id'    => $this->request->post('own_id', 0, 'int'),
                'own_name'  => $this->request->post('own_name', '', 'string'),
                'name'      => $this->request->post('name', '', 'string'),
                'address'   => $this->request->post('address', '', 'string'),
                'big'       => $this->request->post('big', 0, 'int'),
                'small'     => $this->request->post('small', 0, 'int'),
                'remark'    => $this->request->post('remark', '', 'string')
            ];

            // 输入数据校验
            $validate = Validate::make(SarkRule::$addRules, SarkRule::$addMessage);
            if (!$validate->check($requestData)) {
                $this->error($validate->getError());
            }

            if ($requestData['big'] <= 0 && $requestData['small'] <= 0) {
                $this->error('请至少添加一种大箱或者小箱！');
            }

            // 添加货柜
            $flag = true;
            Db::startTrans();
            $res = Db::name('sark')->insert($requestData);
            if ($res !== false) {
                // 添加箱子
                $j  = 1;
                if ($requestData['big'] > 0) {
                    $bigBox = [];
                    for ($i = 1; $i <= $requestData['big']; $i++) {
                        $bigBox[$i]['own_id']   = $requestData['own_id'];
                        $bigBox[$i]['own_name'] = $requestData['own_name'];
                        $bigBox[$i]['name']     = $requestData['name'];
                        $bigBox[$i]['sn']       = $requestData['sn'];
                        $bigBox[$i]['size']     = 1;
                        $bigBox[$i]['box_id']   = $j++;
                    }
                    if (!empty($bigBox)) {
                        $bigBoxRes = Db::name('box')->insertAll($bigBox);
                        if ($bigBoxRes === false) {
                            $flag = false;
                        }
                    }
                }

                if ($requestData['small'] > 0) {
                    $smallBox = [];
                    for ($i = 1; $i <= $requestData['small']; $i++) {
                        $smallBox[$i]['own_id']     = $requestData['own_id'];
                        $smallBox[$i]['own_name']   = $requestData['own_name'];
                        $smallBox[$i]['name']       = $requestData['name'];
                        $smallBox[$i]['sn']         = $requestData['sn'];
                        $smallBox[$i]['size']       = 2;
                        $smallBox[$i]['box_id']     = $j++;
                    }
                    if (!empty($smallBox)) {
                        $smallBoxRes = Db::name('box')->insertAll($smallBox);
                        if ($smallBoxRes === false) {
                            $flag = false;
                        }
                    }
                }
            }
            if ($flag) {
                Db::commit();
                $this->success('柜子添加成功！', '');
            } else {
                Db::rollback();
                $this->error('柜子添加失败！');
            }
        }

        return $this->fetch('form', [
            'title' => $this->title,
        ]);
    }

    /**
     * 编辑按钮
     * @return array|mixed
     */
    public function edit()
    {
        $this->title = '编辑智能柜';
        return parent::_form($this->table, 'form');
    }

    /**
     * 删除按钮
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success('柜子删除成功！', '');
        }
        $this->error('柜子删除失败，请稍后再试！');
    }

    /**
     * 禁用按钮
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("柜子禁用成功！", '');
        }
        $this->error("柜子禁用失败，请稍候再试！");
    }

    /**
     * 启用按钮
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("柜子启用成功！", '');
        }
        $this->error("柜子启用失败，请稍候再试！");
    }

    public function boxList()
    {
        $this->title = '箱子列表';
        $db          = Db::name('Box');
        return parent::_list($db);
    }

    public function openType()
    {
        $this->title = '柜子开门方式列表';
        $db          = Db::name('OpenType');

        return parent::_list($db);
    }

    /**
     * 添加开门方式
     * @return mixed
     */
    public function addOpenType()
    {
        $this->title = '添加开门方式';
        $db          = Db::name('OpenType');
        return parent::_form($db, 'add_open_type_form');
    }
}