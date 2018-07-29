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
		
		if(session('?id')) {
			$this -> assign('is_login', 1);
		}
		

	}
	
	
}
