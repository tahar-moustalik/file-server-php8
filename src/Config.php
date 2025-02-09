<?php

namespace App;

class Config {

    const PARAMS = [
        'MAX_FILE_SIZE' => 3145728,
        'FILE_STORAGE_DIR' => 'storage',
        'LOGIN' => 'user',
        'SECRET' => '123456789'
    ];





    public function get($key)
    {
        if(!isset(self::PARAMS[$key])) throw new \Exception('Config param does not exist');

        return self::PARAMS[$key];

    }
}