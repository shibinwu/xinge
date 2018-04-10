<?php
/*
 *      _______ _     _       _     _____ __  __ ______
 *     |__   __| |   (_)     | |   / ____|  \/  |  ____|
 *        | |  | |__  _ _ __ | | _| |    | \  / | |__
 *        | |  | '_ \| | '_ \| |/ / |    | |\/| |  __|
 *        | |  | | | | | | | |   <| |____| |  | | |
 *        |_|  |_| |_|_|_| |_|_|\_\\_____|_|  |_|_|
 */
/*
 *     _________  ___  ___  ___  ________   ___  __    ________  _____ ______   ________
 *    |\___   ___\\  \|\  \|\  \|\   ___  \|\  \|\  \ |\   ____\|\   _ \  _   \|\  _____\
 *    \|___ \  \_\ \  \\\  \ \  \ \  \\ \  \ \  \/  /|\ \  \___|\ \  \\\__\ \  \ \  \__/
 *         \ \  \ \ \   __  \ \  \ \  \\ \  \ \   ___  \ \  \    \ \  \\|__| \  \ \   __\
 *          \ \  \ \ \  \ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \____\ \  \    \ \  \ \  \_|
 *           \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\    \ \__\ \__\
 *            \|__|  \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__|     \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class DzgzxxController extends HomebaseController {
    //当前使用语言 常量  LANG_SET
    protected $gezi_model;
    protected $pmgezi_model;
    protected $xuetongshu_model;
    function _initialize()
    {
        parent::_initialize();
        $this->gezi_model = M("Gezi");
        $this->pmgezi_model = M("Pmgezi");
        $this->xuetongshu_model = M("Xuetongshu");
    }
    //获取鸽子页面的信息
	public function index() {
        //鸽子环号
        $huanhao = I('get.id');
        $where = array();
        $where['l'] = LANG_SET;
        $where['huanhao'] = $huanhao;
        $data = $this->gezi_model->where($where)->find();
        //获取目录路径
        $path = './data/upload/default/tupian/';
        //取出该目录下所有的文件及目录
        $result = scandir($path);
        //删除目录
        array_splice($result,0,2);
        if(in_array($huanhao.'-pigeon.jpg',$result)){
            $data['pic'] = "$path"."$huanhao".'-pigeon.jpg';
        }
        if(in_array($huanhao.'-eye.jpg',$result)){
            $data['yjpic'] = "$path"."$huanhao".'-eye.jpg';
        }
        if(in_array($huanhao.'-ancestry.jpg',$result)){
            $data['xtpic'] = "$path"."$huanhao".'-ancestry.jpg';
        }
        $data['info'] = $this->xuetongshu_model->where($where)->getField('info');

        $list = $this->pmgezi_model->where($where)->find();
        $this->assign('data', $data);
        $this->assign('list', $list);
    	$this->display(":dzgzxx");
    }
}


