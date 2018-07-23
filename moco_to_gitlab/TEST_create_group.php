<?php

require "vendor/autoload.php";
$gitlab_token = 'Vb23WYp2KmxvPG4xVRhB';


function load_gitlab_projects($gitlab_token)
{
    $user_id = '1817175';
    $url = 'https://gitlab.com/api/v4/projects?membership=true';
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    $gitlab_projekt_array = (array)json_decode($response, true);
    return $gitlab_projekt_array;
}


$projects = load_gitlab_projects($gitlab_token);
var_dump($projects);

// unset($_POST['btn_group']);
// $path = '';

// if (isset($_POST['btn_group'])){
//     $response_array = create_gitlab_group($_POST['create_group']);
//     var_dump($response_array);
// }


function create_gitlab_group($name)
{
    global $gitlab_token, $path;
    $exists = check_group_exsits();

    if (!$exists){

        $path = str_replace(' ', '-', $name);
        $name = urlencode($name);
        $path = urlencode($path);
    
        $url = 'https://gitlab.com/api/v4/groups?name='.$name.'&path='.$path.'-group&description='.$name;
        $response = \Httpful\Request::post($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
        $response_array = (array)json_decode($response, true);
    }
    return $response_array;

}

function check_group_exsits()
{
    global $gitlab_token;

    $url = 'https://gitlab.com/api/v4/groups';
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    $group_array = (array)json_decode($response, true);
    $exists = false;
    for ($i = 0; $i < count($group_array); $i++)
    {
        if ($group_array[$i]['name'] === $_POST['create_group']){
            $exists = true;
        }
    }
    return $exists;
}




?>





<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>TEST Create Group</title>
  <style>
  body      {font-family:Verdana, Arial, Helvetica, sans-serif; font-size:1,5em; font-weight: medium; text-align: center;}
  </style>
</head>

<body>
<br><br>
<form action='TEST_create_group.php' method='post'>
    <input type="text" name="create_group">
    <input type="submit" name="btn_group" value="send">
</form>

 
 </body>
</html>