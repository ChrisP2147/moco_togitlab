<?php
require "vendor/autoload.php";
require_once('conn.inc.php');

$moco_token = "53a856de73a8b8b0a82aa7a604026747";
$gitlab_token = "Vb23WYp2KmxvPG4xVRhB";

// delete_ticket_check_db();
$array = load_all_offers_data_array();
var_dump($array);
insert_offer_into_db();

function load_all_offers_data_array()
{
    global $moco_token, $all_item_ids;
    $all_item_ids = array();
    $url = 'https://cp.mocoapp.com/api/v1/offers';
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
    $offer_array = (array)json_decode($response, true);

    for ($i = 0; $i < count($offer_array); $i++)
    {  
        if ($offer_array[$i]['status'] != "created"){
            $all_offers_array[] = $offer_array[$i]['id'];
        }
        
    }
    for ($i = 0; $i < count($all_offers_array); $i++)
    {
        $url = 'https://cp.mocoapp.com/api/v1/offers/'.$all_offers_array[$i];
        $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
        $offer_items_array = (array)json_decode($response, true);

        $tmp_array[] = $offer_items_array;
    }

    for ($i = 0; $i < count($tmp_array); $i++)
    {
        for ($j = 0; $j < count($tmp_array[$i]['items']); $j++)
        {
            $all_item_ids['id'][] = $tmp_array[$i]['items'][$j]["id"];
            if ($tmp_array[$i]['items'][$j]["title"] == null){
                $tmp_array[$i]['items'][$j]["description"] = str_replace("&nbsp;", '', $tmp_array[$i]['items'][$j]["description"]);
                $all_item_ids['title'][] = strip_tags($tmp_array[$i]['items'][$j]["description"]);
            }
            else{
                $tmp_array[$i]['items'][$j]["title"] = str_replace("&nbsp;", '', $tmp_array[$i]['items'][$j]["title"]);
                $all_item_ids['title'][] = strip_tags($tmp_array[$i]['items'][$j]["title"]);
            }
        }
    }
    return $all_item_ids;
}

function insert_offer_into_db()
{
    global $all_item_ids;
    $pdo = connect_DB_pdo();
    for ($i = 0; $i < count($all_item_ids['id']); $i++)
    {
    // insert all Offer Positions und IDs into database
    $sql = 'INSERT INTO tickets_check(ticket_id, ticket_title) values(?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$all_item_ids['id'][$i], $all_item_ids['title'][$i]]);
    }

}

function delete_ticket_check_db()
{
    $pdo = connect_DB_pdo();
    $sql = 'DELETE from tickets_check';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

function connect_DB_pdo()
{
    global $host, $dbname, $user, $password;
    $dsn = 'mysql:host='. $host . ';dbname=' . $dbname;
    $pdo = new PDO($dsn, $user, $password);
    return $pdo;
}

// array(30){
//     [0]=> string(22) "Design CSS erstellen -"
//     [1]=> NULL [2]=> string(30) "Artikel in Web-Shop einpflegen"
//     [3]=> NULL [4]=> string(46) "Optional Bildbearbeitung der einzelnen Artikel"
//     [5]=> string(25) "Dokumentation erstellen -"
//     [6]=> NULL
//     [7]=> string(8) "Beratung"
//     [8]=> string(11) "Logo Design"
//     [9]=> string(18) "Flasch Animationen"
//     [10]=> string(10) "URL-Design"
//     [11]=> string(13) "URL-Redirects"
//     [12]=> string(6) "Design"
//     [13]=> string(13) "CSS erstellen"
//     [14]=> string(19) "Programm entwickeln"
//     [15]=> string(15) "Dokumentation -"
//     [16]=> NULL
//     [17]=> string(11) "Logo Design"
//     [18]=> string(11) "Android App"
//     [19]=> NULL
//     [20]=> string(16) "Webseite zur App"
//     [21]=> string(29) "Dokumentation für den Kunden"
//     [22]=> string(8) "Beratung"
//     [23]=> string(23) "Anwendungsentwicklung -"
//     [24]=> NULL
//     [25]=> string(9) "IOS App -"
//     [26]=> NULL
//     [27]=> string(13) "Dokumentation"
//     [28]=> string(21) "Anwendungsentwicklung"
//     [29]=> string(8) "Beratung"
// }