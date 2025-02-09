<?php

namespace App;


class SecurityMiddleware {



    public function check()
    {

        if(!isset($_SERVER['HTTP_AUTHORIZATION'])) return false;

        list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'],6)));
        $config = new Config();
        $login = $config->get('LOGIN');
        $secret = $config->get('SECRET');
        return $username === $login && $password === $secret;
    }

}