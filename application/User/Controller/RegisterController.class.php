<?php
namespace User\Controller;

use Common\Controller\HomebaseController;

class RegisterController extends HomebaseController {
	
    // 前台用户注册
	public function index(){
		/*
	    if(sp_is_user_login()){ //已经登录时直接跳到首页
	        redirect(__ROOT__."/");
	    }else{
	        $this->display(":register");
	    }
		*/
		/*
		$code = sp_get_mobile_code(18510860556,300);
		if($code){
			sp_mobile_code_log(18510860556,$code,300);
			$_POST['mobile'] = 18510860556;
			$_POST['mobile_code'] = $code;
			if(!sp_check_mobile_verify_code(18510860556,$code)){
				echo '手机验证码错误！'.$code;exit;
			}
		}else{
			echo '当天发送已经过5条';exit;
		}
		*/
		$cityList = M('region')->where('pid=1')->order('id ASC')->select();
		$this->assign('cityList',$cityList);
		$this->display(":register");
	}
	
	//用户名是否存在
	public function username(){
		$membersMod = M('Members');
		$username = I('request.username');
		$count = $membersMod->where(array('username'=>$username))->count();
		echo $count;
	}
	
	// 短信验证码
	public function mobile_code(){
		$mobile = I('post.mobile');
		if(empty($mobile)){
			echo '0';exit;
		}
		$expire_time = 300;
    	$code = sp_get_mobile_code($mobile,$expire_time);//获取验证码
		if($code){
			sp_mobile_code_log($mobile,$code,$expire_time);//更新短信发送日志
			$contents = '【欧洲信鸽站】注册验证码为：'.$code.',有效期5分钟';
			$mySmsDataInfoArray = array("x_eid"=>"10685","x_uid"=>"bolmedia","x_pwd_md5"=>"11907c5a90a7f46aaa8533505d8049d5","x_ac"=>"12","x_target_no"=>$mobile,"x_memo"=>"$contents");
			$result = $this->SendSMSByGet("http://gateway.woxp.cn:6630/utf8/web_api/?",$mySmsDataInfoArray);
			echo $result;exit;
			if($result>0){
				echo '1';
			}else{
				echo '0';
			}
			/*
			if(!sp_check_mobile_verify_code($mobile,$code)){
				echo '手机验证码错误！'.$code;exit;
			}
			*/
		}else{
			echo '2';
		}
    	exit;
	}
	
	// 前台用户注册提交
	public function doregister(){
    	//手机号注册
		$this->_do_mobile_register();
		/*
    	if(isset($_POST['email'])){
    	    //邮箱注册
    	    $this->_do_email_register();
    	}elseif(isset($_POST['mobile'])){
    	    //手机号注册
    	    $this->_do_mobile_register();
    	}else{
    	    $this->error("注册方式不存在！");
    	}
		*/
    	
	}
	
	// 前台用户手机注册
	private function _do_mobile_register(){
	    
	    //if(!sp_check_verify_code()){
	        //$this->error("验证码错误！");
	    //}
	    if(!sp_check_mobile_verify_code()){
	        $this->error("手机验证码错误！");
        }
	    $users_model=M("Members");
	    $password=I('post.password');
	    $username=I('post.username');
	    $realname=I('post.realname');
		$mobile=I('post.mobile');
		$email=I('post.email');
		$telephone=I('post.telephone');
		$province=I('post.province');
		$address=I('post.address');
		$data = array();
		$data['username'] = $username;//审核
		$data['realname'] = $realname;//审核
		$data['mobile'] = $mobile;//审核
		$data['email'] = $email;//审核
		$data['telephone'] = $telephone;
		$data['xuexi'] = serialize(array_filter($_POST['xuexi']));
		$data['province'] = $province;//审核
		$data['address'] = $address;//审核
		$data['reg_ip'] = $data['lastloginip'] = get_client_ip(0,true);
		$data['state'] = 1;//国籍
		$data['zhuangtai'] = 1;//账户状态1打开2关闭3冻结
		$data['logintimes'] = 1;//登录次数
		$data['lastlogintime'] = $data['addtime'] = date('Y-m-d H:i:s');//最近登录时间
		$data['working'] = 1;//审核
	    $data['password'] = md5($password);
	    $result = $users_model->add($data);
	    if($result){
			session('mobile_code',null);
	        //注册成功页面跳转
	        $data['id']=$result;
	        session('user',$data);
	        $this->success("注册成功！",__ROOT__."/");
	         
	    }else{
	        $this->error("注册失败！",U("user/register/index"));
	    }
	}
	
	// 前台用户邮件注册
	private function _do_email_register(){
	   
        if(!sp_check_verify_code()){
            $this->error("验证码错误！");
        }
        
        $rules = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('email', 'require', '邮箱不能为空！', 1 ),
            array('password','require','密码不能为空！',1),
            array('password','5,20',"密码长度至少5位，最多20位！",1,'length',3),
            array('repassword', 'require', '重复密码不能为空！', 1 ),
            array('repassword','password','确认密码不正确',0,'confirm'),
            array('email','email','邮箱格式不正确！',1), // 验证email字段格式是否正确
        );
	     
	    $users_model=M("Users");
	     
	    if($users_model->validate($rules)->create()===false){
	        $this->error($users_model->getError());
	    }
	     
	    $password=I('post.password');
	    $email=I('post.email');
	    $username=str_replace(array(".","@"), "_",$email);
	    //用户名需过滤的字符的正则
	    $stripChar = '?<*.>\'"';
	    if(preg_match('/['.$stripChar.']/is', $username)==1){
	        $this->error('用户名中包含'.$stripChar.'等非法字符！');
	    }
	     
// 	    $banned_usernames=explode(",", sp_get_cmf_settings("banned_usernames"));
	     
// 	    if(in_array($username, $banned_usernames)){
// 	        $this->error("此用户名禁止使用！");
// 	    }
	    
	    $where['user_login']=$username;
	    $where['user_email']=$email;
	    $where['_logic'] = 'OR';
	    
	    $ucenter_syn=C("UCENTER_ENABLED");
	    $uc_checkemail=1;
	    $uc_checkusername=1;
	    if($ucenter_syn){
	        include UC_CLIENT_ROOT."client.php";
	        $uc_checkemail=uc_user_checkemail($email);
	        $uc_checkusername=uc_user_checkname($username);
	    }
	     
	    $users_model=M("Users");
	    $result = $users_model->where($where)->count();
	    if($result || $uc_checkemail<0 || $uc_checkusername<0){
	        $this->error("用户名或者该邮箱已经存在！");
	    }else{
	        $uc_register=true;
	        if($ucenter_syn){
	             
	            $uc_uid=uc_user_register($username,$password,$email);
	            //exit($uc_uid);
	            if($uc_uid<0){
	                $uc_register=false;
	            }
	        }
	        if($uc_register){
	            $need_email_active=C("SP_MEMBER_EMAIL_ACTIVE");
	            $data=array(
	                'user_login' => $username,
	                'user_email' => $email,
	                'user_nicename' =>$username,
	                'user_pass' => sp_password($password),
	                'last_login_ip' => get_client_ip(0,true),
	                'create_time' => date("Y-m-d H:i:s"),
	                'last_login_time' => date("Y-m-d H:i:s"),
	                'user_status' => $need_email_active?2:1,
	                "user_type"=>2,//会员
	            );
	            $rst = $users_model->add($data);
	            if($rst){
	                //注册成功页面跳转
	                $data['id']=$rst;
	                session('user',$data);
	                	
	                //发送激活邮件
	                if($need_email_active){
	                    $this->_send_to_active();
	                    session('user',null);
	                    $this->success("注册成功，激活后才能使用！",U("user/login/index"));
	                }else {
	                    $this->success("注册成功！",__ROOT__."/");
	                }
	                	
	            }else{
	                $this->error("注册失败！",U("user/register/index"));
	            }
	             
	        }else{
	            $this->error("注册失败！",U("user/register/index"));
	        }
	         
	    }
	}
	
	// 前台用户邮件注册激活
	public function active(){
		$hash=I("get.hash","");
		if(empty($hash)){
			$this->error("激活码不存在");
		}
		
		$users_model=M("Users");
		$find_user=$users_model->where(array("user_activation_key"=>$hash))->find();
		
		if($find_user){
			$result=$users_model->where(array("user_activation_key"=>$hash))->save(array("user_activation_key"=>"","user_status"=>1));
			
			if($result){
				$find_user['user_status']=1;
				session('user',$find_user);
				$this->success("用户激活成功，正在登录中...",__ROOT__."/");
			}else{
				$this->error("用户激活失败!",U("user/login/index"));
			}
		}else{
			$this->error("用户激活失败，激活码无效！",U("user/login/index"));
		}
		
		
	}
	function SendSMSByGet($url,$data=''){
		$row = parse_url($url);
		$host = $row['host'];
		$port = $row['port'] ? $row['port']:80;
		$file = $row['path'];
		while (list($k,$v) = each($data)) {
			$get .= rawurlencode($k)."=".rawurlencode($v)."&";	//×ªURL±ê×¼Âë
		}
		$get = substr( $get , 0 , -1 );
		$len = strlen($get);
		$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			$receive = '';
			$out = "GET $file HTTP/1.1\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Content-type: application/x-www-form-urlencoded\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Content-Length: $len\r\n\r\n";
			$out .= $get;		
			fwrite($fp, $out);
			while (!feof($fp)) {
				$receive .= fgets($fp, 128);
			}
			fclose($fp);
			$receive = explode("\r\n\r\n",$receive);
			unset($receive[0]);
			return implode("",$receive);
		}
	}
	
}