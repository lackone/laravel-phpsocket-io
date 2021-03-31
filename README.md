# laravel-phpsocket-io
为了在laravel中方便的使用phpsocket.io，于是基于phpsocket.io写了一个扩展。

## 一、安装

```
$ composer require lackone/laravel-phpsocket-io
```

## 二、配置
1、在 config/app.php 注册 ServiceProvider (Laravel5.5+无需手动注册)

```
'providers' => [
    // ...
    Lackone\LaravelPhpsocketIo\Providers\PhpSocketIOServiceProvider::class,
];
```

2、创建配置文件

```
php artisan vendor:publish --provider="Lackone\LaravelPhpsocketIo\Providers\PhpSocketIOServiceProvider"
```

3、修改配置文件

根据需要修改 config/ps.php 中的配置即可 。

## 三、使用
创建一个用于处理消息的文件，比如 msg.php ，存放目录随意。

然后继承 Lackone\LaravelPhpsocketIo\Service\DefaultMsg 类。

DefaultMsg 类中默认有几个方法，当然你也可以覆写父类方法自已实现。
```
class Msg extends DefaultMsg
{
    //方法名就是 $socket->on('方法名', function() {})
    public function test($msg) 
    {
        dump($msg);
        //消息处理
        
        $this->global['sockets'][$this->socket_id]->emit('message', date('YmdHis'));
    }
}
```
然后在 config/ps.php 中把你自已的写类配置到 message_handler 中。

```
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
<button id="test">test</button>
<script src='./socket.io.js'></script>
<script>
    // 如果服务端不在本机，请把127.0.0.1改成服务端ip
    var socket = io('http://127.0.0.1:7000');
    // 当连接服务端成功时触发connect默认事件
    
    socket.on('connect', function () {
        console.log('连接成功');
    });

    socket.on('message', function (msg) {
        console.log(msg);
    });

    document.getElementById("test").onclick = function() {
        socket.emit('test', Math.random());
    };
</script>
</body>
</html>
```

## 四、启动
```
php artisan ps default start
```
config/ps.php 中可以配置多个配置项