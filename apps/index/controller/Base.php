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
		
		if(session('?username')) {
			$this -> assign('username', session('username'));
			$this -> assign('is_login', 1);
		}
		

	}
	
	protected function ssp_secret($pass = '') {
		if(empty($pass))
			return null;
			
		return md5('SsPaNeL'. $pass);
	}
	
	
}
