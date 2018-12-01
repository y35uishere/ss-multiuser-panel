<?php
namespace app\index\controller;

use app\index\controller\Base;
use think\Request;

class User extends Base
{
	
	
    public function indexAction()
    {
        //用户中心
    	if(!$this -> checkLogin())
    		return $this->redirect('user/login');

        $query = db('user') -> where('user_name', cookie('username')) -> find();

        //ajax分页输出
        $node = db('ss_node') -> where('node_type < '.$query['type']) -> paginate(2);

        $page = $node ->render();

        if($this->request->isAjax()) {

            $this -> assign("node", $node);
            $this -> assign("page", $page);
            return $this->fetch('User/ajaxNode');
        }


        $checked = array('t' => date("Y/m/d H:m:s", $query['last_check_in_time']), 'e' => ((time() - $query['last_check_in_time'] > 86400)?'1':'0'));

        $this -> assign("info", $query);
        @$this -> assign("remain", 100 - round($query['u'] / $query['transfer_enable'] * 100 + $query['d'] / $query['transfer_enable'] * 100, 1));
        @$this -> assign("progress_u", round($query['u'] / $query['transfer_enable'] * 100, 1));
        @$this -> assign("progress_d", round($query['d'] / $query['transfer_enable'] * 100, 1));
        $this -> assign("upload", round($query['u'] / 1024 / 1024 / 1024, 1));
        $this -> assign("download", round($query['d'] / 1024 / 1024 / 1024, 1));
        $this -> assign("total", round($query['transfer_enable'] / 1024 / 1024 / 1024, 1));
        $this -> assign("last_time", date('Y-m-d H:m:s',$query['t']));
		$this -> assign("checked", $checked);
        $this -> assign("node", $node);
        $this -> assign("page", $page);
        $this -> assign("user_name", cookie('username'));
        $this -> assign("page_title", "个人中心");

        return $this -> fetch();
    }
	
	public function checkinAction()
	{
	    //签到部分
		if(!$this -> checkLogin())
    		return $this->redirect('user/login');
		
		$db = db('user');
		$query = $db -> where('user_name', cookie('username')) -> find();


		if(time() - $query['last_check_in_time'] > 86400) {
			$db -> where('user_name', cookie('username')) -> setField('last_check_in_time', time());
        	
        	
	        $r = rand(1, 100) * 1024 * 1024;
			$db -> where('user_name', cookie('username')) -> setInc('transfer_enable', $r);
			
			$r /= (1024 * 1024);
			echo "<script>alert('签到成功，获得 $r MB流量');window.location.href='" . url('user/index') . "';</script>";
		}
		else
			return $this->redirect('user/index');
		
		
	}
    
    public function settingAction()
    {
    	if(!$this -> checkLogin())
    		return $this->redirect('user/login');

        $query = db('user') -> where('user_name', cookie('username')) -> find();


        $this -> assign("total", round($query['transfer_enable'] / 1024 / 1024 / 1024, 1));
        $this -> assign("info", $query);
        $this -> assign("user_name", cookie('username'));
        $this -> assign("page_title", "账号设置");
       	return $this -> fetch();		
    }
    
    public function loginAction()
    {
    	
    	
    	if($this -> checkLogin()) 
    		return $this->redirect('user/index');


        $user = input('post.user');
        $pw = input('post.pw');
        $this -> assign("page_title", "用户登录");
        $this -> assign('user', session('?username')?session('username'):'');

        if(!input('?post.id') && !input('?post.pw')) 
        	return $this -> fetch();
        else if (!empty($user) && !empty($pw)) {

            $db = db('user');
        	$query = $db->where('user_name', $user)->find();

        	if(!isset($query) || empty($query) || $query['pass'] != $this -> ssp_secret($pw))
        		return $this -> error("账号或密码错误", "login");

            cookie('username', $user);
        	//将所有查询改为ssp_session
            cookie('session', $this -> ssp_secret($user . $this -> ssp_secret($pw)));
            //记录用户登录
            $db -> where('user_name', cookie('username')) -> setField('last_login_time', time());

            session('username', $user);

        	return $this->success("欢迎回来，" . $user, 'user/index');
        }
        else if (empty($id) || empty($pw)) {
        	return $this->error('账号或密码错误', 'login');
        }
        
        
    }
    
    public function registerAction()
    {
        //注册模块
    	if($this -> checkLogin()) 
    		return $this->redirect('user/index');

    	if($this->request->isPost()){
    	    //id,email,pw1,pw2,key



            return $this-> success('注册成功，欢迎登船。', 'user/index');
        }


		$this -> assign("page_title", "用户注册");
		return $this -> fetch();
	}
	
	public function forgotAction()
    {
		$this -> assign("page_title", "找回密码");
		return $this -> fetch();
	}
	
	public function logoutAction()
	{
        cookie('username', null);
        cookie('session', null);
		
		return $this->success('登出成功', 'login');
	}
	
	public function resetpwdAction()
	{
	    //连接密码重置
		if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');

		//获取当前时间与上次重置时间差
		$last_reset = time() - db('user') -> field("last_rest_pass_time")-> where('user_name', cookie('username')) -> find()['last_rest_pass_time'];

        if($last_reset < 200){
            return $this->error( '密码更改频繁，请 ' . (200 - $last_reset).' 秒后再试。' , 'index');
        }
		
		$pwd = $this -> getRandomStr(8);

        //应添加判断username是否被篡改（每次使用cookie username时）
		db('user') -> where('user_name', cookie('username')) -> setField("passwd", $pwd);

        db('user') -> where('user_name', cookie('username')) -> setField("last_rest_pass_time", time());
        
		
		return $this->success('重置成功', 'index');
	}
	
	private function checkReg($user, $pwd, $email, $code)
	{
	    //后台表单检查
		//pwd弱口令禁止
		//username禁止字段
        if(!isset($user) || !isset($pwd) || !isset($email) || !isset($code))
            return false;

        //username正则下划线、字母、数字
        $preg='/^[\w\_]{6,20}$/u';
        if(!preg_match($preg, $user))
            return false;

        //email判断
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            return false;

        //密码长度
        if(strlen($pwd) < 5 || strlen($pwd) > 20)
            return false;

        //冲突email、username
        $db = db('user');
        $query = $db -> where('user_name', $user) ->find();
        if(isset($query))
            return false;

        $query = $db -> where('email', $email) ->find();
        if(isset($query))
            return false;

        //邀请码出错、重复使用
        $query = db('invite_code') -> where('code', $code) ->find();

        if(isset($query) && $query['user'] != 0)
            return false;
        else if(!isset($query))
            return false;


		return true;
	}
	
	public function changepwdAction()
    {
        /*
         * 包含已知用户修改以及找回密码
         * 使用 $token 找回密码
         * 使用正常后台流程进行修改密码
         */

        $this -> assign('page_title', '密码修改');
        return $this ->fetch();
    }
	public function chargeAction()
	{
	    //充值页面
		if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');

		
		$db = db('user');
    	$query = $db -> where('user_name', cookie('username')) -> find();
    	
    	$this -> assign("info", $query);
		
		$this -> assign("remain", round($query['transfer_enable'] / 1024 / 1024 / 1024, 1) - round($query['u'] / 1024 / 1024 / 1024, 1) - round($query['d'] / 1024 / 1024 / 1024, 1));
		
		
		$this -> assign("page_title", "充值");
		return $this -> fetch();
	}
	
	public function inviteAction($add = 0)
	{
	    //邀请

		if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');
    	
    	$db = db('user');
    	$query = $db -> where('user_name', cookie('username')) -> find();
		
		
		if($add == 1 && $query['invite_num'] <= 2) {
			$code = $this -> getRandomStr(27);
				
			db('invite_code') -> insert(['code' => $code, 'user' => 0, 'invite_user' => $query['uid']]);
			
			$db -> where('user_name', cookie('username')) -> setInc('invite_num', 1);
			
			return $this->success('成功', 'invite', 0, 1);
		}
		else if($add == 1)
			return $this->error('错误', 'invite', 0, 1);
			
		$invite_list = db('invite_code') -> where('invite_user', $query['uid']) -> select();
		
		$this -> assign("remain", 3 - $query['invite_num']);
		$this -> assign("invite_list", $invite_list);
		$this -> assign("page_title", "邀请好友");
		return $this -> fetch();

	}
	
	public function checkoutAction()
	{
	    //订单信息
		if ($this->request->isPost()) {
			$order = $this-> getOrderNo();
			
			
			if(input('?post.money'))
				return json([
							'order_no' => $order,
							'money' => input('post.money'),
							'type' => input('post.type'),
							'timestamp' => date('YmdHis'),
							'token' => $this -> getToken($order, 1, input('post.money')),
						]);
			else if(input('?post.band'))
				return json([
							'order_no' => $order,
							'money' => input('post.band'),
							'type' => input('post.type'),
							'timestamp' => date('YmdHis'),
							'token' => $this -> getToken($order, 2, input('post.money')),
						]);
		}
		
		return 'wrong argument'; 
		
	}

	public function checkregAction(){
        /* 检查用户名可用性
         *
         * 0 => ok
         * -1 => bad request
         * -2 => repeated username
         * -3 => missing argument
         * -4 => repeated email
         * -5 => invalid username
         * -6 => invalid email
         */

        if($this->request->isAjax()){
            $user = input('name');
            $email = input('email');

            if(empty($user) || empty($email))
                return json([
                    'response' => -3,
                    'msg' => 'missing argument',
                ]);

            $preg='/^[\w\_]{6,20}$/u';
            if(!preg_match($preg, $user))
                return json([
                    'response' => -5,
                    'msg' => 'invalid username',
                ]);

            if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                return json([
                    'response' => -6,
                    'msg' => 'invalid email',
                ]);


            $db = db('user');
            $query = $db -> where('user_name', $user) ->find();

            if(isset($query))
                return json([
                    'response' => -2,
                    'msg' => 'repeated username',
                ]);

            $query = $db -> where('email', $email) ->find();

            if(isset($query))
                return json([
                    'response' => -4,
                    'msg' => 'repeated email',
                ]);


            return json([
                'response' => 0,
                'msg' => 'ok',
            ]);
        }
        else
            return json([
                'response' => -1,
                'msg' => 'bad request',
            ]);

    }


    public function checkinviteAction(){
        /* 检测邀请码可用性
         *
         * 0 => ok
         * -1 => bad request
         * -2 => repeated invite code
         * -3 => missing argument
         *
         */

        if($this->request->isAjax()){
            $code = input('code');

            if(empty($code))
                return json([
                    'response' => -3,
                    'msg' => 'missing argument',
                ]);

            $query = db('invite_code') -> where('code', $code) ->find();

            if(isset($query) && $query['user'] != 0)
                return json([
                    'response' => -2,
                    'msg' => 'invalid invite code',
                ]);
            else if(isset($query))
                return json([
                    'response' => 0,
                    'msg' => 'ok',
                ]);
        }
        else
            return json([
                'response' => -1,
                'msg' => 'bad request',
            ]);

    }
}
