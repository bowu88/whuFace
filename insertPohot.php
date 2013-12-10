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
//echo $n . "\n";

// store the face_ids obtained by detection/detect API
$face_ids = array();
// register new people, detect faces
foreach ($person_names as $person_name)
    detect($api, $person_name, $face_ids);
    
$group = "sample_group";
create_group($api, $group, $person_names);
train($api, $group);
    
//identify($api, $person_names[0], $group);

/* 
 *	create new person, detect faces from person's image_url
 */
function detect(&$api, $person_name, &$face_ids) {
    // obtain photo_url to train
    //$filePath = getTrainingFile($person_name);
    //$fileContent = fread(fopen($filePath, "r"), filesize($filePath));
    //echo $fileCotent;
     //detect faces in this photo
    $url = getTrainingUrlCet($person_name);
    $result = $api->face_detect($url);
    //$result = $api->face_detect_post($fileContent);
    // skip errors
    if (empty($result->face)) return false;
    // skip photo with multiple faces (we are not sure which face to train)
    if (count($result->face) > 1) return false;

    // obtain the face_id
    $face_id = $result->face[0]->face_id;
    $face_ids[] = $face_id;
    // delete the person if exists
    //$api->person_delete($person_name);
    // create a new person for this face
    //$api->person_create($person_name);
    // add face into new person
    $api->person_add_face($face_id, $person_name);
}

/*
 *	train identification model for group
 */
function train(&$api, $group_name) {
   	// train model
   	$session = $api->train_identify($group_name);
    if (empty($session->session_id)) {
        // something went wrong, skip
        return false;
    }
    $session_id = $session->session_id;
    // wait until training process done
    while ($session=$api->info_get_session($session_id)) {
        sleep(1);
        if (!empty($session->status)) {
            if ($session->status != "INQUEUE")
                break;
        }
    }
	// done
    return true;
}

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
	foreach ($face->candidate as $candidate) 
		echo "$candidate->person_name was found in $group_name with ".
        "confidence $candidate->confidence\n";
}

/*/
 *	generate a new group with group_name, add all people into group
 */
function create_group(&$api, $group_name, $person_names) 
{
	// delete the group if exists
	//$api->group_delete($group_name);
	// create new group
	//$api->group_create($group_name);
   	// add new person into the group
	foreach ($person_names as $person_name)
	   	$api->group_add_person($person_name, $group_name);
}

/*
 *	return the train data(image_url) of $person_name
 */
function getTrainingFile($person_name) {
    // TODO: here is just the fake url
	//return "http://cn.faceplusplus.com/wp-content/themes/faceplusplus.zh/assets/img/demo/".$person_name.".jpg";
    return 'E:\\www\\whuFace\\photo\\my\\' . $person_name . '_my.jpg';
}

function getTrainingUrlMy($person_name) {
    global $person_card;
    //return "http://202.114.74.136/pic/{$person_card[$person_name]}{$person_name}.jpg"; //cte
    return "http://acm.whu.edu.cn/xioumu/gg/{$person_name}_my.jpg";
}
function getTrainingUrlCet($person_name) {
    global $person_card;
    return "http://202.114.74.136/pic/{$person_card[$person_name]}{$person_name}.jpg"; //cte
    //return "http://acm.whu.edu.cn/xioumu/gg/{$person_name}_my.jpg";
}


/*
 *	return the photo_url of $person_name to identify for
 */
function getPhotoFile($person_name) {
    // TODO: here is just the fake url
    return 'E:\\www\\whuFace\\photo\\my\\' . $person_name . '_my.jpg';
}



?>

