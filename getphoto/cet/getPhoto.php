<meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
<?php
include("config.php"); 
ini_set ('memory_limit', '128M');

$order = mysql_query("
    SELECT p.XH, c.card 
    FROM card_user c, photo_info p 
    WHERE cetOK <> 'OK' AND c.XH = p.XH
    ORDER BY p.XH ");

//main
while ($row = mysql_fetch_array($order)) {
    $XH = $row['XH'];
    $card = $row['card'];
    //echo $XH . " " . $card . "\n";
    $len = strlen($card);
    if ($len < 6) continue;
    //$XH = '2011302580311';
    //$card = '230803199206150810';
    do {
        echo $XH . ".......\n";
        $url = "http://202.114.74.136/pic/{$card}{$XH}.jpg";
        $photo = get($url, 0);
        if (strlen($photo) == 0) {
            echo "------------> LOST! \n";
            continue;
        }
        else if (strlen($photo) === 1163) {
            echo "------------> NO! \n";
            saveMarkCet($XH, 'NO');
        }
        else {
            file_put_contents(dirname(__FILE__) . '\\photo\\' . $XH . '_cet.jpg', $photo);
            echo "------------> OK! \n";
            saveMarkCet($XH, 'OK');
        }    
    }while (strlen($photo) == 0);
    //break;
}

function saveMarkCet($XH, $mark) {
    if ( !mysql_query("UPDATE photo_info 
                        SET cetOK = '{$mark}'
						WHERE XH = {$XH}") ) {
			echo "Can't save mark!\n";
	}
}

function deal_output($output) {
    if (strlen($output) == 119) {
        //echo "---->NO<----\n";
        return 0;
    }
    else if (strlen($output) == 83) {
        //echo "---->YES<----\n";
        return 1;
    }
	else return -1;
}

function get($url, $type){
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_HEADER,$type==1?true:false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch,CURLOPT_COOKIEFILE,$request_cookie);
        //curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.83 Safari/537.1');
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
        //echo $request_cookie . " " . $type . "\n";
        $response = $type===1 ? get_response(curl_exec($ch)) : curl_exec($ch);
        curl_close($ch);
        return $response;
}


function get_response($data){
        list($header,$body)=explode("\r\n\r\n",$data);
        preg_match("/set\-cookie:([^\r\n]*)/i",$header,$matches);
        if($matches!=NULL)
                return array('body'=>$body,'cookie'=>$matches[1]);
        return array('body'=>$data,'cookie'=>'');
}


function send_post($textgzh, $password) {
		$cookie_file = dirname(__FILE__) . '/cookie.txt';  //先获取cookies并保存
		$curlPost = 'Login.Token1=' . urlencode($textgzh).
					'&Login.Token2=' . urlencode($password);
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
		curl_close($ch);
		return $output;
}

?>


