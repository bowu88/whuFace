<meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
<?php
include("config.php"); 
ini_set ('memory_limit', '128M');



function send_post($textgzh, $password) {
		$cookie_file = dirname(__FILE__) . '/cookie.txt';  //先获取cookies并保存
		$curlPost = 'Login.Token1=' . urlencode($textgzh).
					'&Login.Token2=' . urlencode($password). 
				    '&goto=http%3A%2F%2Fmy.whu.edu.cn%2FloginSuccess.portal&gotoOnFail=http%3A%2F%2Fmy.whu.edu.cn%2FloginFailure.portal';
		//echo $curlPost . "<hr />";
		$url = "http://my.whu.edu.cn/userPasswordValidate.portal";
		$ch = curl_init($url); //初始化
		curl_setopt($ch, CURLOPT_HEADER, 0); //不返回header部分
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，而非直接输出
		curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
		curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15 );
		$output=curl_exec($ch);
		////curl_close($ch);
		return $output;
}

?>


