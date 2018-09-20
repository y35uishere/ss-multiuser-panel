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
		
        $node = db('ss_node') -> where('node_type < '.$query['type']) -> select();
        $this -> assign("node_list", $node);
        
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
    	
        if(!input('?post.id') && !input('?post.pw')) 
        	return $this -> fetch();
        	
        
        else if (!empty(input('post.id')) && !empty(input('post.pw'))) {
        	$query = db('user')->where('user_name', input('post.id'))->find();
        	//$query = isset($query[0])?$query[0]:'';
        	
        	if(!isset($query) || empty($query) || $query['pass'] != $this -> ssp_secret(input('post.pw')))
        		return $this -> error("账号或密码错误", "login");

        	
        	
        	
        	session('username', input('post.id'));
        	//将所有查询改为ssp_session
        	session('ssp_session', $this -> ssp_secret(input('post.id') . $this -> ssp_secret($_POST['pw'])));
        	
        	return $this->success("欢迎回来，" . input('post.id'), 'user/index');
        }
        else if (empty(input('post.id')) || empty(input('post.pw'))) {
        	return $this->error('账号或密码错误', 'login');
        }
        
        
    }
    
    public function registerAction()
    {
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
	
	public function reset_pwdAction()
	{
		if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');
		
		$pwd = $this -> getRandomStr(8);
		
		db('user') -> where('user_name', session('username')) -> setField("passwd", $pwd);
        
		
		return $this->success('重置成功', 'index');
	}
	
	private function checkReg($id, $pwd)
	{
		//pwd弱口令禁止
		//username禁止字段
		return true;
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
		if (Request::instance()->isPost()) {
			return $_POST['a'];
		}
		
		return 'wrong argument'; 
		
	}
}
