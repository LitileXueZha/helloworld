/*
*  测试WebSocket协议
*  Create at 2017.6.4 by Tao from http://www.cnblogs.com/LXJ416/p/5629038.html
*/

<?php
  echo "dddd";
  class WS{
    // 初始化：连接server的client，不同状态的socket管理，是否握手了(只有握手了才表示连接成功)

    function _construct($address, $port){
      $this-> master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create()失败了！");
      socket_set_option($this-> master, SOL_SOCKET, SO_REUSEADDR, 1) or die("socket_set_option()失败了！");
      socket_bind($this-> master, $address, $port) or die("socket_bind()失败了！");
      socket_listen($this-> master, 2) or die("socket_listen()失败了！");

      $this-> sockets[] = $this-> master;

      while(true){
        $write = null;
        $except = null;
        socket_select($this-> sockets, $write, $except, NULL);

        foreach($this-> sockets as $socket){
          if($socket == $this-> master){
            // 判断为连接主机的client
            $client = socket_accept($this-> socket);
            if($client < 0){
              // 这是调试
              echo "socket_accept()失败了！重试ing";
              continue;
            } else {
              // 添加这个到sockets管理集中
              array_push($this-> sockets, $client);
            }
          } else {
            $bytes = @socket_recv($socket, $buffer, 2048, 0);
            print_r($buffer);

            // 读取的字节为零，无传输，退出
            if($bytes == 0) return;
            if(!$this-> handShake){
              // 判断是否握手了，
              $this-> doHandShake($socket, $buffer);
              echo "我们正在握手。。。\n";
            } else {
              // 已经握手，进行数据处理
              $buffer = $this-> decode($buffer);
              echo "Yeah，成功了";
            }
          }
        }
      }
    }

    // 进行握手交易
    function doHandShake($socket, $req){
      $acceptKey = $this-> encrypt($req);
      $upgrade = "HTTP/1.1 101 Switching Protocols\r\n".
                 "Upgrade: websocket\r\n".
                 "Connection: Upgrade\r\n".
                 "Sec-WebSocket-Accept: $acceptKey\r\n".
      // echo "来啊，握手啊。{ $upgrade.chr(0) }";

      // 写入并标记握手成功
      socket_write($socket, $upgrade.chr(0), strlen($upgrade.chr(0)));
      $this-> handShake = true;
    }

    // 加密key字段
    function encrypt($req){
      // 提取key字段
      $key = null;
      if(preg_match("/Sec-WebSocket-Accept: (.*)\r\n/", $req, $match)){
        $key = $match[1];
      }

      $mask = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
      return base64_encode(sha1($key.$mask, true));
    }

    // 解析数据帧
    function decode($buffer){
      $len = $masks = $data = $decoded = null;
      $len = ord($buffer[1]) & 127;
      if($len === 126)  {
        $masks = substr($buffer, 4, 4);
        $data = substr($buffer, 8);
      } else if ($len === 127)  {
        $masks = substr($buffer, 10, 4);
        $data = substr($buffer, 14);
      } else  {
        $masks = substr($buffer, 2, 4);
        $data = substr($buffer, 6);
      }
      for ($index = 0; $index < strlen($data); $index++) {
        $decoded .= $data[$index] ^ $masks[$index % 4];
      }

      return $decoded;
    }
  }

$ws = new WS("localhost", 80);