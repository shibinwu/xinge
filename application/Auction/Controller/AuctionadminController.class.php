<?php
namespace Auction\Controller;

use Common\Controller\AdminbaseController;
ini_set('memory_limit','100M');
class AuctionadminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET 
    protected $pmzt_model;
    protected $pmproduct_model;
    protected $member_model;
    protected $pmjilu_model;
    protected $pmorder_model;
    protected $user_model;
    protected $product_model;
    protected $zhanshou_model;
    protected $changci_model;
    protected $gezi_model;
    protected $xuetongshu_model;
    protected $pmgezi_model;

    function _initialize()
    {
        parent::_initialize();
        $this->pmzt_model = M("Pmzt");
        $this->pmproduct_model = M("Pmproduct");
        $this->member_model = M("Members");
        $this->pmjilu_model = M("Pmjilu");
        $this->pmorder_model = M("Pmorder");
        $this->user_model = M("Users");
        $this->product_model = M("Product");
        $this->zhanshou_model = M("Zhanshou");
        $this->changci_model = M("Changci");
        $this->gezi_model = M("Gezi");
        $this->xuetongshu_model = M("Xuetongshu");
        $this->pmgezi_model = M("Pmgezi");
    }

    // 后台拍卖专题列表
    public function auctionindex()
    {
        $where = array();
        $request = I('request.');
        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['cn_show'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['tname'] = array('like', "%$keyword%");
        }

        $where['l'] = LANG_SET;
        $count = $this->pmzt_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->pmzt_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->field('id,adduser,seq,tname,start_time,end_time,cn_show,tuijian,addtime')
            ->select();
        foreach ($list as $key =>$val){
            $gezicount = 0;
            $arr =  $this->changci_model->where(array('cid' => $val['id']))->field('id')->select();
            foreach ($arr as $k => $vo){
                $gezinum = $this->pmgezi_model->where(array('cid' => $vo['id']))->count();
                $gezicount += $gezinum;
            }
            $list[$key]['gezicount'] = $gezicount;
        }

        foreach ($list as $k => $val) {
//            $list[$k]['names'] = $this->user_model->where(array('id' => $val['adduser']))->getField('user_nicename');
//            $list[$k]['nums'] = $this->pmproduct_model->where(array('cid' => $val['id']))->count();
            $list[$k]['num'] = $this->changci_model->where(array('cid' => $val['id']))->count();
//            $list[$k]['num'] = $this->pmgezi_model->where(array('cid' => $val['id']))->count();
            $nums= $this->pmjilu_model->where(array('cid' => $val['id']))->getField('pmprice',true);
            $list[$k]['totals']=array_sum($nums);

        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台拍卖专题添加
    public function auctionadd()
    {
        if (IS_POST) {
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['adduser'] = get_current_admin_id();
            $_POST['post']['country'] = implode(",",array_filter(array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3'])));
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->pmzt_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }

    // 后台拍卖专题编辑
    public function auctionedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['country'] = implode(",",array_filter(array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3'])));
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->pmzt_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this->pmzt_model->where($where)->find();
        $a = $info['country'];
        $arr = explode(",",$a);
        foreach ($arr as $k => $val) {
           if($val == 1){
               $info['country1'] =1;
           }elseif($val == 2){
               $info['country2'] =2;
           }else{
               $info['country3'] =3;
           }
        }
        $this->assign('post', $info);
        $this->display();
    }

    // 后台展售专题删除
    public function auctiondelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->pmzt_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->pmzt_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台拍卖场次列表
    public function changciindex()
    {
        $where = array();
        $request = I('request.');
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['name'] = array('like', "%$keyword%");
        }
        $changci = I('get.');
        $id = I('get.id');
        $where['cid'] = $id;
        $where['l'] = LANG_SET;
        $count = $this->changci_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->changci_model
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['num'] = $this->pmgezi_model->where(array('cid' => $val['id']))->count();
            $nums= $this->pmgezi_model->where(array('cid' => $val['id']))->getField('start_price',true);
            $list[$k]['totals']=array_sum($nums);
        }

        $this->assign('list', $list);
        $this->assign('changci', $changci);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台拍卖场次添加
    public function changciadd()
    {
        if (IS_POST) {
            $article = I("post.post");
            //把时间转换成时间戳
            $t = strtotime($article['start_time']);
            $et = strtotime($article['end_time']);
            //根据北京时间添加荷兰和英国时间
            $article['en_start_time'] = $t - 28800;
            $article['hl_start_time'] = $t - 21600;
            $article['en_end_time'] = $et - 28800;
            $article['hl_end_time'] = $et - 21600;
            $article['start_time'] = $t;
            $article['end_time'] = $et;
            $result = $this->changci_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $arr = I('get.');
        $num = $arr["num"]+1;
        $info = $this->pmzt_model->where(array('id' => $arr['id']))->field('id,tname')->find();
        $this->assign('post', $info);
        $this->assign('num', $num);
        $this->display();
    }

    // 后台拍卖场次编辑
    public function changciedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $article = I("post.post");
            //把时间转换成时间戳
            $t = strtotime($article['start_time']);
            $et = strtotime($article['end_time']);
            //根据北京时间添加荷兰和英国时间
            $article['en_start_time'] = $t - 28800;
            $article['hl_start_time'] = $t - 21600;
            $article['en_end_time'] = $et - 28800;
            $article['hl_end_time'] = $et - 21600;
            $article['start_time'] = $t;
            $article['end_time'] = $et;
            $result = $this->changci_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this->changci_model->where($where)->find();
        $info['tname'] = $this->pmzt_model->where(array('id' => $info['cid']))->getField('tname');

        $this->assign('post', $info);
        $this->display();
    }

    // 后台拍卖场次选择鸽子列表
    public function geziselect()
    {
        $where = array();
        $id = I('get.id');
        $where['l'] = LANG_SET;
        //根据场次id获取该场次下面的所有鸽子信息
        $where['cid'] = $id;
        $article = $this->pmgezi_model->where($where)->getField('huanhao,cid,sequence,addtime');
        foreach ($article as $k => $val){
            $article[$k]['title'] = $this->gezi_model->where(array('huanhao'=>$k))->getField('title');
        }
        $this->assign('list', $article);
        $this->assign('post', $id);
        $this->display();
    }

    // 后台拍卖信鸽添加
    public function pmgeziadd()
    {
        if (IS_POST) {
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");

            $article['addtime'] = time();
            $huanhao = $article['huanhao'];
            $result = $this->pmgezi_model->add($article);
            if ($result) {
                $this-> gezi_model->where("huanhao = '$huanhao'")->setField('zhuangtai',1);
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $huanhao = I('get.');
        $map = array();
        $maps = array();
        foreach ($huanhao as $k => $val){
            $map['huanhao'] = $val[0];
            $maps['id'] = $val[1];
        }
        $info = $this->gezi_model->where($map)->find();
        $info['name'] = $this->changci_model->where($maps)->getField('name');
        $info['cid'] = $maps['id'];

        //获取目录路径
        $path = './data/upload/default/tupian/';
        //取出该目录下所有的文件及目录
        $result = scandir($path);
        //删除目录
        array_splice($result,0,2);

        if(in_array($map['huanhao'].'-pigeon.jpg',$result)){
            $info['pic'] = "$path"."$map[huanhao]".'-pigeon.jpg';
        }
        if(in_array($map['huanhao'].'-eye.jpg',$result)){
            $info['yjpic'] = "$path"."$map[huanhao]".'-eye.jpg';
        }
        if(in_array($map['huanhao'].'-ancestry.jpg',$result)){
            $info['xtpic'] = "$path"."$map[huanhao]".'-ancestry.jpg';
        }
        $info['info'] = $this->xuetongshu_model->where($map)->getField('info');
        if(empty($info['huanhao'])){
            echo '无此鸽子';exit;
        }
        if(!empty($info) && $info['zhuangtai'] == 1){
            echo '此鸽子已在拍卖中';exit;
        }
        if(!empty($info) && $info['zhuangtai'] == 2){
            echo '此鸽子为赠鸽，不能拍卖';exit;
        }
        $this->assign('info', $info);
        $this->display();
    }

    // 后台拍卖场次删除
    public function changcidelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->changci_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->changci_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台拍卖管理列表
    public function xingeindex()
    {
        $where = array();
        $request = I('post.');
        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
//        if (!empty($request['keyword'])) {
//            $keyword = $request['keyword'];
//            $where['title'] = array('like', "%$keyword%");
//        }
        if ($request['keyword'] ) {
//            $keyword = $request['keyword'];
            $where['title'] = array('like', "%".$request['keyword']."%");
        }
        if ($request['zhuangtai'] ) {
//            $keyword = $request['keyword'];
            $where['zhuangtai'] = array('like', "%".$request['zhuangtai']."%");
        }
        $where['l'] = LANG_SET;
        $count = $this->pmproduct_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->pmproduct_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['types'] = $this->pmzt_model->where(array('id' => $val['cid']))->getField('tname');
            $temp = $this->pmjilu_model->where(array('gid' => $val['id']))->field('uid,username,pmprice')->select();
            foreach ($temp as $k => $val){
                $list[$k]['uid'] = $val['uid'];
                $list[$k]['username'] = $val['username'];
                $list[$k]['pmprice'] = $val['pmprice'];
                $temps = $this->member_model->where(array('id' => $val['uid']))->field('baojin,realname')->select();
                foreach ($temps as $k => $val){
                    $list[$k]['baojin'] = $val['baojin'];
                    $list[$k]['realname'] = $val['realname'];
                }
            }
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    public function uploads(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('txt', 'TXT');// 设置附件上传类型
        $upload->rootPath  =      './'.C("UPLOADPATH"); // 设置附件上传根目录
        $upload->savePath  =      '/default/xuetongshu/'; // 设置附件上传（子）目录
        $upload-> autoSub = false;
        $upload->saveName = '';
        $upload->replace   =    true;
// 上传文件
        $info   =   $upload->uploadOne($_FILES['post']);
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功 获取上传文件信息
            return $info;
        }
    }

    // 后台拍卖信鸽血统书添加
    public function xuetongadd()
    {
        if (IS_POST) {
            //上传成功返回文件数组信息
            $fiel = $this->uploads();
            //拆分文件名和扩展名
            $nameArr = explode('.',$fiel['savename']);
            //获取文件路径
            $file = SITE_PATH.'data'.'/'.'upload'.$fiel['savepath'].$fiel['savename'];
            $str=file($file);
            $_POST = array();
            $info = '';
            foreach($str as $val){
                $info .= '<p>'.$val.'</p>';
            }
            $_POST['huanhao'] = $nameArr[0];
            $_POST['info'] = $info;
            $xuetongshu = M("Xuetongshu");
            $xuetongshu->create();
            $result =$xuetongshu->add();
            if ($result) {
                $this->redirect(U('Auction/xingeedit',array('id'=>$result)));
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $info = $this->pmzt_model->getField('id,tname');
        $this->assign('post', $info);
        $this->display();
    }

    // 后台拍卖鸽子列表
    public function geziindex()
    {
        $where = array();
        $request = I('request.');
        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title']  = array('like', "%$keyword%");
        }
        if (!empty($request['huanhao'])) {
            $huanhao = $request['huanhao'];
            $where['huanhao']  = array('like', "%$huanhao%");
        }
        if (!empty($request['zhuangtai'])) {
            $zhuangtai = $request['zhuangtai'];
            $where['zhuangtai'] = array('like', "%$zhuangtai%");
        }
        $where['l'] = LANG_SET;
        $count = $this->gezi_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->gezi_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }


    // 后台拍卖鸽子添加
    public function geziadd()
    {
        if (IS_POST) {
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();
            $result = $this->gezi_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }

    // 后台拍卖鸽子编辑
    public function geziedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> gezi_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this-> gezi_model->where($where)->find();
        $this->assign('post', $info);
        $this->display();
    }

    // 后台拍卖鸽子删除
    public function gezidelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->gezi_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->gezi_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台拍卖信鸽添加
    public function xingeadd()
    {
        if (IS_POST) {
            if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
                foreach ($_POST['photos_url'] as $key=>$url){
                    $photourl=sp_asset_relative_url($url);
                    $_POST['post'][$key] = $photourl;
//                    $_POST['post'][$_POST['photos_alt'][$key]]= $photourl;
                }
            }
            $_POST['post']['created_by']=get_current_admin_id();
            $article=I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['pic'] = $article['0'];
            $article['xtpic'] = $article['1'];
            $article['yjpic'] = $article['2'];
            $result=$this->pmproduct_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
//        if (IS_POST) {
//            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
//            $_POST['post']['xtpic'] = sp_asset_relative_url($_POST['smeta']['thumbs']);
//            $_POST['post']['created_by'] = get_current_admin_id();
//            $article = I("post.post");
//            $article['content'] = htmlspecialchars_decode($article['content']);
//            $result = M("Pmproduct")->add($article);
//            if ($result) {
//                $this->success("添加成功！");
//            } else {
//                $this->error("添加失败！");
//            }
//            exit;
//        }
        $info = $this->pmzt_model->getField('id,tname');
        $this->assign('post', $info);
        $this->display();
    }

    // 后台拍卖信鸽编辑
    public function xingeedit()
    {
        if (IS_POST) {
            echo 6666;
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            if(!isset($article[tuijian])){
                $article[tuijian] = 0;
            }elseif(!isset($article[zhiding])){
                $article[zhiding] = 1;
            }elseif(!isset($article[fobidden])){
                $article[fobidden] = 0;
            }
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> pmproduct_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }

        $where = array();
        $id = I('get.id');
        dump($id);
        $where['id'] = $id;
        $info = $this-> pmproduct_model->where($where)->find();
        $data = $this-> pmzt_model ->getField('id,tname');

        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }

    // 后台拍卖信鸽删除
    public function xingedelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->pmproduct_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->pmproduct_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台拍卖专题订单列表
    public function orderindex()
    {
        $where = array();
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        //获取当前时间
        $temp = time();
        $where['l'] = LANG_SET;
        //拍卖已经结束的条件
        $where['end_time'] = array('lt',"$temp");
        $count = $this->pmzt_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->pmzt_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['names'] = $this->user_model->where(array('id' => $val['adduser']))->getField('user_nicename');
            //计算同个专题的鸽子总数
            $list[$k]['nums'] = $this->pmproduct_model->where(array('cid' => $val['id']))->count();
            //计算同个专题所有鸽子的单价总和
            $nums = $this->pmorder_model->where(array('cid' => $val['id']))->getField('price',true);
            $list[$k]['price']=array_sum($nums);
            //计算同个专题所有鸽子的总金额总和
            $numss = $this->pmorder_model->where(array('cid' => $val['id']))->getField('totalprice',true);
            $list[$k]['totalprice']=array_sum($numss);
            $list[$k]['time'] = time();
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 后台拍卖订单列表
    public function pmorderindex()
    {
        $where = array();
        $id = I('get.id');
        $where['cid'] = $id;
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;
        $count = $this->pmorder_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->pmorder_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['koop'] = $this->pmproduct_model->where(array('id' => $val['shangping_id']))->getField('sequence');
            $list[$k]['huanhao'] = $this->pmproduct_model->where(array('id' => $val['shangping_id']))->getField('huanhao');
            $list[$k]['auctionname'] = $this->pmzt_model->where(array('id' => $val['cid']))->getField('tname');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 后台查看订单信息
    public function pmorderedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> pmorder_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this-> pmorder_model->where($where)->find();
        $this->assign('post', $info);
        $this->display();
    }

    // 后台待处理订单列表
    public function pendingorderindex()
    {
        $where = array();
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;
        //取出当前出价最高纪录
        $where['status'] = array('eq',2);
        $count = $this->pmjilu_model->where($where)->count();
        $page = $this->page($count, 10);
        $list = $this->pmjilu_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->getField('id,cid,sequence,gid,uid,username,cn_tname,cn_title,pmprice');
        foreach ($list as $k => $val) {
            $list[$k]['huanhao'] = $this->pmproduct_model->where(array('id' => $val['gid']))->getField('huanhao');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 后台处理订单信息，生成订单
    public function pendingorderedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);

            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            //删除原始的id
            array_splice($article,0,1);
            $article['order_sn']  = $this->get_sn();
            $result = $this-> pmorder_model->add($article);
            if ($result !== false) {
                //修改待处理订单的状态
                $this-> pmjilu_model->where("id = $post_id")->setField('status',1);
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['lanhai_pmjilu.id'] = $id;

        $info = $this-> pmjilu_model
                ->where($where)
                ->join('lanhai_pmproduct pm ON lanhai_pmjilu.gid = pm.id')
                ->join('lanhai_pmzt t ON lanhai_pmjilu.cid = t.id')
                ->join('lanhai_members m ON lanhai_pmjilu.uid = m.id')
                ->field('lanhai_pmjilu.id,gid,uid,pmprice,t.end_time,pm.sequence,pm.title,lanhai_pmjilu.cid,huanhao,m.username,realname,address,email,telephone,mobile,pm_time,baojin')
                ->select();
        $this->assign('post', $info);
        $this->display();
    }
    public function historyindex()
    {
        $where = array();
        $request = I('request.');
        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;

        $count = $this->yporder_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->yporder_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['name'] = $this->pmproduct_model->where(array('id' => $val['shangping_id']))->getField('title');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }
    //生成订单号
    public function get_sn() {
        return date('YmdHis').rand(100000, 999999);
    }
}
