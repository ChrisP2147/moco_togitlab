<?php
// Rest API functions MOCO ///////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
function load_offer_options($moco_token)
{   
    global $moco_token;
    $url = 'https://cp.mocoapp.com/api/v1/offers';
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
    $offer_array_raw = (array)json_decode($response, true);

    for ($i = 0; $i < count($offer_array_raw); $i++)
    {
        $_SESSION['offer_id'][] = $offer_array_raw[$i]['id'];
        $_SESSION['offer_title'][] = $offer_array_raw[$i]['title'];
        echo "<option class='optionCenter' value=" . $offer_array_raw[$i]['id'] . ">" . $offer_array_raw[$i]['title'] . "</option>";
    }  
}

// Rest API functions GITLAB ///////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////
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

function insert_project_tickets($string, $ticket_array, $gitlab_token)
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
// create issue in project ///////////////////////////////
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
        return;
    }
}

/////////////////////////////////////////////////////////
function load_projects()
{
    global $project_name_array, $moco_token;

    $url = 'https://cp.mocoapp.com/api/v1/projects';
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();

    $projekt_array = (array)json_decode($response, true);

    for ($i = 0; $i < count($projekt_array); $i++)
    { 
        $project_name_array[] = $projekt_array[$i]['name'];
        echo "<option class='optionCenter' value='" . $projekt_array[$i]['name'] . "'>" . $projekt_array[$i]['name'] . "</option>";
    }
    $_SESSION['project_name_array'] = $project_name_array;
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

function load_offer($array)
{
    for ($i = 0; $i < count($array); $i++)
    {
        echo "<tr>";
        echo "<td>".$array[$i]."</td>";
        // echo "<td><input type='submit' class='btn_select' name='select_ticket[$i]' value='&#10004' /></td>";
        echo "<td><input type='checkbox' name='select_ticket[".$i."]' value='".$i."' ></td>";
        echo "</tr>";
    }
}