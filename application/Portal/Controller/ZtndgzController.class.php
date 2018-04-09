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
class ZtndgzController extends HomebaseController {
    //当前使用语言 常量  LANG_SET
    protected $pmzt_model;
    protected $changci_model;
    protected $pmgezi_model;
    protected $pmjilu_model;
    protected $gezi_model;
    function _initialize()
    {
        parent::_initialize();
        $this->pmzt_model = M("Pmzt");
        $this->changci_model = M("Changci");
        $this->pmgezi_model = M("Pmgezi");
        $this->pmjilu_model = M("Pmjilu");
        $this->gezi_model = M("Gezi");
    }
    //获取专题页面的信息
	public function index() {
        //专题id
        $id = I('get.id');
        //场次id
        $changci = I('get.cid');
        $where['l'] = LANG_SET;
        $where['id'] = $id;
        //获取专题信息
        $list = $this->pmzt_model
            ->where($where)
            ->field('id,tname,pics,zhaiyao')->find();
        //获取场次信息
        $data = $this->changci_model->where(array('cid'=>$list['id']))->select();
        foreach ($data as $key => $val){
            $data[$key]['map'] = $this->pmgezi_model->where(array('cid'=>$val['id']))->getField('sequence',true);
        }
        foreach ($data as $key =>$val){
            $arr = $val['map'];
            if(!empty($arr)){
                sort($arr);
                $data[$key]['min'] = $arr[0];
                $data[$key]['max'] = $arr[count($arr)-1];
            }
        }


        if(empty($changci)){
            $changci = $data['0']['id'];
        }
       //根据场次id获取该场次下面的所有鸽子信息
        $map['cid'] = $changci;
        $article = $this->pmgezi_model->where($map)->field('huanhao,cid,sequence,addtime')->select();
        foreach ($article as $k => $val){
            $gzmap = array('huanhao'=>$val['huanhao']);
            $huanhao = $val['huanhao'];
            $article[$k]['title'] = $this->gezi_model->where($gzmap)->getField('title');
            $article[$k]['gezi_sex'] = $this->gezi_model->where($gzmap)->getField('gezi_sex');
            //获取目录路径
            $path = './data/upload/default/tupian/';
            //取出该目录下所有的文件及目录
            $result = scandir($path);
            //删除目录
            array_splice($result,0,2);

            if(in_array($huanhao.'-pigeon.jpg',$result)){
                $article[$k]['pics'] = "$path"."$huanhao".'-pigeon.jpg';
            }
        }


        //专题信息
        $this->assign('list', $list);
        $this->assign('changci', $changci);
        $this->assign('id', $id);
        //场次信息
        $this->assign('data', $data);
        //鸽子信息
        $this->assign('article', $article);
    	$this->display(":ztndgz");
    }
}


