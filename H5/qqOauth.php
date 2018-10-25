<?php
$state=md5(uniqid(rand(), TRUE));
$code = $_GET["code"];
$APPID=1106231250;
$REDIRECT_URI='http://m.lianwoapp.com/H5/qq.php';
// $scope='snsapi_base';
$display='mobile';
$url='https://graph.qq.com/oauth2.0/authorize?client_id='.$APPID.'&redirect_uri='.urlencode($REDIRECT_URI).'&response_type=code&display='.$display.'&state='.$state;
header("Location:".$url);
?>