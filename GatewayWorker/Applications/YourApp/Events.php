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

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

require_once __DIR__ . "/../../Watcher/Watcher.php";

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        Watcher::tick("Worker", "onConnect");
        Watcher::report("Worker", "onConnect", true, 201, $client_id . "连接成功");

        Gateway::sendToClient($client_id, json_encode(array('type' => 'connect', 'clientId' => $client_id)));
        echo "【Worker】连接来源未设置；客户端ID：" . $client_id . "已连接" . "\n";
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message)
    {
        Watcher::tick("Worker", "onMessage");
        //要求$message需为json格式
        echo $message . "\n";
        $req_data = json_decode($message, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            Watcher::report("Worker", "onMessage", true, 1008, $client_id . "数据格式不对");
            return;
        } else {
            //还是要绑定一次UID因为总有情况是client_id会变
            Gateway::bindUid($client_id, $req_data['fromId']);
	    $req_data['msgId']=self::getMillisecond();
            $toWhich = $req_data['toWhich'];
            switch ($toWhich) {
                case 'friend':
                    //先发送给自己
                    Gateway::sendToUid($req_data['fromId'], json_encode($req_data));
                    //发送给好友
                    Gateway::sendToUid($req_data['toId'], json_encode($req_data));
                    break;
                case 'group':
                    //发送给群组即可，自己也会收到，下面只是简单的示例
                    //1、查询数据库，找到群组中的所有人，逐一进行广播
                    //2、或者将每个人登录时都加入group，然后直接在group中广播
                    for ($i = 1; $i < 7; $i++) {
                        Gateway::sendToUid($i, json_encode($req_data));
                    }
                    break;
                case 'server':
                    //一上线就绑定UID
                    Gateway::bindUid($client_id, $req_data['fromId']);
                    break;
                default:
                    # code...
                    break;
            }
            Watcher::report("Worker", "onMessage", true, 202, $client_id . $message);
        }
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        Watcher::tick("Worker", "onClose");
        Watcher::report("Worker", "onClose", true, 1000, $client_id . "关闭连接");
        echo $client_id . "hasClosed" . "\n";
    }

    /**
     * 获取当前时间的毫秒数
     * @return float
     */
    public static function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}
