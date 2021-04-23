<?php

namespace Lackone\LaravelPhpsocketIo\Service;

class DefaultWorker
{
    public $global = [];

    public function __construct(&$global)
    {
        $this->global = &$global;
    }

    /**
     * 处理消息
     * @param $connection
     * @param $message
     * @return mixed
     */
    public function onMessage($connection, $request)
    {
        $params = $request->post() ? $request->post() : $request->get();
        switch (@$params['type']) {
            case 'all':
                $res = [
                    'from' => $params['from'] ?? -1,
                    'to' => $params['to'] ?? -1,
                    'data' => $params['data'] ?? '',
                    'type' => 'all',
                    'timestamp' => time(),
                ];
                $this->global['io']->emit('message', $res);
                break;
            case 'one':
                $to = $params['to'] ?? -1;
                if ($to != -1) {
                    $res = [
                        'from' => $params['from'] ?? -1,
                        'to' => $to,
                        'data' => $params['data'] ?? '',
                        'type' => 'one',
                        'timestamp' => time(),
                    ];
                    $this->global['io']->to($to)->emit('message', $res);
                }
                break;
            default:
                break;
        }
        return $connection->send('fail');
    }

    public function onWorkerStart($worker)
    {
    }

    public function onConnect($connection)
    {
    }

    public function onClose($connection)
    {
    }

    public function onWorkerStop($worker)
    {
    }

    public function onError($connection, $code, $msg)
    {
    }

    public function onBufferFull($connection)
    {
    }

    public function onBufferDrain($connection)
    {
    }

    public function onWorkerReload($worker)
    {
    }
}