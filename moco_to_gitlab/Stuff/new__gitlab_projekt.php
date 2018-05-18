<?php
require "vendor/autoload.php";
$moco_token = "53a856de73a8b8b0a82aa7a604026747";
$gitlab_token = "Vb23WYp2KmxvPG4xVRhB";


//////////////////////////////////////////////////////////
$string = "Test_Projekt 2";
// $ticket_array = array("albert","berta","caesar","donald");
$ticket_array = array("ü", "albert","berta","caesar","donald");
$project_id = 0;

insert_project($string, $ticket_array);
insert_project_tickets($string, $ticket_array);

function insert_project($string, $ticket_array)
{
    $project_exists = false;
    $tmp_project_name_array = array();
    $tmp_project_id_array = array();
    $project_id = 0;
    global $gitlab_token;
 // all gitlab projectnames & IDs /////////////////////////
    $url = 'https://gitlab.com/api/v3/projects';
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();

    $projekt_array = (array)json_decode($response, true);

    for ($i = 0; $i < count($projekt_array); $i++)
    { 
        $tmp_project_name_array[] = $projekt_array[$i]['name'];
        $tmp_project_id_array[] = $projekt_array[$i]['id'];
    }

    for ($i = 0; $i < count($tmp_project_name_array); $i++)
    { 
        if ($tmp_project_name_array[$i] == $string){
            $project_exists = true;
            $project_id = $tmp_project_id_array[$i];
        }
    }
// create project in gitlab if doesn't exist ////////////
    if ($project_exists == false){
        // $string = preg_replace('/\s+/', '%20', $string);
        $string = urlencode($string);
        $url = 'https://gitlab.com/api/v3/projects?name='.$string;
        $response = \Httpful\Request::post($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    }
    else{
        exit(0);
    }
}

function insert_project_tickets($string, $ticket_array)
{
    $project_exists = false;
    $tmp_project_name_array = array();
    $tmp_project_id_array = array();
    $project_id = 0;
    global $gitlab_token;
// all gitlab projectnames & IDs /////////////////////////
    $url = 'https://gitlab.com/api/v3/projects';
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();

    $projekt_array = (array)json_decode($response, true);

    for ($i = 0; $i < count($projekt_array); $i++)
    { 
        $tmp_project_name_array[] = $projekt_array[$i]['name'];
        $tmp_project_id_array[] = $projekt_array[$i]['id'];
    }

    for ($i = 0; $i < count($tmp_project_name_array); $i++)
    { 
        if ($tmp_project_name_array[$i] == $string){
            $project_exists = true;
            $project_id = $tmp_project_id_array[$i];
        }
    }
// create project in gitlab if doesn't exist ////////////
    if ($project_exists == true){
        for ($i = 0; $i < count($ticket_array); $i++)
        {
            // $ticket_array[$i] = preg_replace('/\s+/', '%20', $ticket_array[$i]);
            $ticket_array[$i] = urlencode($ticket_array[$i]);
            $url = "https://gitlab.com/api/v3/projects/". $project_id. "/issues?title=".$ticket_array[$i];
            $response = \Httpful\Request::post($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
        }
    }
    else{
        exit(0);
    }
}

