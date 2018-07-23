<?php
ini_set('max_execution_time', 500);
// Rest API functions MOCO & GITLAB ///////////////////////////////////////////////////////

// Authenticates User and return the needed Moco API-Token to be able to use the Moco API.
function authenticate_user($email, $passwd)
{
    $_SESSION['loggedIn'] = false;
    $domain_name = substr(strrchr($email, "@"), 1);
    if ($domain_name != "gal-digital.de"){
        $_SESSION["state"] = "wrongUser";
        unset($_SESSION['loggedIn']);
        return; 
    }

    //////////////////////////////////////////////////////////////////////////////
    // to DO -- link muss variabel sein, denn cp.mocoapp.com ist Benutzerdefiniert https://galdigital.mocoapp.com/
    //////////////////////////////////////////////////////////////////////////////

    $url = "https://cp.mocoapp.com/api/v1/session?email=".$email."&password=".$passwd;
    $response = \Httpful\Request::post($url)->withContentType("application/x-www-form-urlencoded")->expectsJson()->send();
    $moco_api_key_array_raw = (array)json_decode($response, true);

    // var_dump($moco_api_key_array_raw);

    if ($moco_api_key_array_raw['api_key'] != "") {
        $_SESSION["moco_token"] = $moco_api_key_array_raw['api_key'];
        $_SESSION["state"] = "loggedIn";
        $_SESSION['loggedIn'] = true;

        ///////////////////////////////
        $moco_token = $_SESSION["moco_token"];
        $url = 'https://cp.mocoapp.com/api/v1/offers';
        $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
        $offer_array_raw = (array)json_decode($response, true);
    
        for ($i = 0; $i < count($offer_array_raw); $i++)
        {  
            // only if status is accepted 
            if ($offer_array_raw[$i]['status'] == 'accepted'){
                $_SESSION['all_offers_array'][] = $offer_array_raw[$i]['title'];
            }
        }
        ///////////////////////////////

        ///////////////////////////////
        $url = 'https://cp.mocoapp.com/api/v1/projects';
        $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
    
        $project_array = (array)json_decode($response, true);
    
        for ($i = 0; $i < count($project_array); $i++)
        { 
            $_SESSION['all_projects_array'][] = $project_array[$i]['name'];
        }
        ///////////////////////////////

        return $_SESSION["moco_token"];
    }
    else{
        $_SESSION["state"] = "wrongUser";
        unset($_SESSION['loggedIn']);
    }
}

// loads all offers currently in Moco
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
            $_SESSION['all_offers'][] = $offer_array_raw[$i]['title'];
        }
    }
}

// loads all Project of User
function load_gitlab_projects($gitlab_token)
{
    // $user_id = get_user_id($gitlab_token);
    // $url = 'https://gitlab.com/api/v4/users/'.$user_id.'/projects';
    $url = 'https://gitlab.com/api/v4/projects?membership=true';
    // $url = 'https://gitlab.com/api/v4/projects';
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    $gitlab_projekt_array = (array)json_decode($response, true);
    return $gitlab_projekt_array;
}
// function get_user_id($gitlab_token)
// {
//     $userName = get_gitlab_user_name_pdo();
//     $url = 'https://gitlab.com/api/v4/users?username='.$userName;
//     $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
//     $user_array = (array)json_decode($response, true);
//     $user_id = $user_array[0]['id'];
//     return $user_id;
// }

// check if tickets already exist in chosen project in GitLab
function check_gitlab_tickets($project_string, $ticket_array, $gitlab_token)
{
    $project_exists = false;
    $result = '';
    $project_array = load_gitlab_projects($gitlab_token);
    $tmp_project_name_array = $tmp_project_id_array = $ticket_double_array = array();

    for ($i = 0; $i < count($project_array); $i++)
    { 
        $tmp_project_name_array[] = $project_array[$i]['name'];
        $tmp_project_id_array[] = $project_array[$i]['id'];
    }

    for ($i = 0; $i < count($tmp_project_name_array); $i++)
    { 
        if ($tmp_project_name_array[$i] === $project_string){
            $project_exists = true;
            $project_id = $tmp_project_id_array[$i];
            // used in load_gitlab_project_description()
            $_SESSION['project_id'] = $project_id;
        }
    }
    if ($project_exists === true){
        $url = "https://gitlab.com/api/v4/projects/".$project_id."/issues";
        $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
        $issue_array = (array)json_decode($response, true);

        for ($i = 0; $i < count($issue_array); $i++)
        { 
            for ($j = 0; $j < count($ticket_array['title']); $j++)
            {
                if ($issue_array[$i]['title'] === $ticket_array['title'][$j]){

                    $ticket_double_array[] = $ticket_array['title'][$j];
                }
            }
        }
        if ($ticket_double_array != null){
            $tmp = array_unique($ticket_double_array);
            $result = implode(',', $tmp);
        }
    }
    return $result;
}

// to check which tickets and what Project was selected
function check_selected_tickets()
{
        // array of all selected checkboxes //////////////////////////////////////////////  
        if(isset($_POST['select_ticket'])){
            foreach($_POST['select_ticket'] as $key => $checked) {            
                $submitNumber_ticket[] = $key;
            }
        }
        // array of all selected Tickets ////////////////////////////////////////////////
        $_SESSION["submitNumber_ticket"] = $submitNumber_ticket;
    
        foreach ($_SESSION["submitNumber_ticket"] as $value){
            $tmp_array['id'][] = $_SESSION['offer_data']['id'][$value];
            $tmp_array['title'][] = $_SESSION['offer_data']['title'][$value];
            $tmp_array['description'][] = $_SESSION['offer_data']['description'][$value];
        }
    
        $_SESSION["selected_tickets"] = $tmp_array;
        // selected Project //////////////////////////////////////////////////////////////
        $_SESSION['select_project'] = $_POST['projectInput'];
}

// load the description of chosen Projekt all GitLab Projects
function load_gitlab_project_description($gitlab_token, $chosen_project_title)
{
    $description = '';
    $project_array = load_gitlab_projects($gitlab_token);
    $tmp_project_name_array = $tmp_project_id_array = $tmp_project_description_array = array();

    for ($i = 0; $i < count($project_array); $i++)
    { 
        for ($j = 0; $j < count($project_array[$i]); $j++){
            $tmp_project_name_array[] = $project_array[$i]['name'];
            $tmp_project_id_array[] = $project_array[$i]['id'];
            $tmp_project_description_array[] = $project_array[$i]['description'];
        }
    }

    for ($i = 0; $i < count($tmp_project_name_array); $i++)
    { 
        if ($tmp_project_name_array[$i] == $chosen_project_title){
            $description = $tmp_project_description_array[$i];
        }
    }
    
    return $description;
}

// inserts the chosen Project into GitLab if it doesn't already exist
function insert_project($string, $ticket_array, $gitlab_token, $group_id)
{
    $project_exists = false;
    $project_array = load_gitlab_projects($gitlab_token);

    for ($i = 0; $i < count($project_array); $i++)
    { 
        if ($project_array[$i]['name'] == $string){
            $project_exists = true;
            $project_id = $project_array[$i]['id'];
            $_SESSION['id'] =  $project_id;
        }
    }
// create project in gitlab if doesn't exist ////////////
    if ($project_exists == false){
        $string = urlencode($string);
        $desc = $_POST['project_description'];
        $desc = urlencode($desc);
        // $url = 'https://gitlab.com/api/v4/projects?name='.$string.'&description='.$desc;
        $url = 'https://gitlab.com/api/v4/projects?name='.$string.'&description='.$desc.'&namespace_id='.$group_id;
        $response = \Httpful\Request::post($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();

        // $desc = $_POST['project_description'];
        // $url = 'https://gitlab.com/api/v4/projects';
        // $response = \Httpful\Request::post($url)->body(json_encode([
        //     'name' => $string,
        //     'description' => description,
        // ]))->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    }
    else{
        $desc = $_POST['project_description'];
        $desc = urlencode($desc);
        // $url = 'https://gitlab.com/api/v4/projects/'.$project_id.'?description='.$desc;
        $url = 'https://gitlab.com/api/v4/projects/'.$project_id.'?description='.$desc;
        $response = \Httpful\Request::put($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    }
}

// inserts all chosen tickets into GitLab
function insert_project_tickets($string, $ticket_array, $description_array, $gitlab_token)
{
    $project_exists = false;
    $tmp_project_name_array = array();
    $tmp_project_id_array = array();
    $project_id = 0;

    $project_array = load_gitlab_projects($gitlab_token);

    for ($i = 0; $i < count($project_array); $i++)
    { 
        $tmp_project_name_array[] = $project_array[$i]['name'];
        $tmp_project_id_array[] = $project_array[$i]['id'];
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
                $url = "https://gitlab.com/api/v4/projects/". $project_id. "/issues?title=".$ticket_array[$i]; 
            }
            else{
                $url = "https://gitlab.com/api/v4/projects/". $project_id. "/issues?title=".$ticket_array[$i]."&description=".$description_array[$i];
            }
            $response = \Httpful\Request::post($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
        }
    }
}

// loads all Moco data(id, title, description) from selected offer into "$all_items_array" ARRAY
function load_selected_offer_array($moco_token, $chosen_offer_id)
{
    if ($chosen_offer_id != ""){
        $url = 'https://cp.mocoapp.com/api/v1/offers/'.$chosen_offer_id;
        $response = \Httpful\Request::get($url)->withAuthorization("Token token=$moco_token")->expectsJson()->send();
        $offer_array = (array)json_decode($response, true);
    
        for ($i = 0; $i < count($offer_array['items']); $i++)
        {
            if (empty($offer_array['items'][$i]['title'])){
                $offer_array['items'][$i]['title'] = "";
            }
            else{
                $offer_array['items'][$i]['title'] = str_replace('&nbsp;', '', $offer_array['items'][$i]['title']);
                $offer_array['items'][$i]['title'] = strip_tags($offer_array['items'][$i]['title']);
            }

            if (empty($offer_array['items'][$i]['description'])){
                $offer_array['items'][$i]['description'] = "";
            }
            else{
                $offer_array['items'][$i]['description'] = str_replace('&nbsp;', '', $offer_array['items'][$i]['description']);
                $offer_array['items'][$i]['description'] = strip_tags($offer_array['items'][$i]['description']);            
            }

            $all_items_array['id'][] = $offer_array['items'][$i]['id'];
            $all_items_array['title'][] = $offer_array['items'][$i]['title'];
            $all_items_array['description'][] = $offer_array['items'][$i]['description'];
        }
    }
    else{
        return;
    }
    return $all_items_array;
}

// loads all offer tiele
function load_offer($array)
{
    $db_array = array();
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
                    echo "<td><label class='container'><input type='checkbox' name='select_ticket[".$i."]' value='".$i."'><span class='checkmark'></span></label></td>";
                }
                else{
                    echo "<td class='ticket_in_db_font-size'>".$array['title'][$i]."</td>";
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

function get_offer_id()
{
   $offer_title = $_POST['offerInput'];
    
    for ($i = 0; $i < count($_SESSION['offer_id']); $i++)
    {
        if ($_SESSION['offer_title'][$i] == $offer_title){
            $_SESSION['chosen_offer_id'] = $_SESSION['offer_id'][$i];
        }
    }
    return $_SESSION['chosen_offer_id'];
}

function check_offer_exists($offer_title)
{
    $check_offer_exists = false;
    for ($i = 0; $i < count($_SESSION['all_offers_array']); $i++)
    {
        if ($_SESSION['all_offers_array'][$i] == $offer_title){
            $check_offer_exists = true;
        }
    }
    return $check_offer_exists;
}

function get_gitlab_groups()
{
    $gitlab_token = 'Vb23WYp2KmxvPG4xVRhB';

    $url = 'https://gitlab.com/api/v4/namespaces';
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();
    $group_array = (array)json_decode($response, true);

    for ($i = 0; $i < count($group_array); $i++)
    {
        if ($group_array[$i]['kind'] === 'group'){
            if ($_SESSION['sel_group'] == $group_array[$i]['id']){

                echo "<option class='optionCenter' value='" . $group_array[$i]['id'] . "'selected>" . $group_array[$i]['name'] . "</option>";
            }
            else{
                echo "<option class='optionCenter' value='" . $group_array[$i]['id'] . "'>" . $group_array[$i]['name'] . "</option>";
             }
        }


    }

}