<?php
require "vendor/autoload.php";
require_once('conn.inc.php');

$moco_token = "53a856de73a8b8b0a82aa7a604026747";
$gitlab_token = "Vb23WYp2KmxvPG4xVRhB";
$all_items_array = array();

delete_ticket_check_db();
// $array = load_all_offers_data_array();
// var_dump($array);
// insert_offer_into_db();

function load_all_offers_data_array()
{
    global $moco_token, $all_items_array;
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

            if ($tmp_array[$i]['items'][$j]["title"] == null){
                $tmp_array[$i]['items'][$j]["title"] = "";
            }
            else{
                $tmp_array[$i]['items'][$j]["title"] = str_replace("&nbsp;", '', $tmp_array[$i]['items'][$j]["title"]);
                $tmp_array[$i]['items'][$j]["title"] = strip_tags($tmp_array[$i]['items'][$j]["title"]);
            }
            if ($tmp_array[$i]['items'][$j]["description"] == null){
                $tmp_array[$i]['items'][$j]["description"] = "";
            }
            else{
                $tmp_array[$i]['items'][$j]["description"] = str_replace("&nbsp;", '', $tmp_array[$i]['items'][$j]["description"]);
                $tmp_array[$i]['items'][$j]["description"] = strip_tags($tmp_array[$i]['items'][$j]["description"]);            
            }
            $all_items_array['id'][] = $tmp_array[$i]['items'][$j]["id"];
            $all_items_array['title'][] = $tmp_array[$i]['items'][$j]["title"];
            $all_items_array['description'][] = $tmp_array[$i]['items'][$j]["description"];
        }
    }
    return  $all_items_array;
}

function load_tickets()
{
    global $sel_data_array, $moco_token;
    $url = 'https://cp.mocoapp.com/api/v1/offers/'.$_SESSION['chosen_offer_id'];
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
    $offer_array = (array)json_decode($response, true);

        for ($i = 0; $i < count($offer_array['items']); $i++)
        {  
            if ($offer_array['items'][$i]["description"] == null && $offer_array['items'][$i+1]["description"] == null){
                $sel_data_array[] = $offer_array['items'][$i]["title"];
            }
            elseif ($offer_array['items'][$i]["description"] != null){
                $sel_data_array[] = $offer_array['items'][$i-1]["title"].$offer_array['items'][$i]["description"];
            }
        }
}

function insert_offer_into_db()
{
    global $all_items_array;
    $pdo = connect_DB_pdo();
    for ($i = 0; $i < count($all_items_array['id']); $i++)
    {
    // insert all Offer Positions und IDs into database
    $sql = 'INSERT INTO tickets_check(ticket_id, title, description) values(?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$all_items_array['id'][$i], $all_items_array['title'][$i], $all_items_array['description'][$i]]);
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

