<?php

return [
    //phpsocket.io机制决定的，只能单进程，可以多端口多实例的方式开多进程，根据ip代理到固定的对应的端口上
    //可以配置多个service_name的配置，然后进行代理
    //ps default1 start --d
    //ps default2 start --d
    //ps default3 start --d
    'service_name' => 'default',

    'default' => [
        'pid_file' => storage_path('logs/pid.log'),
        'log_file' => storage_path('logs/log.log'),

        //SocketIO的端口
        'socket_io_port' => 6000,
        'socket_io_handler' => \Lackone\LaravelPhpsocketIo\Service\DefaultSocketIO::class,
        'socket_io_context' => [
            //SocketIO限制连接域名，多个域名用空格
            'origins' => '',
            //'ssl' => [
            //    'local_cert' => '',
            //    'local_pk' => '',
            //    'verify_peer' => false,
            //],
        ],
        //默认的worker处理类
        'worker_url' => 'http://0.0.0.0:6001',
        'worker_handler' => \Lackone\LaravelPhpsocketIo\Service\DefaultWorker::class,
        'worker_context' => [
            //'ssl' => [
            //    'local_cert' => '',
            //    'local_pk' => '',
            //    'verify_peer' => false,
            //],
        ],
        //默认的事件处理类
        'message_handler' => \Lackone\LaravelPhpsocketIo\Service\DefaultMsg::class,
    ],
];