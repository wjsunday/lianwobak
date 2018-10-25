<?php
$state=$_GET['state'];
$code = $_GET["code"];
$APPID='wx6c89bdbb3c7b1a90';
$REDIRECT_URI='http://m.lianwoapp.com/H5/weixin.php';
// $scope='snsapi_base';
$scope='snsapi_userinfo';
$url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$APPID.'&redirect_uri='.urlencode($REDIRECT_URI).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
header("Location:".$url);
?>