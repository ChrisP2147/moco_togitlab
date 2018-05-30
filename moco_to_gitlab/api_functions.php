<?php
ini_set('max_execution_time', 500);
// Rest API functions MOCO & GITLAB ///////////////////////////////////////////////////////

// this function loads all "accepted offers from Moco into the Dropdown-Menu"
function load_offer_options($moco_token)
{   
    global $moco_token;
    $url = 'https://cp.mocoapp.com/api/v1/offers';
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
    $offer_array_raw = (array)json_decode($response, true);

    for ($i = 0; $i < count($offer_array_raw); $i++)
    {  
        // only if status is accepted 
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

// loads all accepted project into the Dropdown-Menu
function load_projects()
{
    global $project_name_array, $moco_token;

    $url = 'https://cp.mocoapp.com/api/v1/projects';
    $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();

    $projekt_array = (array)json_decode($response, true);

    for ($i = 0; $i < count($projekt_array); $i++)
    { 
        $project_name_array[] = $projekt_array[$i]['name'];
        // preselects the previously chosen project or else selects the first one
        if ($projekt_array[$i]['name'] == $_SESSION['select_project']){
            echo "<option class='optionCenter' value='" . $projekt_array[$i]['name'] . "' selected>" . $projekt_array[$i]['name'] . "</option>";
        }
        else{
            echo "<option class='optionCenter' value='" . $projekt_array[$i]['name'] . "'>" . $projekt_array[$i]['name'] . "</option>";
        }
    }
    $_SESSION['project_name_array'] = $project_name_array;
}

// inserts the chosen Project into GitLab if it doesn't already exist
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

// loads all Moco data(id, title, description) from selected offer into "$all_items_array" ARRAY
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

// loads all offer tiele
function load_offer($array)
{
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
                    // special style checkbox
                    echo "<td class='ticket_in_db_font-color'>".$array['title'][$i]. "  <br><sub><i>(bereits übertragen)</i></sub></td>";
                    // normal style checkbox
                    // echo "<td><input type='checkbox' name='select_ticket[".$i."]' value='".$i."'></td>";
                    echo "<td><label class='container'><input type='checkbox' name='select_ticket[".$i."]' value='".$i."'><span class='checkmark'></span></label></td>";
                }
                else{
                    echo "<td class='ticket_in_db_font-size'>".$array['title'][$i]."</td>";
                    // echo "<td><input type='checkbox' name='select_ticket[".$i."]' value='".$i."' checked></td>";
                    echo "<td><label class='container'><input type='checkbox' name='select_ticket[".$i."]' value='".$i."' checked><span class='checkmark'></span></label></td>";
                }
                echo "</tr>";
            }
        }
    }
    else{
        echo "<tr>";
        echo "<td>Bitte Angebot auswählen</td>";
        echo "<td></td>";
        echo "</tr>"; 
    }
}

// snippet for main Frame "frame_offer_chosen"
function load_frame_offer_chosen($offer_title, $moco_token)
{
    echo "<div class='frame frame_offer_chosen'>";            
    echo "<form action=".$_SERVER["PHP_SELF"]." method='post'>";
    echo "<div class='mainframeContainer'>";
        echo "<div class='headlineContainer'><h1>Tickets to GitLab &nbsp &#10004</h1></div>";
            echo "<div class='sel_btn_container'>";
                echo "<h1 class='h1_offer_chosen' >eingeloggt als ". $_SESSION["firstname"] ." ". $_SESSION["lastname"] . "</h1>";
                // superUser has admin permissions
                if ($_SESSION['superUser'] == true){
                    echo "<input type='submit' class='btnSuperUser btn_manageUser' name='manageUser' value='Benutzer Verwalten'/>";
                    echo "<input type='submit' class='button btn_logout' name='logout' value='Ausloggen'/>";
                }
                else{
                    echo "<input type='submit' class='button btn_logout' name='logout' value='Ausloggen'/>";
                }
            echo "</div>";
    echo "</div>";
 
    echo "<div class='spacer'><hr></div>";

    echo "<div class='projectContainer'>";

    // transfer Tickts ///////////////////////////////////////////////////////////////////////////////////////////
    echo "<div class='sent_ticketsContainer'>";
        echo "<div class='tmp_div1'>";
            echo "<select class='selectAPI selectAPI_chosen' name='sel_chosenOffer'>";
            load_offer_options($moco_token);
            echo "</select>";
            echo "<input type='submit' class='button btn_chosen_offer' name='btn_choose_offer' value='wählen' />";
        echo "</div>";

        echo "<div class='tmp_div2'>";
            echo "<select class='selectAPI select_project' name='select_project'>";
                load_projects();
            echo "</select>";
            echo "<input type='submit' class='button btn_sent_tickets' name='sent_tickets' value='Tickets erstellen &nbsp &#10004'/>";
        echo "</div>";
    echo "</div>";
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // render Data-Table
    echo "<table id='table_id' class='display'>";
        echo "<thead>";
        echo "<div class='offer_title'>";

        if ($offer_title != null){
            if ($_SESSION["notTicketsSelected"] == true){
                echo "<h2 class='h2_offer_title'><i>Angebot:&nbsp&nbsp&nbsp  </i>". $offer_title . "</h2><h2 class='H2notTicketsSelected'>Bitte Tickets auswählen</h2>";
            }
            else{
                echo "<h2><i>Angebot:&nbsp&nbsp&nbsp  </i>". $offer_title . "</h2>";
            }
        }
        else{
            echo "<h2 class='angebotAuswählen'>Bitte Angebot auswählen</h2>";
        }
        
        echo "</div";

            echo "<tr>";
                echo "<th>Angebots-Positionen</th>";
                echo "<th>als Ticket einfügen</th>";
            echo "</tr>";
        echo "</thead>";
            echo "<tbody>";

}