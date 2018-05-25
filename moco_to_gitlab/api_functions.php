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
        if ($offer_array_raw[$i]['status'] == 'accepted'){
            $_SESSION['offer_id'][] = $offer_array_raw[$i]['id'];
            $_SESSION['offer_title'][] = $offer_array_raw[$i]['title'];
            // selects the value which was previously selected //////////////////////////////////////
            if ($_SESSION['chosen_offer_id'] == $offer_array_raw[$i]['id']){
                echo "<option class='optionCenter' value=" . $offer_array_raw[$i]['id'] . " selected>" . $offer_array_raw[$i]['title'] . "</option>";
            }
            else{
                echo "<option class='optionCenter' value=" . $offer_array_raw[$i]['id'] . ">" . $offer_array_raw[$i]['title'] . "</option>";
            }
        }
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

//////////////////////////////////////////////////////////
function load_projects()
{
    global $project_name_array, $moco_token;

    $url = 'https://cp.mocoapp.com/api/v1/projects';
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();

    $projekt_array = (array)json_decode($response, true);

    for ($i = 0; $i < count($projekt_array); $i++)
    { 
        $project_name_array[] = $projekt_array[$i]['name'];
        if ($projekt_array[$i]['name'] == $_SESSION['select_project']){
            echo "<option class='optionCenter' value='" . $projekt_array[$i]['name'] . "' selected>" . $projekt_array[$i]['name'] . "</option>";
        }
        else{
            echo "<option class='optionCenter' value='" . $projekt_array[$i]['name'] . "'>" . $projekt_array[$i]['name'] . "</option>";
        }
    }
    $_SESSION['project_name_array'] = $project_name_array;
}

function load_selected_offer_array($moco_token, $chosen_offer_id)
{
    $url = 'https://cp.mocoapp.com/api/v1/offers/'.$chosen_offer_id;
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
    $offer_array = (array)json_decode($response, true);

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

    return $all_items_array;
}

function load_offer($array)
{
    // $_SESSION['ticket_ordered_array'] = array();

    for ($i = 0; $i < count($_SESSION['id_array_db']); $i++)
    {
        $db_array[] = (int)$_SESSION['id_array_db'][$i];
    }

    if ($array != null){

        for ($i = 0; $i < count($array['id']); $i++)
        {
            if ($array['title'][$i] != ""){
                echo "<tr>";
                if (in_array($array['id'][$i], $db_array, TRUE)){
                    echo "<td class='ticket_in_db_font-color'>".$array['title'][$i]. "  <br><sub><i>(bereits übertragen)</i></sub></td>";
                    // echo "<td><input type='checkbox' name='select_ticket[".$i."]' value='".$i."'></td>";
                    echo "<td><label class='container'><input type='checkbox' name='select_ticket[".$i."]' value='".$i."'><span class='checkmark'></span></label></td>";
                }
                else{
                    echo "<td>".$array['title'][$i]."</td>";
                    // echo "<td><input type='checkbox' name='select_ticket[".$i."]' value='".$i."' checked></td>";
                    echo "<td><label class='container'><input type='checkbox' name='select_ticket[".$i."]' value='".$i."' checked><span class='checkmark'></span></label></td>";
                }
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

