<?php
$redis = new \Redis();
$redis->connect('127.0.0.1',6379);
//var_dump($redis->flushAll());
//---
$redisKey = 'onlin_list';

$redis->get($redisKey);
//---

//-----
//$listKey = 'list_user';
//// 取list
//$list = $redis->lrange($listKey,0,-1);
//var_dump($list);
//-----

//$redisKey = 'online_list';
//----------
//$setKey = 'chatMsg';
//
//$redis->sAdd($setKey,2);
//
//$setFd = $redis->sMembers($setKey);
//var_dump($setFd);
//---------------

//$userlist = [
//    'client_id'=>2,
//    'username'=> '222'
//];
//$userlist = json_encode($userlist,JSON_UNESCAPED_UNICODE);
//$redis->set($redisKey,$userlist);
//
//var_dump(json_decode($redis->get($redisKey),true));


// hash
//$fd = 1;
//$hashKey = 'user_'.$fd;
//$redis->hset($hashKey,'client_id',$fd);
//$redis->hset($hashKey,'username','yyy');
//$redis->hset($hashKey,'is_online',1);
//
//
//$listKey = 'list_user';
//$redis->rpush($listKey,$hashKey);
////
//$list = $redis->lrange($listKey,0,-1);
////
//var_dump($list);
//$arr = [];
//foreach($list as $v){
//    $data = $redis->hgetall($v);
//    array_push($arr,$data);
//}
//var_dump($arr);
