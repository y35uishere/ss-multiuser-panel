<?php
namespace app\admin\controller;

use think\Controller;

class User extends Base
{
	
	public function infoAction()
	{
        if(!input('?id'))
            return $this->error('用户不存在','index/index', 0, 3);

		//防止SQL注入
		$id = (int)input('id');
		
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

    public function disableAction()
    {
        if(!input('?id'))
            return $this->error('用户不存在','index/index', 0, 3);

        $id = (int)input('id');

        $db = db('user');
        $query = $db -> where('uid', $id) -> find();

        if($query['enable'] == 1){
            $db ->where('uid', $id) ->update([
                'enable' => 0
            ]);

            return $this->success('账户 '.  $query['user_name'].' 已禁用', url('admin/user/info', 'id='.$query['uid']), 0, 3);
        }
        else {
            $db ->where('uid', $id) ->update([
                'enable' => 1
            ]);

            return $this->success('账户 '.  $query['user_name'].' 已启用', url('admin/user/info', 'id='.$query['uid']), 0, 3);
        }

    }

	
	
	
}

?>