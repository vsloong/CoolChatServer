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
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;
use \Plugins\Domains;
// 自动加载类
require_once __DIR__ . '/../../Workerman/Autoloader.php';
require_once __DIR__ . '/../../Watcher/Watcher.php';
//require_once __DIR__ . '/../../Plugins/Domains.php';

Autoloader::setRootPath(__DIR__);

// gateway 进程，这里使用Text协议，可以用telnet测试
$gateway = new Gateway("websocket://0.0.0.0:8283");
// gateway名称，status方便查看
$gateway->name = 'GatewayWS';
// gateway进程数，gateway属于CPU密集型，建议设为逻辑CPU个数
//$gateway->count = 4;
$gateway->count = intval(shell_exec('cat /proc/cpuinfo | grep "processor" | wc -l'));
// 本机ip，分布式部署时使用内网ip
$gateway->lanIp = '120.27.47.125';
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口 
$gateway->startPort = 2800;
// 服务注册地址
$gateway->registerAddress = '127.0.0.1:1238';

// 心跳间隔
//$gateway->pingInterval = 10;
// 心跳数据
//$gateway->pingData = '{"type":"ping"}';


// 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
$gateway->onConnect = function ($connection) {
    $connection->onWebSocketConnect = function ($connection, $http_header) {


        Watcher::tick("Gateway", "onWebSocketConnect");
        Watcher::report("Gateway", "onWebSocketConnect", true, 200,  "连接成功");

        // 可以在这里判断连接来源是否合法，不合法就关掉连接
        // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接	
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : "无";
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "无";
        $token = isset($_GET['token']) ? $_GET['token'] : "无";
        $_SESSION['HTTP_ORIGIN'] = $origin;
        echo "【Gateway】连接来源：" . $origin . "；客户端的信息是：" . $userAgent . "；用户的token是：" . $token . "\n";
        
	include_once __DIR__ . '/../../Plugins/Domains.php';
	$domains = new Domains();
	echo $domains->getDomainNames();
	
	//static $domains = array(
        //	"http://121.40.137.188",
        //	""
        //);
        //if(!in_array($_SERVER['HTTP_ORIGIN'], $domains))
        //{
        //    $connection->close();
        //}
        // onWebSocketConnect 里面$_GET $_SERVER是可用的
        // var_dump($_GET, $_SERVER);
    };
};


// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}

