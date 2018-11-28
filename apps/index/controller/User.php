<?php
namespace app\index\controller;

use app\index\controller\Base;
use think\Request;

class User extends Base
{
	
	
    public function indexAction()
    {
    	if(!$this -> checkLogin())
    		return $this->redirect('user/login');
    		
    	$this -> assign("user_name", session('username'));
        $this -> assign("page_title", "个人中心");
        
	
        $query = db('user') -> where('user_name', session('username')) -> find();
        
        $this -> assign("info", $query);
        @$this -> assign("remain", 100 - round($query['u'] / $query['transfer_enable'] * 100 + $query['d'] / $query['transfer_enable'] * 100, 1));
        @$this -> assign("progress_u", round($query['u'] / $query['transfer_enable'] * 100, 1));
        @$this -> assign("progress_d", round($query['d'] / $query['transfer_enable'] * 100, 1));
        $this -> assign("upload", round($query['u'] / 1024 / 1024 / 1024, 1));
        $this -> assign("download", round($query['d'] / 1024 / 1024 / 1024, 1));
        $this -> assign("total", round($query['transfer_enable'] / 1024 / 1024 / 1024, 1));
        $this -> assign("last_time", date('Y-m-d H:m:s',$query['t']));
		
		$checked = array('t' => date("Y/m/d H:m:s", $query['last_check_in_time']), 'e' => ((time() - $query['last_check_in_time'] > 86400)?'1':'0'));
		
		$this -> assign("checked", $checked);

		
        $node = db('ss_node') -> where('node_type < '.$query['type']) -> paginate(2);

        $page = $node ->render();

        if($this->request->isAjax()) {

            $this -> assign("node", $node);
            $this -> assign("page", $page);
            return $this->fetch('User/ajaxNode');
        }

        $this -> assign("node", $node);
        $this -> assign("page", $page);



        return $this -> fetch();
    }
	
	public function checkinAction()
	{
		if(!$this -> checkLogin())
    		return $this->redirect('user/login');
		
		$db = db('user');
		$query = $db -> where('user_name', session('username')) -> find();
		
		if(time() - $query['last_check_in_time'] > 86400) {
			$db -> where('user_name', session('username')) -> setField('last_check_in_time', time());
        	
        	
	        $r = rand(1, 100) * 1024 * 1024;
			$db -> where('user_name', session('username')) -> setInc('transfer_enable', $r);
			
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
    		
    	$this -> assign("user_name", session('username'));
        $this -> assign("page_title", "账号设置");
        
        $query = db('user') -> where('user_name', session('username')) -> find();
        
        $this -> assign("total", round($query['transfer_enable'] / 1024 / 1024 / 1024, 1));
        $this -> assign("info", $query);
       	return $this -> fetch();		
    }
    
    public function loginAction()
    {
    	
    	
    	if($this -> checkLogin()) 
    		return $this->redirect('user/index');
		
    	$this -> assign("page_title", "用户登录");

        $id = input('post.id');
        $pw = input('post.pw');


        if(!input('?post.id') && !input('?post.pw')) 
        	return $this -> fetch();
        else if (!empty($id) && !empty($pw)) {
            $db = db('user');
        	$query = $db->where('user_name', $id)->find();
        	//$query = isset($query[0])?$query[0]:'';
        	
        	if(!isset($query) || empty($query) || $query['pass'] != $this -> ssp_secret($pw))
        		return $this -> error("账号或密码错误", "login");

        	
        	
        	
        	session('username', $id);
        	//将所有查询改为ssp_session
        	session('ssp_session', $this -> ssp_secret($id . $this -> ssp_secret($pw)));
            //记录用户登录
            $db -> where('user_name', session('username')) -> setField('last_login_time', time());

        	return $this->success("欢迎回来，" . $id, 'user/index');
        }
        else if (empty($id) || empty($pw)) {
        	return $this->error('账号或密码错误', 'login');
        }
        
        
    }
    
    public function registerAction()
    {
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
		session('username', null);
		session('ssp_session', null);
		
		return $this->success('登出成功', 'login');
	}
	
	public function resetpwdAction()
	{
		if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');

		$last_reset = time() - db('user') -> field("last_rest_pass_time")-> where('user_name', session('username')) -> find()['last_rest_pass_time'];

        if($last_reset < 200){
            return $this->error( '密码更改频繁，请 ' . (200 - $last_reset).' 秒后再试。' , 'index');
        }
		
		$pwd = $this -> getRandomStr(8);
		
		db('user') -> where('user_name', session('username')) -> setField("passwd", $pwd);

        db('user') -> where('user_name', session('username')) -> setField("last_rest_pass_time", time());
        
		
		return $this->success('重置成功', 'index');
	}
	
	private function checkReg($id, $pwd)
	{
		//pwd弱口令禁止
		//username禁止字段
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
		if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');

		
		$db = db('user');
    	$query = $db -> where('user_name', session('username')) -> find();
    	
    	$this -> assign("info", $query);
		
		$this -> assign("remain", round($query['transfer_enable'] / 1024 / 1024 / 1024, 1) - round($query['u'] / 1024 / 1024 / 1024, 1) - round($query['d'] / 1024 / 1024 / 1024, 1));
		
		
		$this -> assign("page_title", "充值");
		return $this -> fetch();
	}
	
	public function inviteAction($add = 0)
	{
		if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');
    	
    	$db = db('user');
    	$query = $db -> where('user_name', session('username')) -> find();
		
		
		if($add == 1 && $query['invite_num'] <= 2) {
			$code = $this -> getRandomStr(27);
				
			db('invite_code') -> insert(['code' => $code, 'user' => 0, 'invite_user' => $query['uid']]);
			
			$db -> where('user_name', session('username')) -> setInc('invite_num', 1);
			
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
         */

        if($this->request->isAjax()){
            $user = input('name');
            $email = input('email');

            if(empty($user) || empty($email))
                return json([
                    'response' => -3,
                    'msg' => 'missing argument',
                ]);

            $query = db('user') -> where('user_name', $user) ->find();

            if(isset($query))
                return json([
                    'response' => -2,
                    'msg' => 'repeated username',
                ]);

            $query = db('user') -> where('email', $email) ->find();

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

            if(isset($query))
                return json([
                    'response' => -2,
                    'msg' => 'repeated invite code',
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
}
