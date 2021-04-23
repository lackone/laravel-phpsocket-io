<?php

namespace Lackone\LaravelPhpsocketIo\Service;

use Workerman\Worker;
use PHPSocketIO\SocketIO;

class DefaultSocketIO
{
    public $service_name = '';
    public $io = null;
    public $global = [];

    public function __construct($service_name)
    {
        $this->service_name = $service_name;
    }

    /**
     * 开启服务
     */
    public function start()
    {
        $this->io = new SocketIO($this->config('socket_io_port', 7000), $this->config('socket_io_context', []));

        $this->io->on('workerStart', [$this, 'onWorkerStart']);
        $this->io->on('workerStop', [$this, 'onWorkerStop']);
        $this->io->on('connection', [$this, 'onConnection']);

        $pid_file = $this->config('pid_file');
        $log_file = $this->config('log_file');
        $stdout_file = $this->config('stdout_file');

        $pid_file && Worker::$pidFile = $pid_file;
        $log_file && Worker::$logFile = $log_file;
        $stdout_file && Worker::$stdoutFile = $stdout_file;

        $this->global['io'] = $this->io;

        Worker::runAll();
    }

    /**
     * 进程启动时的回调函数
     */
    public function onWorkerStart()
    {
        $work_url = $this->config('worker_url', 'http://0.0.0.0:7001');

        if ($work_url) {
            $work = new Worker($work_url, $this->config('worker_context', []));

            $class = $this->config('worker_handler');

            if ($class) {
                $service = new $class($this->global);
            } else {
                $service = new DefaultWorker($this->global);
            }

            $work->onWorkerStart = [$service, 'onWorkerStart'];
            $work->onConnect = [$service, 'onConnect'];
            $work->onMessage = [$service, 'onMessage'];
            $work->onClose = [$service, 'onClose'];
            $work->onError = [$service, 'onError'];
            $work->onBufferFull = [$service, 'onBufferFull'];
            $work->onBufferDrain = [$service, 'onBufferDrain'];
            $work->onWorkerStop = [$service, 'onWorkerStop'];
            $work->onWorkerReload = [$service, 'onWorkerReload'];

            $work->listen();
        }
    }

    /**
     * 当有客户端连接时
     */
    public function onConnection($socket)
    {
        isset($this->global['connect_nums']) ? $this->global['connect_nums']++ : $this->global['connect_nums'] = 1;

        $this->global['sockets'][$socket->id] = $socket;

        $class = $this->config('message_handler');

        if ($class) {
            $service = new $class($socket->id, $this->global);
        } else {
            $service = new DefaultMsg($socket->id, $this->global);
        }

        $ref = new \ReflectionClass(get_class($service));
        $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        if ($methods) {
            foreach ($methods as $method) {
                if ($method->isConstructor() || $method->isDestructor()) {
                    continue;
                }
                $method_name = $method->getName();
                if ($method_name) {
                    $socket->on($method_name, [$service, $method_name]);
                }
            }
        }
    }

    /**
     * 进程结束时的回调函数
     */
    public function onWorkerStop()
    {

    }

    /**
     * 获取配置
     */
    public function config($name, $default = null)
    {
        return config("ps.{$this->service_name}.{$name}", $default);
    }
}