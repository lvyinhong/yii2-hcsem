<?php
/**
 * Created by PhpStorm.
 * User: lyh
 * Date: 2018/8/28
 * Time: 下午11:08
 */

namespace hc\sem;


class SemServer
{
    private $client;

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

        $this->connect();
    }

    public function connect()
    {
        $fp = $this->client->connect("127.0.0.1", 9501 , 1);
        if( !$fp ) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
    }

    public function onConnect($cli)
    {
        $cli->send("hello world\n");
    }
    public function onReceive($cli, $data)
    {
        echo "Received: ".$data."\n";
    }
    public function onError($cli)
    {
        echo "Connect failed \n";
    }
    public function onClose($cli)
    {
        echo "Connection close\n";
    }
}

new SemServer();