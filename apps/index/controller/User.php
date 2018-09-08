<?php
namespace app\index\controller;

use app\index\controller\Base;

class User extends Base
{
	
	
    public function index()
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
	
	public function checkin()
	{
		if(!$this -> checkLogin())
    		return $this->redirect('user/login');
		
		$query = db('user') -> where('user_name', session('username')) -> find();
		
		if(time() - $query['last_check_in_time'] > 86400) {
			db('user') -> where('user_name', session('username')) -> setField('last_check_in_time', time());
        	
        	
	        $r = rand(1, 100) * 1024 * 1024;
			db('user') -> where('user_name', session('username')) -> setInc('transfer_enable', $r);
			
			$r /= (1024 * 1024);
			echo "<script>alert('签到成功，获得 $r MB流量');window.location.href='" . url('user/index') . "';</script>";
		}
		else
			return $this->redirect('user/index');
		
		
	}
    
    public function setting()
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
    
    public function login()
    {
    	
    	
    	if($this -> checkLogin()) 
    		return $this->redirect('user/index');
		
    	$this -> assign("page_title", "用户登录");
    	
        if(!isset($_POST['id']) && !isset($_POST['pw'])) 
        	return $this -> fetch();
        	
        
        else if (!empty($_POST['id']) && !empty($_POST['pw'])) {
        	$query = db('user')->where('user_name', $_POST['id'])->find();
        	//$query = isset($query[0])?$query[0]:'';
        	
        	if(!isset($query) || empty($query) || $query['pass'] != $this -> ssp_secret($_POST['pw']))
        		return $this -> error("账号或密码错误", "login");

        	
        	
        	
        	session('username', $_POST['id']);
        	//将所有查询改为ssp_session
        	session('ssp_session', $this -> ssp_secret($_POST['id'] . $this -> ssp_secret($_POST['pw'])));
        	
        	return $this->success("欢迎回来，" . $_POST['id'], 'user/index');
        }
        else if (empty($_POST['id']) || empty($_POST['pw'])) {
        	return $this->error('账号或密码不能为空', 'login');
        }
        
        
    }
    
    public function register()
    {
		$this -> assign("page_title", "用户注册");
		return $this -> fetch();
	}
	
	public function forgot()
    {
		$this -> assign("page_title", "找回密码");
		return $this -> fetch();
	}
	
	public function logout()
	{
		session('username', null);
		session('ssp_session', null);
		
		return $this->success('登出成功', 'login');
	}
	
	public function reset_pwd()
	{
		if(!$this -> checkLogin()) 
    		return $this->redirect('user/login');
		
		$str = 'ABCDEFGHIJKLNMOPQRSTUVWXYZabcdefghijklnmopqrstuvwxyz0123456789';
		$pwd = '';
		
		for($i = 0; $i < 8; $i++)
			$pwd .= substr($str, rand(0,61), 1);
		
		db('user') -> where('user_name', session('username')) -> setField("passwd", $pwd);
        
		
		return $this->success('重置成功', 'index');
	}
}
