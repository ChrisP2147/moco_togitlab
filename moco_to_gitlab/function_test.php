<?php
require "vendor/autoload.php";


$token = get_gitlab_token();
var_dump($token);

function get_gitlab_token()
{
    $url = 'https://gitlab.com/api/v4/user';
    $response = \Httpful\Request::post($url)->addHeader('Bearer', 'Vb23WYp2KmxvPG4xVRhB')->expectsJson()->send();
    $projekt_array = (array)json_decode($response, true);
    return $projekt_array;
}



function insert_project($string, $ticket_array, $gitlab_token)
{
    $project_exists = false;
    $tmp_project_name_array = array();
    $tmp_project_id_array = array();
    $project_id = 0;
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
        return;
    }
}

// inserts all chosen tickets into GitLab
function insert_project_tickets($string, $ticket_array, $description_array, $gitlab_token)
{
    $project_exists = false;
    $tmp_project_name_array = array();
    $tmp_project_id_array = array();
    $project_id = 0;
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
// create project if doesn't exist ////////////////////////
    if ($project_exists == false){
        $string = urlencode($string);
        $url = 'https://gitlab.com/api/v3/projects?name='.$string;
        $response = \Httpful\Request::post($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
        // recursive (enter function again)
        insert_project_tickets($string, $ticket_array, $description_array, $gitlab_token);
    }
// create issue in project ///////////////////////////////
    elseif ($project_exists == true){
        for ($i = 0; $i < count($ticket_array); $i++)
        {
            // $ticket_array[$i] = preg_replace('/\s+/', '%20', $ticket_array[$i]);
            $ticket_array[$i] = urlencode($ticket_array[$i]);
            $description_array[$i] = urlencode($description_array[$i]);
            
            if (empty($description_array[$i])){
                $url = "https://gitlab.com/api/v3/projects/". $project_id. "/issues?title=".$ticket_array[$i]; 
            }
            else{
                $url = "https://gitlab.com/api/v3/projects/". $project_id. "/issues?title=".$ticket_array[$i]."&description=".$description_array[$i];
            }
            $response = \Httpful\Request::post($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
        }
    }
    else{
        return;
    }
}