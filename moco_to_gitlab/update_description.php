<?php
// error_reporting(0);
require "vendor/autoload.php";

$desc = "OOOOO Das ist die Desription update OOOOOKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKKK";
$project_id = '7430070';
$gitlab_token = 'Vb23WYp2KmxvPG4xVRhB';

update_project_description();

function update_project_description()
{
    global $desc;
    global $project_id;
    global $gitlab_token; 

    $desc = urlencode($desc);
    $url = 'https://gitlab.com/api/v4/projects/'.$project_id.'?description='.$desc;
    $response = \Httpful\Request::put($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
}