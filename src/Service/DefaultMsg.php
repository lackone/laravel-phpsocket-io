<?php

namespace Lackone\LaravelPhpsocketIo\Service;

class DefaultMsg
{
    public $socket_id = 0;
    public $global = [];

    public function __construct($socket_id, &$global)
    {
        $this->socket_id = $socket_id;
        $this->global = &$global;
    }

    /**
     * 登录
     * @param $data
     */
    public function login($data)
    {
        //这里的uid由前端传递，当然也可以后端通过IP和UA进行生成
        if (!$data['uid']) {
            return;
        }

        $uid = (string)$data['uid'];

        if (!isset($this->global['online_users'][$uid])) {
            isset($this->global['online_nums']) ? $this->global['online_nums']++ : $this->global['online_nums'] = 1;
        }

        $this->global['online_users'][$uid] = [
            //可以添加自定义数据
            'uid' => $uid,
            'login_time' => time(),
        ];

        $this->global['sockets'][$this->socket_id]->uid = $uid;
        //把当前连接加入到uid分组，方便后面推送数据
        $this->global['sockets'][$this->socket_id]->join($uid);

        $res = [
            'from' => $uid,
            'to' => -1,
            'data' => '',
            'type' => 'login',
            'timestamp' => time(),
        ];

        $this->global['sockets'][$this->socket_id]->broadcast->emit('message', $res);
    }

    /**
     * 当前的在线信息
     */
    public function onlineInfo($data)
    {
        $res = [
            'from' => -1,
            'to' => -1,
            'data' => [
                'connect_nums' => $this->global['connect_nums'] ?? 0,
                'online_nums' => $this->global['online_nums'] ?? 0,
                'online_users' => $this->global['online_users'] ?? [],
            ],
            'type' => 'online_info',
            'timestamp' => time(),
        ];

        $this->global['sockets'][$this->socket_id]->emit('message', $res);
    }

    /**
     * 关闭页面
     * @param $data
     */
    public function disconnect($data)
    {
        isset($this->global['connect_nums']) && $this->global['connect_nums']--;

        if (isset($this->global['sockets'][$this->socket_id]->uid)) {
            isset($this->global['online_nums']) && $this->global['online_nums']--;

            $this->global['sockets'][$this->socket_id]->leave($this->global['sockets'][$this->socket_id]->uid);

            unset($this->global['online_users'][$this->global['sockets'][$this->socket_id]->uid]);

            $res = [
                'from' => $this->global['sockets'][$this->socket_id]->uid,
                'to' => -1,
                'data' => '',
                'type' => 'leave',
                'timestamp' => time(),
            ];

            $this->global['sockets'][$this->socket_id]->broadcast->emit('message', $res);
        }

        unset($this->global['sockets'][$this->socket_id]);
    }
}