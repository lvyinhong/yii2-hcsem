<?php
/**
 * Created by PhpStorm.
 * User: lyh
 * Date: 2018/8/28
 * Time: 下午11:08
 */

namespace hc\sem;


class SemClient
{
    private $client = null;
    public $host = '127.0.0.1';// $host是远程服务器的地址，1.10.0或更高版本已支持自动异步解析域名，$host可直接传入域名
    public $port = 9501;// 远程服务器端口
    public $timeout = 1;// $timeout是网络IO的超时，包括connect/send/recv，单位是s，支持浮点数。默认为0.5s，即500ms
    public $logAction = 'close';// close 是关闭log，write 是写入日志文件，screen 是显示到终端

    public function __construct()
    {
        $this->client = new \Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
        //注册连接成功回调
        $this->client->on("connect", array($this, 'onConnect'));
        //注册数据接收回调
        $this->client->on("receive", array($this, 'onReceive'));
        //注册连接失败回调
        $this->client->on("error", array($this, 'onError'));
        //注册连接关闭回调
        $this->client->on("close", array($this, 'onClose'));
    }

    public function connect()
    {
        $fp = $this->client->connect($this->host, $this->port , $this->timeout);
        if( !$fp ) {
            $msg = "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            $this->writeLog($msg, 'error');
            return;
        }
    }

    public function onConnect($cli)
    {
        $cli->send("hello world\n");
    }
    public function onReceive($cli, $data)
    {
        $msg="Received: ".$data."\n";
        $this->writeLog($msg, 'info');
    }
    public function onError($cli)
    {
        $msg =  "Connect failed \n";
        $this->writeLog($msg, 'error');
    }
    public function onClose($cli)
    {
        $msg = "Connection close\n";
        $this->writeLog($msg, 'info');
    }

    public function writeLog($msg, $level)
    {
        switch ($this->logAction){
            case 'close':
                break;
            case 'write':
                \Yii::$level($msg, 'sem');
                break;
            case 'screen':
                echo $msg;
                break;
            default:
                trigger_error('$logAction 参数错误');
                return false;
        }
        return true;
    }
}