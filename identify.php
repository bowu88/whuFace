<?php
// load php_client_demo, this can be downloaded from our website(http://us.faceplusplus.com/dev/others/sdks/)
require_once(__DIR__ . "/FacePPClientDemo.php");
include("config.php"); 

// your api_key and api_secret
$api_key = "8efd663edcabaf4d0b58c6453f0da7fc";
$api_secret = "CWHKKnalNIReEJdPIshC2iGBEDMDQg3P";
// initialize client object
// 
$order = mysql_query("
    SELECT p.XH, c.card 
    FROM card_user c, photo_info p 
    WHERE cetOK = 'OK' AND c.XH = p.XH and p.XH LIKE '2011302580%'
    ORDER BY p.XH ");

$api = new FacePPClientDemo($api_key, $api_secret);

// the list of person_name to train and identify for
$n = 0;
$person_names = array();
$person_card = array();
while ($row = mysql_fetch_array($order)) {
    $person_names[$n++] = $row['XH'];
    $person_card[$row['XH']] = $row['card'];
}
$group = "sample_group";
identify($api, $person_names[0], $group);

/*
 *	identify a person in group
 */
function identify(&$api, $person_name, $group_name)
{
	// obtain photo_url to identify
	$url = getPhotoUrl($person_name);
	
	// recoginzation
	$result = $api->recognition_identify($url, $group_name);
	
	// skip errors
	if (empty($result->face)) return false;
	// skip photo with multiple faces
	if (count($result->face) > 1) return false;
	$face = $result->face[0];
	// skip if no person returned
	if (count($face->candidate) < 1) return false;
		
    // print result
    echo "<br>";
	foreach ($face->candidate as $candidate)  {
		echo "$candidate->person_name was found in $group_name with ".
        "confidence $candidate->confidence <br>";
    }
    echo '<br>';
    echo '<img src=\''. getPhotoUrl(1) . '\'><br>';
    foreach ($face->candidate as $candidate)  {
        echo '<img src=\''. getTrainingUrl($candidate->person_name) . '\'>';
    }
}

function getFile($person_name) {
    // TODO: here is just the fake url
	//return "http://cn.faceplusplus.com/wp-content/themes/faceplusplus.zh/assets/img/demo/".$person_name.".jpg";
    return 'photo\cet\\' . $person_name . '_cet.jpg';
}

function getTrainingUrl($person_name) {
    global $person_card;
    //return "http://202.114.74.136/pic/{$person_card[$person_name]}{$person_name}.jpg";
    //return "http://202.114.74.136/pic/{$person_card[$person_name]}{$person_name}.jpg";
    return "http://acm.whu.edu.cn/xioumu/gg/{$person_name}_my.jpg";
}

/*
 *	return the photo_url of $person_name to identify for
 */
function getPhotoUrl($person_name) {
    // TODO: here is just the fake url
    //return "http://hdn.xnimg.cn/photos/hdn421/20120325/2320/large_RB2E_16746b019117.jpg";
    //return "http://fmn.rrimg.com/fmn064/20120804/2130/original_Hnvu_794d0000334d118e.jpg";
    //return "http://202.114.74.136/pic/4310031992102465532011302580362.jpg";
    //return "http://b75.photo.store.qq.com/psu?/968c9d62-75f4-4cb2-9f29-e58e41941c33/V7yp8.82phhJNs69jaP*RjHyyTHjRifQQ4EGZCVq9rQ!/b/YTDhxCzUJAAAYvUxtyzpJAAA&bo=ngL2AQAAAAABAEw!&rf=photoDetail";
    //return "http://hdn.xnimg.cn/photos/hdn221/20130409/1715/h_large_UeDp_2c1800000e30113e.jpg";
    return $_GET["url"];
}

?>
