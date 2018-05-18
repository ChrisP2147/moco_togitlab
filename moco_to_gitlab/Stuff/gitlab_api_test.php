<?php
require "vendor/autoload.php";
echo "hello you<br>";

$token = "Vb23WYp2KmxvPG4xVRhB";

// $url = 'https://gitlab.com/api/v3/projects/?username=ChrisP2147';
// $url = 'https://gitlab.com/api/v3/issues/?username=ChrisP2147';

// $url = 'https://gitlab.example.com/api/v4/projects/4/issues?title=Issues%20with%20auth&labels=bug';


// Liste aller Projekte /////////////////////////////////////////////////
// $url = 'https://gitlab.com/api/v3/projects/?username=ChrisP2147';
// $response = \Httpful\Request::get($url)->addHeader('Private-Token', "Vb23WYp2KmxvPG4xVRhB")->expectsJson()->send();
// echo "<pre>" . print_r((array)json_decode($response), true) . "</pre>";
/////////////////////////////////////////////////////////////////////////
// create Projekt ///////////////////////////////////////////////////////
// $url = 'https://gitlab.com/api/v3/projects?name=foobartest';
// $response = \Httpful\Request::post($url)->addHeader('Private-Token', "Vb23WYp2KmxvPG4xVRhB")->expectsJson()->send();
/////////////////////////////////////////////////////////////////////////

// Select a specific Project ID /////////////////////////////////////////
$url = 'https://gitlab.com/api/v3/projects';
$response = \Httpful\Request::get($url)->addHeader('Private-Token', "Vb23WYp2KmxvPG4xVRhB")->expectsJson()->send();
echo "<pre>" . print_r((array)json_decode($response), true) . "</pre>";

$projekt_array = (array)json_decode($response, true);
// var_dump($projekt_array);

for ($i = 0; $i < count($projekt_array); $i++)
{ 
    // echo $projekt_array[$i]['text'];
    // echo "<br><br>";
} 

/////////////////////////////////////////////////////////////////////////
// create Issue for one Project /////////////////////////////////////////
$url = 'https://gitlab.com/api/v3/projects/6381841/issues?title=Ticket_4711=bug';
$response = \Httpful\Request::post($url)->addHeader('Private-Token', "Vb23WYp2KmxvPG4xVRhB")->expectsJson()->send();
/////////////////////////////////////////////////////////////////////////