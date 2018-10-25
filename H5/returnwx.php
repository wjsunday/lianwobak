<?php

function qq(){
	$state=$_GET['state'];
	$code = $_GET["code"];
	$appid = 1106231250;
	$appkey = "DrFyA6RfNQ39Ordw";
	$REDIRECT_URI='http://m.lianwoapp.com/H5/qqOauth.php';
	if($_GET['state'] != $state){
    	echo '参数错误';
    }
	$get_token_url = 'https://graph.z.qq.com/moc2/token?client_id='.$appid.'&client_secret='.$appkey.'&grant_type=authorization_code&code='.$code;
	$json_obj = curl($get_token_url);
	var_dump($json_obj);
}

function weixin(){
	$state=$_GET['state'];
	$code = $_GET["code"];
	$appid = "wx6c89bdbb3c7b1a90";
	$secret = "b0ac6a51c9353471f5023d05661d2b80";
	for ($i=0;$i<3;$i++){
		$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
		$json_obj = curl($get_token_url);
		$access_token = $json_obj['access_token'];
		$openid = $json_obj['openid'];

		$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		$user_obj = curl($get_user_info_url);
		if ($user_obj) break;
	}
	$login = login($user_obj['openid'],$json_obj['access_token'],$json_obj['refresh_token'],$json_obj['expires_in']);
	if($login['uid'] && $login['uid'] > 0)
	{
		$index_bonus = appIndexBonus($login['oauth_token'],$login['oauth_token_secret']);
		$range_type = rangeType($login['oauth_token'],$login['oauth_token_secret']);
		// include_once('bonus.html');
		$bonusList = bonusList($login['oauth_token'],$login['oauth_token_secret']);
		include('bonus.html');
		
		
	}else{
		$register = register($user_obj['nickname'],$user_obj['openid'],$json_obj['access_token'],$json_obj['refresh_token'],$json_obj['expires_in']);
		$range_type = rangeType($register['oauth_token'],$register['oauth_token_secret']);
		$bonusList = bonusList($register['oauth_token'],$register['oauth_token_secret']);
		if($register['uid'] && $register['uid'] > 0){
			include('bonus.html');
		}else{
			include('index.php');
		}
		
	}
}
	

// echo $login;

function curl($url)
{
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_HEADER,0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	$res = curl_exec($ch);
	curl_close($ch);
	$json_obj = json_decode($res,true);
	return $json_obj;
}

function register($uname,$type_uid,$access_token,$refresh_token,$expire_in){
	$registerUrl = '112.74.46.91/api.php?mod=Oauth&act=bind_new_user&uname='.$uname.'&password=66666666&type=weixin&type_uid='.$type_uid.'&access_token='.$access_token.'&refresh_token='.$refresh_token.'&expire_in='.$expire_in;
	$json_obj = curl($registerUrl);
	return $json_obj;
}

function login($type_uid,$access_token,$refresh_token,$expire_in){
	$loginUrl = '112.74.46.91/api.php?mod=Oauth&act=get_other_login_info&type=weixin&type_uid='.$type_uid.'&access_token='.$access_token.'&refresh_token='.$refresh_token.'&expire_in='.$expire_in.'&openid='.$type_uid;
	$json_obj = curl($loginUrl);
	return $json_obj;
}

function bonusList($oauth_token,$oauth_token_secret,$range_type,$latitude,$longitude){
	$url = '112.74.46.91/api.php?mod=Bonus&act=getBonusData&oauth_token='.$oauth_token.'&oauth_token_secret='.$oauth_token_secret.'&range_type='.$range_type.'&page=1&h5=1';
	$json_obj = curl($url);
	return $json_obj;
}

function rangeType($oauth_token,$oauth_token_secret){
	$url = '112.74.46.91/api.php?mod=Bonus&act=getIndexBonusList&oauth_token='.$oauth_token.'&oauth_token_secret='.$oauth_token_secret;
	$json_obj = curl($url);
	return $json_obj;
}

function appIndexBonus($oauth_token,$oauth_token_secret){
	$url = '112.74.46.91/api.php?mod=Bonus&act=appIndexBonusH5&oauth_token='.$oauth_token.'&oauth_token_secret='.$oauth_token_secret;
	$json_obj = curl($url);
	return $json_obj;
}

?>