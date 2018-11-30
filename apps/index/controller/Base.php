<?php
namespace app\index\controller;

use think\Controller;

class Base extends Controller {
	
	public function __construct() {
		parent::__construct();
		

		
		if(cookie('?username') && cookie('?session')) {
			$this -> assign('username', cookie('username'));
			$this -> assign('is_login', 1);
		}

        $this -> assign('ip', $this->request->ip());
		

	}
	
	public function _empty() {
		return "404";
		
	}
	
	protected function ssp_secret($pass = '') {
		if(empty($pass))
			return null;
			
		return md5('SsPaNeL'. $pass);
	}
	
	protected function checkLogin() {
		if(!cookie('?username') || !cookie('?session'))
			return false;
		
		$query = db('user')->where('user_name', cookie('username'))->find();
		
		if(!isset($query))
			return false;
		else if(cookie('session') == $this->ssp_secret($query['user_name'] . $query['pass']))
			return true;
		
		return false;
	}
	
	protected function getRandomStr($length = 0) {
		$str = 'ABCDEFGHIJKLNMOPQRSTUVWXYZabcdefghijklnmopqrstuvwxyz0123456789';
		$code = '';
					
		for($i = 0; $i < $length; $i++)
			$code .= substr($str, rand(0,61), 1);
			
		return $code;
	}
	
	protected function getOrderNo() {
		return date('YmdHis') . substr('00000'. rand(0,999999), -6);
	}
	
	protected function getToken($order = '', $type = '', $money = '') {
		return md5('SsPaNeL'. $order . $type . $money);
	}

	protected function checkCookie() {
        //判断cookies是否被篡改
        //username、timestamp、session
        //username.timestamp.pass =>session?
    }
}
