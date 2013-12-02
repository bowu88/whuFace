<?php 
$server='localhost';
$username='root';
$password='';
$database='students';
//$conn=mysql_connect($server,$username,$password)
//or die("无法连接mysql");
//mysql_select_db($database)
//or die("无法打开mysql");
//mysql_query('set names utf8'); 
//$str0="select XH,XM,ZJHM from stu2011";
//$result0=mysql_query($str0);
$cookie_file = dirname(__FILE__).'/cookie.txt'; 
		//先获取cookies并保存
		//$url = "http://my.whu.edu.cn/userPasswordValidate.portal?Login.Token1=2013286190138&Login.Token2=21001X";
		//$ch = curl_init($url); //初始化
		//curl_setopt($ch, CURLOPT_HEADER, 0); //不返回header部分
		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，而非直接输出
		//curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
		//curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 );
		//curl_exec($ch);
		//curl_close($ch);
        //$count=0;
        //while($row = mysql_fetch_array($result0)){
        $XH = '2013286190137';
        $XM = '21001X';
        $picaddr="F:/".$XH.$XM.".jpg";
        $pic="http://my.whu.edu.cn/attachmentDownload.portal?notUseCache=true&type=userPhoto&ownerId=$XH";
        $ch2=curl_init($pic);
        curl_setopt($ch2, CURLOPT_HEADER, 0);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt ( $ch2, CURLOPT_TIMEOUT, 10 );
        $imageData=curl_exec($ch2);
        curl_close($ch2);
        //echo "test\n";
        echo strlen($imageData) . "\n";
        echo $imageData;
        $picaddr="F:/".$XH.$XM.".jpg";
        $count++;
        if(!strpos($imageData, '<body>')){
            $tp = @fopen($picaddr, 'wb');
            fwrite($tp, $imageData);
            fclose($tp);
        }
        echo  $XH.'done'.$count.'/88027'.chr(13).chr(10);
        //}
?>
