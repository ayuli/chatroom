<?php

//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9502);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
});

/**
 * 监听WebSocket消息事件
 * @param $frame->data jsonobj 接受数据
 * @param $type string 类型-login-message-loginout
 * @param $username string 用户名
 * @param $content string 消息内容
 * @param $frame->fd int 用户标识
 *
 * @return $client_id int 用户标识
 * @return $username string 用户名
 */
$ws->on('message', function ($ws, $frame) {

    $data = json_decode($frame->data,true); // 接受消息
    $type = $data['type'];
    $fd = $frame->fd;
    //redis
    $redis = new \Redis();
    $redis->connect('127.0.0.1',6379);

    if($type == 'login'){

        $username = $data['username'];
        login($redis,$ws,$fd,$username);

    }elseif($type == 'chatMsg'){

        $content = $data['content'];
        chatMsg($redis,$ws,$fd,$content);

    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    //redis
    $redis = new \Redis();
    $redis->connect('127.0.0.1',6379);

    loginout($ws,$redis,$fd);  // 关闭
});

$ws->start();


//--------- function ----------

// 关闭
function loginout($ws,$redis,$fd){

    $redisKey = 'onlin_list';
    $list = getRedisList($redis,$redisKey); //取
    // 删除fd
    foreach($list as $k => $v){
        if($v['client_id'] == $fd){
            unset($list[$k]);
            $username = $v['username'];
        }
    }
    setRedisList($redis,$redisKey,$list); //存

    $q_msg = sendMsg('loginout',0,$username);
    $q_res = json_encode($q_msg,JSON_UNESCAPED_UNICODE);
    foreach($list as $k => $v){
        $ws->push($v['client_id'],$q_res);
    }
}

// 聊天消息
function chatMsg($redis,$ws,$fd,$content){
// 标识
    $redisKey = 'onlin_list';
    $list = getRedisList($redis,$redisKey); //取

    foreach($list as $k => $v){
        if($v['client_id'] == $fd){
            $username = $v['username'];
        }
    }
    $q_msg = sendMsg('chatMsg',0,$username,$content);
    $q_res = json_encode($q_msg,JSON_UNESCAPED_UNICODE);
    foreach($list as $k => $v){
        if($v['client_id'] != $fd){
            $ws->push($v['client_id'],$q_res);
        }
    }

    $b_msg = sendMsg('chatMsg',1,$username,$content);
    $b_res = json_encode($b_msg,JSON_UNESCAPED_UNICODE);
    $ws->push($fd,$b_res);
}

// 登录
function login($redis,$ws,$fd,$username)
{
    // 标识
    $redisKey = 'onlin_list';
    $list = getRedisList($redis,$redisKey); // 取

    if(empty($list)){
        $list = [];
    }else{
        $q_msg = sendMsg('login',0,$username);
        $q_res = json_encode($q_msg,JSON_UNESCAPED_UNICODE);
        foreach($list as $k => $v){
            $ws->push($v['client_id'],$q_res);  // send push
        }
    }

    $list[] = [
        'client_id' => $fd,
        'username' => $username
    ];

    setRedisList($redis,$redisKey,$list); //存

    $b_msg = sendMsg('login',1,$username);

    $b_res = json_encode($b_msg,JSON_UNESCAPED_UNICODE);
    $ws->push($fd,$b_res);
}
 //  -----common
function sendMsg($type,$is_me,$username,$content=null)
{
    $msg = [
        'type' => $type,
        'is_me' => $is_me,
        'username'=>$username,
    ];
    if(!is_null($content)){
        $msg['content'] = $content;
    }
    return $msg;
}

function getRedisList($redis,$redisKey)
{
    return json_decode($redis->get($redisKey),true);
}

function setRedisList($redis,$redisKey,$userList)
{
    $jsonStr = json_encode($userList,JSON_UNESCAPED_UNICODE);
    $redis->set($redisKey,$jsonStr);
}

