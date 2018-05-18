<?php
require "vendor/autoload.php";
// echo "hello<br>";
$status = 'created';
$api_key = "53a856de73a8b8b0a82aa7a604026747";


// $url = 'https://cp.mocoapp.com/api/v1/offers';
// $url = 'https://cp.mocoapp.com/api/v1/offers/77772';
// $url = 'https://cp.mocoapp.com/api/v1/projects';

// $response = \Httpful\Request::get($url)->withAuthorization("Token token=$api_key")->expectsJson()->send();

// $response = \Httpful\Request::get($url)->withAuthorization("Token token=$api_key")->expectsJson()->send();

// echo "<br><br>";
// echo "<pre>" . print_r(json_decode($response), true) . "</pre>";
// echo "<br><br>";
// echo "<pre>" . print_r((array)json_decode($response), true) . "</pre>";
// echo "<br><br>";


// Alle Angebote und deren ID & Title /////////////////////////////////////////////////////////
$url = 'https://cp.mocoapp.com/api/v1/offers';
$response = \Httpful\Request::get($url)->withAuthorization("Token token=$api_key")->expectsJson()->send();

$offer_array_raw = (array)json_decode($response, true);

for ($i = 0; $i < count($offer_array_raw); $i++)
{
    $tmp_array[$i]['title'] = $offer_array_raw[$i]['title'];
    $tmp_array[$i]['id'] = $offer_array_raw[$i]['id'];
}
echo "<br><br>";
echo "<pre>" . print_r($tmp_array) . "</pre>";

echo "<br><br>";
echo $tmp_array[1]['id'];
echo "<br><br>";

// Items zu einem Angebot //////////////////////////////////////////////////
$url = 'https://cp.mocoapp.com/api/v1/offers/'.$tmp_array[0]['id'];
$response = \Httpful\Request::get($url)->withAuthorization("Token token=$api_key")->expectsJson()->send();

$offer_array = (array)json_decode($response, true);
echo "<pre>" . print_r($offer_array ) . "</pre>";


// foreach ($offer_array['items'] as $value) {
//     echo $value["title"];
//     echo $value["description"];
//     echo $value["optional"];
//     echo "<br><br>";
// }

// foreach ($offer_array as $value) {
//     if ($offer_array['status'] == 'accepted'){
//         $status = 'accepted';
//     }
// }
// echo $status;