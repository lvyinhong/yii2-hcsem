<?php
/**
 * Created by PhpStorm.
 * User: lyh
 * Date: 2018/8/29
 * Time: 上午10:00
 */

namespace hc\sem;


class SemServer
{
    private $serv = null;
    public $host = '127.0.0.1';
    public $port = 9501;// 服务器端口
    public $logAction = 'close';// close 是关闭log，write 是写入日志文件，screen 是显示到终端

    public function __construct()
    {
        $this->serv = new \Swoole\Server($this->host, $this->port);

        //设置异步任务的工作进程数量
        $this->serv->set(array('task_worker_num' => 4));

        $this->serv->on('receive', array($this, 'onReceive'));
        $this->serv->on('task', array($this, 'onTask'));
        $this->serv->on('finish', array($this, 'onFinish'));
        $this->serv->start();
    }

    public function onReceive($serv, $fd, $from_id, $data)
    {
        //投递异步任务
        $task_id = $serv->task($data);
        $msg = "Dispatch AsyncTask: id = $task_id\n";
        $this->writeLog($msg, 'info');
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        $msg = "New AsyncTask[id=$task_id]".PHP_EOL;
        $this->writeLog($msg, 'info');
        //返回任务执行的结果
        $serv->finish("$data -> OK");
    }

    public function onFinish($serv, $task_id, $from_id, $data)
    {
        $msg = "AsyncTask[$task_id] Finish: $data" . PHP_EOL;
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