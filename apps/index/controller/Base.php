<?php
namespace app\index\controller;

use think\Controller;

class Base extends Controller {
	
	public function __construct() {
		parent::__construct();
		
		session([
			    'prefix'     => 'ssp',
			    'type'       => '',
			    'auto_start' => true,
		]);
		
		if(session('?username') && session('?ssp_session')) {
			$this -> assign('username', session('username'));
			$this -> assign('is_login', 1);
		}
		

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
		if(!session('?username') || !session('?ssp_session'))
			return false;
		
		$query = db('user')->where('user_name', session('username'))->find();
		
		if(!isset($query))
			return false;
		else if(session('ssp_session') == $this->ssp_secret($query['user_name'] . $query['pass']))
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
}
