<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jake
 * Date: 10/31/2018
 * Time: 11:37 PM
 */

namespace app\admin\controller;

use think\Controller;

class Base extends Controller {

    public function __construct() {
        parent::__construct();

        cookie([
            'prefix'     => 'ssp_admin_',
        ]);

        if(cookie('?username') && cookie('?session')) {
            $this -> assign('username', cookie('username'));
            $this -> assign('is_login_admin', 1);
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


}
