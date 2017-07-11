<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use \Workerman\Worker;
//use \GatewayWorker\Register;
use \Workerman\WebServer;

// 自动加载类
require_once __DIR__ . '/../../Workerman/Autoloader.php';

// 这里监听8080端口，如果要监听80端口，需要root权限，并且端口没有被其它程序占用
$webserver = new WebServer('http://120.27.47.125:8080');
// 类似nginx配置中的root选项，添加域名与网站根目录的关联，可设置多个域名多个目录
//$webserver->addRoot('www.example.com', '/your/path/of/web/');
$webserver->addRoot('http://120.27.47.125', '/home/GatewayWorker/Workerman/WebServer.php');
// 设置开启多少进程
$webserver->count = 4;

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}

