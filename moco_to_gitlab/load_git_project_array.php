<?php
require "vendor/autoload.php";
ini_set('max_execution_time', 500);
$gitlab_token = "Vb23WYp2KmxvPG4xVRhB";
$userName = "ChrisP2147";

$id = get_user_id();
// var_dump($id);
echo "ID = : ".$id;
echo "<br><br>";
$array = load_gitlab_projects($gitlab_token);
var_dump($array);

function load_gitlab_projects()
{
    $user_id = get_user_id();
    global $gitlab_token;
    $url = 'https://gitlab.com/api/v4/users/'.$user_id.'/projects';
    // $url = 'https://gitlab.com/api/v4/projects';
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    $gitlab_projekt_array = (array)json_decode($response, true);
    return $gitlab_projekt_array;
}
function get_user_id()
{
    global $gitlab_token;
    global $userName;
    $url = 'https://gitlab.com/api/v4/users?username='.$userName;
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    $user_array = (array)json_decode($response, true);
    $user_id = $user_array[0]['id'];
    return $user_id;
}