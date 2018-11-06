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

        session([
            'prefix'     => 'ssp_admin',
            'type'       => '',
            'auto_start' => true,
        ]);

        if(session('?username') && session('?ssp_session')) {
            $this -> assign('username', session('username'));
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


}