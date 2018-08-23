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
        
        if(isset($_REQUEST['checkin']))
        	$this -> assign("checked", date("Y/m/d H:m:s"));
        		
        $query = db('user') -> where('user_name', session('username')) -> find();
        
        $this -> assign("info", $query);
        $this -> assign("remain", 100 - round($query['u'] / $query['transfer_enable'] * 100 + $query['d'] / $query['transfer_enable'] * 100, 1));
        $this -> assign("remain_u", round($query['u'] / $query['transfer_enable'] * 100, 1));
        $this -> assign("remain_d", round($query['d'] / $query['transfer_enable'] * 100, 1));
        $this -> assign("upload", round($query['u'] / 1024 / 1024, 1));
        $this -> assign("download", round($query['d'] / 1024 / 1024, 1));
        $this -> assign("total", round($query['transfer_enable'] / 1024 / 1024, 1));
        $this -> assign("last_time", date('Y-m-d H:m:s',$query['t']));
        
        $node = db('ss_node') -> where('node_type < '.$query['type']) -> select();
        $this -> assign("node_list", $node);
        
        return $this -> fetch();
    }
    
    public function setting()
    {
    	if(!$this -> checkLogin())
    		return $this->redirect('user/login');
    		
    	$this -> assign("user_name", session('username'));
        $this -> assign("page_title", "账号设置");
        
        $query = db('user') -> where('user_name', session('username')) -> find();
        
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
