<?php

$up_dir = strrpos($_SERVER['DOCUMENT_ROOT'], "/");
$up_dir = substr($_SERVER['DOCUMENT_ROOT'], 0 , $up_dir );

return array(
    'db'=>array(
        'connecttype' => 'mysql',
        'host'=>'localhost',
        'dbname'=>'b24App',
        'user' => 'b24App',
        'pwd' => 'JKcnwDcefgjmv9',
        'charset' => 'utf8'),
    'log'=>array(
        'logging'=>'enabled',
        'mode'=>(LOG__ERROR | LOG__WARNING | LOG__ACTION  ),
        'path'=>$up_dir.'/log/log.txt'),
    'appdir'=> $up_dir.'/ext_app/',
    'upload'=> $up_dir.'/upload/',
    'email'=>array(
        'host' => 'smtp.yandex.ru',
        'userName' => 'portal@income-media.ru',
        'password' => 'zuvayixywibauiru',
        'from' => 'portal@income-media.ru' ),
    'jsonPath' => $up_dir.'/upload/',
    'cryptkey' => 'kx1i829jdwef3421n!9uxji93jux2201',
    'tgtoken' => ''
);