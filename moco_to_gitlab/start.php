<?php
error_reporting(0);
require "vendor/autoload.php";
require_once('pdo_functions.php');
require_once('api_functions.php');
require_once('conn.inc.php'); // in "conn.inc.php" is stored the connection data
session_start();

$post_check = 'deny';
$_SESSION["notTicketsSelected"] = $_SESSION['no_tickets_selected'] = false;
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
        
        $load_offer_options = new Twig_SimpleFunction('load_offer_options', function() {
             load_offer_options($_SESSION["moco_token"]); // api_functions.php
        });
        $twig->addFunction($load_offer_options);
        
        $loading_projects = new Twig_SimpleFunction('loading_projects', function() {
            load_projects();
        });
        $twig->addFunction($loading_projects);
        
        $load_projects = new Twig_SimpleFunction('load_projects', function() {
            load_projects();
        });
        $twig->addFunction($load_projects);
        
        $headline = new Twig_SimpleFunction('headline', function() {
            global $offer_title;
            echo "<div class='offer_title'>";
            if ($_SESSION['offer_title_h2'] != ""){
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
            load_offer($_SESSION['offer_data']); // api_functions.php
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
    session_destroy();  
    $_SESSION = array();
    unset($_POST);
    echo $twig->render('index.html', array(
        'state' => 'not_loggedIn',
    ));
}

if (isset($_POST["login"])){
    echo $_POST['sel_chosenOffer'];
    $_SESSION["moco_token"] = authenticate_user($_POST['user'], $_POST['passwd']); // api_functions.php

    // var_dump($_SESSION['all_offers_array']);
    // echo "<br>";
    // var_dump($_SESSION['all_projects_array']);

    $moco_token = $_SESSION["moco_token"];
    // global $moco_token;
    if ($_SESSION["state"] == "loggedIn"){

        $offer = $_POST['sel_chosenOffer'];

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

        $_SESSION['offer_data'] = load_selected_offer_array($moco_token, $_SESSION['chosen_offer']);

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
    //////////////////////////////////////////
    $_SESSION["gitlab_token"] = $_POST['sel_gitlab_token'];
    //////////////////////////////////////////

    // $_SESSION['select_project'] = $_POST['select_project'];
    $_SESSION['select_project'] = $_POST['projectInput'];
    $moco_token = $_SESSION["moco_token"];
    if ($_SESSION['loggedIn'] === true){

        // $offer = $_POST['sel_chosenOffer'];
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
            'no_tickets_selected' => $_SESSION['no_tickets_selected'],
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

///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_POST['sent_tickets'])){

    $_SESSION['select_project'] = $_POST['projectInput'];

    $all_offers = array_unique($_SESSION['all_offers_array']);
    $all_projects = array_unique($_SESSION['all_projects_array']);

    if ($_POST['projectInput'] == "" || $_POST['offerInput'] == ""){
        echo $twig->render('index.html', array(
            'no_tickets_selected' => $_SESSION['no_tickets_selected'],
            'state' => 'offer_chosen',
            'all_offers' => $all_offers,
            'all_projects' => $all_projects,
            'offer_title_input' => $_SESSION['offer_title_input'],
            'project_title_input' => $_SESSION['select_project'],
            'no_input' => true,

        ));
    }
    else{
        //////////////////////////////////////////
        $_SESSION["gitlab_token"] = $_POST['sel_gitlab_token'];
        //////////////////////////////////////////

        check_selected_tickets();
        $result = check_gitlab_tickets($_SESSION['select_project'], $_SESSION['selected_tickets'], $_SESSION['gitlab_token']); // api_functions.php

        // transfer data to index.html ///////////////////////////////////////////////////
        if ($_SESSION["selected_tickets"] == null){

            $_SESSION['no_tickets_selected'] = true;
            $moco_token = $_SESSION["moco_token"];
            if ($_SESSION['loggedIn'] == true){

                // /////////////////////////////////////////
                for ($i = 0; $i < count($_SESSION['offer_id']); $i++)
                {
                    if ($_SESSION['offer_id'][$i] == $_SESSION['chosen_offer_id']){
                        $offer_title = $_SESSION['offer_title'][$i];
                    }
                }

                $_SESSION['offer_title_h2'] = $offer_title;

                $_SESSION['offer_data'] = load_selected_offer_array($moco_token, $_SESSION['chosen_offer_id']); // api_functions.php

                $_SESSION['back_to_main_frame'] = 'ok';

                $all_offers = array_unique($_SESSION['all_offers_array']);
                $all_projects = array_unique($_SESSION['all_projects_array']);

                echo $twig->render('index.html', array(
                    'state' => 'offer_chosen',
                    'no_tickets_selected' => $_SESSION['no_tickets_selected'],
                    'all_offers' => $all_offers,
                    'all_projects' => $all_projects,
                ));
            }
            else{
                echo $twig->render('index.html', array(
                    'state' => 'not_loggedIn',
                ));
            }
        }
        else{
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
            
            $_SESSION['description_array'] = $description_array;

            $_SESSION['select_project'] = str_replace("&nbsp;", '', $_SESSION['select_project']);

            echo $twig->render('index.html', array(
                'state' => 'ticket_sent',
                'ticket_check' => $result,
                'selected_tickets' => $_SESSION['selected_tickets']['title'],
                'selected_project' => $_SESSION['select_project'],
            ));
        }
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
// transfer tickets to GitLab ///////////////////////////////////////////////////////////////////////
if (isset($_POST["transfer"])){
    // functions (api_functions.php) creates projects & issues & new database entries 
    insert_project($_SESSION['select_project'], $_SESSION['selected_tickets']['title'], $_SESSION["gitlab_token"]); // api_functions.php
    insert_project_tickets($_SESSION['select_project'], $_SESSION['selected_tickets']['title'], $_SESSION['description_array'], $_SESSION["gitlab_token"]); // api_functions.php
    write_ticketIDs_in_DB($_SESSION["selected_tickets"]); // pdo_functions.php
    $_SESSION['back'] = "after tickets were sent";
    header('Location: start.php');
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////

// loop through $_POST array to check which was set
    foreach($_POST as $key => $value)
    {
        if ($key[$i] != "back"){
            $post_check = 'allow';
        }
    }

if (isset($_POST["back"]) || isset($_SESSION['back_to_main_frame']) &&  $post_check === 'deny'){

    $_SESSION['back'] = "reset";
    $moco_token = $_SESSION["moco_token"];
    if ($_SESSION['loggedIn'] === true){

        // /////////////////////////////////////////
        for ($i = 0; $i < count($_SESSION['offer_id']); $i++)
        {
            if ($_SESSION['offer_id'][$i] == $_SESSION['chosen_offer_id']){
                $offer_title = $_SESSION['offer_title'][$i];
            }
        }

        $_SESSION['offer_title_h2'] = $offer_title;

        $_SESSION['offer_data'] = load_selected_offer_array($moco_token, $_SESSION['chosen_offer_id']); // api_functions.php

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
    global $gitlab_token_array, $gitlab_email_array;
    $submitNumber_token = array_pop(array_keys($_REQUEST['edit']));
    $_SESSION["submitNumber_token"] = $submitNumber_token;
    edit_token(); // pdo_functions.php
    $sel = $_SESSION["submitNumber_token"];
    echo $twig->render('index.html', array(
        'state' => 'editToken',
        'gitlab_email' => $gitlab_email_array[$sel],
        'gitlab_token' => $gitlab_token_array[$sel],
    ));
}

// save edited token into database
if (isset($_POST["saveToken"])){

    save_token_pdo();

    $gitlab_email = $_POST["gitlab_email"];
    $gitlab_token = $_POST["gitlab_token"];

    if (empty($gitlab_email) || empty($gitlab_token)){
        echo $twig->render('index.html', array(
            'state' => 'editTokenEmptyField',
            'gitlab_email' => $gitlab_email,
            'gitlab_token' => $gitlab_token,
            // 'button_check' => true,
        ));
    }
    elseif ($username_invalid == true){
        echo $twig->render('index.html', array(
            'state' => 'editUserWrongInvalidUser',
            'lastname' => $_POST["lastname"],
            'firstname' => $_POST["firstname"],
            'username' => $_POST["username"],
            'moco_token' => $_POST['moco_token'],
            'gitlab_token' => $_POST['gitlab_token'],
            'passwd' => $_POST["passwd"],
            'admin' => $_POST["admin"],
            // 'button_check' => true,
        ));
    }         
    else{
            // update user in database
            $id = $id_array[$_SESSION["submitNumber_user"]];

            $sql = 'UPDATE staff SET moco_token = ?, gitlab_token = ?, username = ?, password = ?, firstname = ?, lastname = ?, admin = ? WHERE id = ?';

            $lastname = $_POST["lastname"];
            $firstname = $_POST["firstname"];
            $username = $_POST["username"];
            $moco_token = $_POST['moco_token'];
            $gitlab_token = $_POST['gitlab_token'];
            $passwd = $_POST["passwd"];
            $admin = $_POST["admin"];

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$moco_token, $gitlab_token, $username, $passwd_hash, $firstname, $lastname, $admin, $id]);
            
            echo $twig->render('index.html', array(
                'state' => 'manage_gitlab_token',              
            ));
    }
    $pdo = null;   
}

    if (isset($_POST["deleteToken"])){
        delete_token_pdo(); // pdo_functions.php
        echo $twig->render('index.html', array(
            'state' => 'manage_gitlab_token',     
        ));
    }