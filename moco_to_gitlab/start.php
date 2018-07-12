<?php
// error_reporting(0);
require "vendor/autoload.php";
require_once('pdo_functions.php');
require_once('api_functions.php');
require_once('conn.inc.php'); // in "conn.inc.php" is stored the connection data
session_start();

$post_check = 'deny';
// $_SESSION['no_tickets_selected'] = false;
// $moco_token = "53a856de73a8b8b0a82aa7a604026747";
// $gitlab_token = "Vb23WYp2KmxvPG4xVRhB"; // gitlab chrispitzner@hotmail.com
// $gitlab_token = "WWTShpHg-sSiy-Kqimxk"; // gitlab chrispitzner@gmail.com

// Twig ///////////////////////////////////////////////////////////
$loader = new Twig_Loader_Filesystem('templates'); // the file beein rendered lies is called index.html and is located in folder "templates"
$twig = new Twig_Environment($loader);
///////////////////////////////////////////////////////////////////

        // Functions for TWIG /////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////

        $get_gitlab_API_key = new Twig_SimpleFunction('get_gitlab_API_key', function() {
            get_gitlab_API_key();
        });
        $twig->addFunction($get_gitlab_API_key);
        
        $load_moco_projects = new Twig_SimpleFunction('load_moco_projects', function() {
            load_moco_projects();
        });
        $twig->addFunction($load_moco_projects);
        
        $headline = new Twig_SimpleFunction('headline', function() {
            $title = "";
            if (isset($_SESSION['offer_title_h2'])){
                $title = $_SESSION['offer_title_h2'];
            }
            echo "<div class='offer_title'>";
            if ($title != ""){
                echo "<h2><i>Angebot:&nbsp&nbsp&nbsp  </i>". $_SESSION['offer_title_h2'] . "</h2>";
            }
            else{
                echo "<h2 class='lightblue'>Angebot auswählen</h2>";  
            }
            echo "</div";
        });
        $twig->addFunction($headline);

        $select_ticketIDs_from_DB = new Twig_SimpleFunction('select_ticketIDs_from_DB', function() {
            select_ticketIDs_from_DB();
        });
        $twig->addFunction($select_ticketIDs_from_DB);
            
        $load_offer = new Twig_SimpleFunction('load_offer', function() {
            $offer_data = array();
            if (isset($_SESSION['offer_data'])){
                $offer_data = $_SESSION['offer_data'];
            }
            load_offer($offer_data); // api_functions.php
        });
        $twig->addFunction($load_offer);

        $show_token_pdo = new Twig_SimpleFunction('show_token_pdo', function() {
            show_token_pdo(); // pdo_functions.php
        });
        $twig->addFunction($show_token_pdo);

        ///////////////////////////////////////////////////////////////////////////

// set the different states of the program the states are rendered in index.html depending on which $_POST was set= ... ->
// "not_loggegIn", "logged_in", "manage", "createUser", "editUser", "editUserWrong", "dataSaved", "userDeleted"
///////////////////////////////////////////////////////////////////

// state when not logged in
if (!isset($_SESSION['loggedIn']) && !isset($_POST["logout"]) && !isset($_POST["login"])){
    echo $twig->render('index.html', array(
        'state' => 'not_loggedIn',
    ));
}

// state when user is logged out
if (isset($_POST["logout"])){
    // session_destroy();  
    $_SESSION = array();
    unset($_POST);
    echo $twig->render('index.html', array(
        'state' => 'not_loggedIn',
    ));
}

if (isset($_POST["login"])){
    $_SESSION["moco_token"] = authenticate_user($_POST['user'], $_POST['passwd']); // api_functions.php

    $moco_token = $_SESSION["moco_token"];
    if ($_SESSION["state"] == "loggedIn"){

        $_SESSION['back_to_main_frame'] = 'ok';

        $all_offers = array_unique($_SESSION['all_offers_array']);
        $all_projects = array_unique($_SESSION['all_projects_array']);
    
        echo $twig->render('index.html', array(
            'state' => 'offer_chosen',
            'all_offers' => $all_offers,
            'all_projects' => $all_projects,
        ));
    }
    else{
        echo $twig->render('index.html', array(
            'state' => 'wrongUser',
        ));
    }                                    
}

if (isset($_POST["btn_choose_offer"])){
    $moco_token = $_SESSION["moco_token"];
    $check_offer_exists = false;
    $_SESSION['select_project'] = $_POST['projectInput'];
    $_SESSION['offer_title_input'] = $_POST['offerInput'];

    load_offer_options($moco_token);

    $check_offer_exists = check_offer_exists($_SESSION['offer_title_input']);

    if ($_SESSION['loggedIn'] === true && $check_offer_exists === true){
        $offer = get_offer_id();
        // Session data of which offer was chosen 
        $_SESSION['chosen_offer_id'] = $offer;
        /////////////////////////////////////////
        for ($i = 0; $i < count($_SESSION['offer_id']); $i++)
        {
            if ($_SESSION['offer_id'][$i] == $offer){
                $offer_title = $_SESSION['offer_title'][$i];
            }
        }

        $_SESSION['offer_title_h2'] = $offer_title;

        $_SESSION['chosen_offer'] = $offer;

        $_SESSION['offer_data'] = load_selected_offer_array($moco_token, $_SESSION['chosen_offer']); // api_functions.php

        $_SESSION['back_to_main_frame'] = 'ok';

        $all_offers = array_unique($_SESSION['all_offers_array']);
        $all_projects = array_unique($_SESSION['all_projects_array']);

        echo $twig->render('index.html', array(
            'no_tickets_selected' => false,
            'state' => 'offer_chosen',
            'all_offers' => $all_offers,
            'all_projects' => $all_projects,
            'offer_title_input' => $_SESSION['offer_title_input'],
            'project_title_input' => $_SESSION['select_project'],
        ));
    }
    else{
        $_SESSION['offer_data'] = "";
        $_SESSION['offer_title_h2'] = "";
        $all_offers = array_unique($_SESSION['all_offers_array']);
        $all_projects = array_unique($_SESSION['all_projects_array']);

        echo $twig->render('index.html', array(
            'no_tickets_selected' => false,
            'state' => 'offer_chosen',
            'all_offers' => $all_offers,
            'all_projects' => $all_projects,
            'offer_title_input' => $_SESSION['offer_title_input'],
            'project_title_input' => $_SESSION['select_project'],
        ));
    }                    
}

///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_POST['sent_tickets'])){

    //////////////////////////////////////////
    $_SESSION['gitlab_token'] = $_POST['sel_gitlab_token'];
    //////////////////////////////////////////
    $_SESSION['select_project'] = $_POST['projectInput'];

    $all_offers = array_unique($_SESSION['all_offers_array']);
    $all_projects = array_unique($_SESSION['all_projects_array']);

    if ($_POST['projectInput'] == "" || $_POST['offerInput'] == ""){
        echo $twig->render('index.html', array(
            'no_tickets_selected' => false,
            'state' => 'offer_chosen',
            'all_offers' => $all_offers,
            'all_projects' => $all_projects,
            'offer_title_input' => $_SESSION['offer_title_input'],
            'project_title_input' => $_SESSION['select_project'],
            'no_input' => true,
        ));
    }
    elseif (! isset($_POST['select_ticket'])){
        // $_SESSION['no_tickets_selected'] = true;
        echo $twig->render('index.html', array(
            'no_tickets_selected' => true,
            'state' => 'offer_chosen',
            'all_offers' => $all_offers,
            'all_projects' => $all_projects,
            'offer_title_input' => $_SESSION['offer_title_input'],
            'project_title_input' => $_SESSION['select_project'],
        ));
    }
    else{
        check_selected_tickets();
        $result = check_gitlab_tickets($_SESSION['select_project'], $_SESSION['selected_tickets'], $_SESSION['gitlab_token']); // api_functions.php
        $ticket_array = $_SESSION["selected_tickets"];
        $_SESSION['back_to_send_frame'] = 'ok';

        // loop through all positions in chosen offer - puts the descriptions in $description_array for each offer
        for ($i = 0; $i < count($ticket_array['id']); $i++)
        {
            for ($j = 0; $j < count($_SESSION['offer_data']['id']); $j++)
            {
                if ($_SESSION['offer_data']['title'][$j] == $ticket_array['title'][$i]){
                    $description_array[] = $_SESSION['offer_data']['description'][$j+1];
                }
            }
        }
        // array of all offer descriptions
        $_SESSION['description_array'] = $description_array;
        ////////////////////////////////////////////////////////////////////////////////////////////

        $_SESSION['select_project'] = str_replace("&nbsp;", '', $_SESSION['select_project']);

        $project_description = load_gitlab_project_description($_SESSION['gitlab_token'], $_SESSION['select_project']); // api_functions.php

        echo $twig->render('index.html', array(
            'state' => 'ticket_sent',
            'ticket_check' => $result,
            'selected_tickets' => $_SESSION['selected_tickets']['title'],
            'selected_project' => $_SESSION['select_project'],
            'project_description' => $project_description,
        ));
    }
}


/////////////////////////////////////////////////////////////////////////////////////////////////////
// transfer tickets to GitLab ///////////////////////////////////////////////////////////////////////
if (isset($_POST["transfer"])){
    // functions (api_functions.php) creates projects & issues & new database entries 
    insert_project($_SESSION['select_project'], $_SESSION['selected_tickets']['title'], $_SESSION['gitlab_token']); // api_functions.php
    insert_project_tickets($_SESSION['select_project'], $_SESSION['selected_tickets']['title'], $_SESSION['description_array'], $_SESSION['gitlab_token']); // api_functions.php
    write_ticketIDs_in_DB($_SESSION["selected_tickets"]); // pdo_functions.php
    $_SESSION['back'] = "after tickets were sent";
    header('Location: start.php');
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////

// loop through $_POST array to check which was set
    foreach($_POST as $key => $value)
    {
        // if ($key[$i] != "back"){
        if ($key != "back"){
            $post_check = 'allow';
        }
    }

if (isset($_POST["back"]) || isset($_SESSION['back_to_main_frame']) &&  $post_check === 'deny'){

    $_SESSION['back'] = "reset";
    $moco_token = $_SESSION["moco_token"];
    if ($_SESSION['loggedIn'] === true){

        // /////////////////////////////////////////
        $offer_id = array();
        $offer_title = "";
        if (isset($_SESSION['offer_id'])){
            $offer_id = $_SESSION['offer_id'];
        }
        for ($i = 0; $i < count($offer_id); $i++)
        {
            if ($offer_id[$i] == $_SESSION['chosen_offer_id']){
                $offer_title = $_SESSION['offer_title'][$i];
            }
        }
        $_SESSION['offer_title_h2'] = $offer_title;

        $co_id = "";
        if (isset($_SESSION['chosen_offer_id'])){
            $co_id = $_SESSION['chosen_offer_id'];
        }

        $_SESSION['offer_data'] = load_selected_offer_array($moco_token, $co_id); // api_functions.php

        $_SESSION['back_to_main_frame'] = 'ok';

        $all_offers = array_unique($_SESSION['all_offers_array']);
        $all_projects = array_unique($_SESSION['all_projects_array']);

        echo $twig->render('index.html', array(
            'state' => 'offer_chosen',
            'all_offers' => $all_offers,
            'all_projects' => $all_projects,
            'offer_title_input' => $_SESSION['offer_title_input'],
            'project_title_input' => $_SESSION['select_project'],      
        ));
    }
    else{
        echo $twig->render('index.html', array(
            'state' => 'not_loggedIn',
        ));
    }       
}

///////////////////////////////////////////////////////////////

if (isset($_POST['manage_gitlab_token']) || isset($_POST["back_to_edit"])){
    echo $twig->render('index.html', array(
        'state' => 'manage_gitlab_token',
    ));
}

if (isset($_REQUEST["edit"])){
    global $gitlab_token_array, $gitlab_email_array, $gitlab_user_name_array;
    $submitNumber_token = array_pop(array_keys($_REQUEST['edit']));
    $_SESSION["submitNumber_token"] = $submitNumber_token;
    edit_token(); // pdo_functions.php
    $sel = $_SESSION["submitNumber_token"];
    $_SESSION["gitlab_email"] = $gitlab_email_array[$sel];
    $_SESSION["gitlab_user_name"] = $gitlab_user_name_array[$sel];
    $_SESSION['gitlab_token'] = $gitlab_token_array[$sel];
    echo $twig->render('index.html', array(
        'state' => 'editToken',
        'gitlab_email' => $gitlab_email_array[$sel],
        'gitlab_user_name' => $gitlab_user_name_array[$sel],
        'gitlab_token' => $gitlab_token_array[$sel],
    ));
}

// save edited token into database
if (isset($_POST["saveToken"])){

    save_token_pdo(); // pdo_functions

    $gitlab_email = $_POST["gitlab_email"];
    $gitlab_user_name = $_POST["gitlab_user_name"];
    $gitlab_token = $_POST["gitlab_token"];

    if (empty($gitlab_email) || empty($gitlab_token)){
        echo $twig->render('index.html', array(
            'state' => 'editTokenEmptyField',
            'gitlab_email' => $_SESSION["gitlab_email"],
            'gitlab_user_name' => $_SESSION["gitlab_user_name"],
            'gitlab_token' => $_SESSION['gitlab_token'],
        ));
    }
    elseif ($token_invalid == true){
        echo $twig->render('index.html', array(
            'state' => 'editTokenInvalidToken',
            'gitlab_email' => $_SESSION["gitlab_email"],
            'gitlab_user_name' => $_SESSION["gitlab_user_name"],
            'gitlab_token' => $_SESSION['gitlab_token'],
        ));
    }         
    else{
            // update token in database
            update_token_pdo(); // pdo_functions

            echo $twig->render('index.html', array(
                'state' => 'manage_gitlab_token',
            ));
    }
}

if (isset($_POST["deleteToken"])){
    delete_token_pdo(); // pdo_functions.php
    echo $twig->render('index.html', array(
        'state' => 'manage_gitlab_token',     
    ));
}