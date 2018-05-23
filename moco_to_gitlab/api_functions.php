<?php
ini_set('max_execution_time', 300);
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
// create issue in project ///////////////////////////////
    if ($project_exists == true){
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

function load_selected_offer_array($moco_token, $chosen_offer_id)
{
    $url = 'https://cp.mocoapp.com/api/v1/offers/'.$chosen_offer_id;
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
    $offer_array = (array)json_decode($response, true);
    // check if offer is already accepted - only then show data
    foreach ($offer_array as $value) {
        if ($offer_array['status'] == 'accepted'){
            $offer_status = 'accepted';
        }
    }

    if ($offer_status == "accepted"){

        for ($i = 0; $i < count($offer_array['items']); $i++)
        {
            if (empty($offer_array['items'][$i]["title"])){
                $offer_array['items'][$i]["title"] = "";
            }
            else{
                $offer_array['items'][$i]["title"] = str_replace("&nbsp;", '', $offer_array['items'][$i]["title"]);
                $offer_array['items'][$i]["title"] = strip_tags($offer_array['items'][$i]["title"]);
            }

            if (empty($offer_array['items'][$i]["description"])){
                $offer_array['items'][$i]["description"] = "";
            }
            else{
                $offer_array['items'][$i]["description"] = str_replace("&nbsp;", '', $offer_array['items'][$i]["description"]);
                $offer_array['items'][$i]["description"] = strip_tags($offer_array['items'][$i]["description"]);            
            }

            $all_items_array['id'][] = $offer_array['items'][$i]["id"];
            $all_items_array['title'][] = $offer_array['items'][$i]["title"];
            $all_items_array['description'][] = $offer_array['items'][$i]["description"];
        }
    }
    else{
        $all_items_array = array();
    }
    return $all_items_array;
}

function load_offer($array)
{
    $_SESSION['ticket_ordered_array'] = array();
    if ($array != null){

        for ($i = 0; $i < count($array['id']); $i++)
        {
            if ($array['title'][$i] != ""){
                echo "<tr>";
                echo "<td>".$array['title'][$i]."</td>";
                echo "<td><input type='checkbox' name='select_ticket[".$i."]' value='".$i."' checked></td>";
                echo "</tr>";
            }
        }
    }
    else{
        echo "<tr>";
        echo "<td>Dieses Angebot wurde noch nicht bestätigt!</td>";
        echo "<td></td>";
        echo "</tr>"; 
    }
}

// function load_tickets()
// {
//     global $sel_data_array, $moco_token;
//     $url = 'https://cp.mocoapp.com/api/v1/offers/'.$_SESSION['chosen_offer_id'];
//     $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
//     $offer_array = (array)json_decode($response, true);

//         for ($i = 0; $i < count($offer_array['items']); $i++)
//         {  
//             if ($offer_array['items'][$i]["description"] == null && $offer_array['items'][$i+1]["description"] == null){
//                 $sel_data_array[] = $offer_array['items'][$i]["title"];
//             }
//             elseif ($offer_array['items'][$i]["description"] != null){
//                 $sel_data_array[] = $offer_array['items'][$i-1]["title"].$offer_array['items'][$i]["description"];
//             }
//         }
// }

// function load_tickets($array)
// {
//     global $sel_data_array; 

//     for ($i = 1; $i < count($array['id']); $i++)
//     {
//         if (empty($array['description'][$i]) && empty($array['description'][$i+1])){
//             // $sel_data_array['id'] = $array['tid'][$i];
//             $sel_data_array[] = $array['title'][$i];
//             // $sel_data_array['description'] = $array['description'][$i];
//         }
//         if ($array['description'][$i] != ""){
//             $sel_data_array[] = $array['title'][$i-1].$array['description'][$i];
//         }
//     }
// }