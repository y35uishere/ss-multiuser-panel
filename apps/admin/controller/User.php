<?php
namespace app\admin\controller;

use think\Controller;

class User extends Base
{
	
	public function infoAction($id)
	{
		//防止SQL注入
		$id = (int)$id;
		
		$query = db('user') -> where('uid', $id) -> find();
		
		$this -> assign("info", $query);
		
		$transfer = [
			'total' => round($query['transfer_enable'] / 1024 / 1024 / 1024, 1),
			'upload' => round($query['u'] / 1024 / 1024 / 1024, 1),
			'download' => round($query['d'] / 1024 / 1024 / 1024, 1),
		
		];
		
		$this -> assign("trans", $transfer);
		$this -> assign("page_title", "用户详情");
		return $this -> fetch();
	}



	
	
	
}

?>