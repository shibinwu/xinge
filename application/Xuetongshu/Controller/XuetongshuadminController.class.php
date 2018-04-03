<?php
namespace Xuetongshu\Controller;

use Common\Controller\AdminbaseController;
ini_set('memory_limit','100M');
class XuetongshuadminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET
    protected $pmproduct_model;
    protected $xuetongshu_model;
    function _initialize()
    {
        parent::_initialize();
        $this->pmproduct_model = M("Pmproduct");
        $this->xuetongshu_model = M("Xuetongshu");
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
    public function add()
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
                $info .= '<p>'.$val.'<p>';
            }
            $_POST['huanhao'] = $nameArr[0];
            $_POST['info'] = $info;
            $xuetongshu = M("Xuetongshu");
            $xuetongshu->create();
            $result =$xuetongshu->add();
            if ($result) {
//                $this->redirect('Auction/Auctionadmin/xingeedit',array('id'=>$result));
                $this->redirect('Xuetongshuadmin/edit',array('id'=>$result));
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }

    // 后台血统书列表
    public function index()
    {
        $where = array();
        $id = I('get.id');
        $where['cid'] = $id;
        $request = I('request.');
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['huanhao'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;
        $count = $this->xuetongshu_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->xuetongshu_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 血统书编辑
    public function edit()
    {
        if (IS_POST ) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> xuetongshu_model->save($article);
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
        $info = $this-> xuetongshu_model
            ->where($where)
            ->select();
        $post =array();
        foreach ($info as $key=>$val){
            $post = $val;
        }
        $this->assign('post', $post);
        $this->display('edit');
    }

    // 后台血统书删除
    public function delete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->xuetongshu_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->xuetongshu_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

}
