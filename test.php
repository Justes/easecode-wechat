<?php

/**
 * test.php
 *
 * @copyright  Charles.
 * @author     Charles <charles.mz.lyn@gmail.com>
 * @created    2024/8/29 12:17
 */
require __DIR__ . '/vendor/autoload.php';

use Easecode\Wechat\Wechat;

$wechat = new Wechat('APPID', 'APP_SECRET');
$token = $wechat->getAccessToken();
print_r($token);