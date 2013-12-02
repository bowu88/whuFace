
<meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
<?php
/*
 * 把一个表中的信息转移到另一表里
*/
include("config.php"); 
ini_set ('memory_limit', '128M');

$order = mysql_query("SELECT * FROM aaa_sy ORDER BY XH");
while ($row_order = mysql_fetch_array($order) )  {
    $select_s = "SELECT XH FROM photo_info WHERE XH = '" .  $row_order['XH'] . "'";
    $insert_s = "INSERT INTO photo_info (XH) VALUES ('" .  $row_order['XH'] . "')"; 
    //echo $select_s . "\n";
    //echo count($row_order) . "\n";
    if (strlen($row_order['XH']) != 13) {
        //echo strlen($row_order['XH']) . " CONTINUE!\n";
        continue;
    }
    echo $row_order['XH'] . '-------------------->';
    //while (1);
    $select_res = mysql_query($select_s) or die("SQL error: " . $select_s . mysql_error());
    if (mysql_num_rows($select_res) == 0) {
        mysql_query($insert_s) or die("SQL error: " . $insert_s . mysql_error());
        echo "OK!\n";
    }
    else {
        //echo $select_res;
        //echo "count = " . count($select_res) . "\n";
        echo "NO: exist!\n";
    }
}

function sqlRrror($info) {
    echo "Can't execute" . $info . "\n";
}

?>


