<?php
error_reporting(0);
require "vendor/autoload.php";
require_once('pdo_functions.php');
require_once('api_functions.php');
require_once('html_snippets.php');
session_start();
$_SESSION['state'] = "not_loggedIn";
$offer_status = 'created';
$post_check = 'deny';
$_SESSION['saveNewUser'] = $_SESSION['saveUser'] = $_SESSION["notTicketsSelected"] = $_SESSION['no_tickets_selected'] = false;
// $moco_token = "53a856de73a8b8b0a82aa7a604026747";
// $gitlab_token = "Vb23WYp2KmxvPG4xVRhB";

// the Moco & GitLab-Token is stored in Session variable when user is logged in
$moco_token = $_SESSION["moco_token"];
$gitlab_token = $_SESSION["gitlab_token"];
// Twig ///////////////////////////////////////////////////////////
$loader = new Twig_Loader_Filesystem('templates'); // the file beein rendered lies is called index.html and is located in folder "templates"
$twig = new Twig_Environment($loader);
///////////////////////////////////////////////////////////////////

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
    get_data_pdo(); // pdo_functions.php
    global $moco_token;
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

        $_SESSION['chosen_offer'] = $offer;

        $_SESSION['offer_data'] = load_selected_offer_array($moco_token, $_SESSION['chosen_offer']);

        //////////////////////////////////
        // renders the main frame
        load_frame_offer_chosen($offer_title, $moco_token); // api_functions.php
        //////////////////////////////////

        select_ticketIDs_from_DB();  // pdo_functions.php
        load_offer($_SESSION['offer_data']); // api_functions.php
    
        echo $twig->render('index.html', array(
            'state' => 'offer_chosen',          
        ));
    }
    else{
        echo $twig->render('index.html', array(
            'state' => 'wrongUser',
        ));
    }                                    
}

if (isset($_POST["btn_choose_offer"])){
    $_SESSION['select_project'] = $_POST['select_project'];
    get_data_pdo();  // pdo_functions.php
    global $moco_token;
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

        $_SESSION['chosen_offer'] = $offer;

        $_SESSION['offer_data'] = load_selected_offer_array($moco_token, $_SESSION['chosen_offer']); // api_functions.php

        //////////////////////////////////
        // renders the main frame
        load_frame_offer_chosen($offer_title, $moco_token); // api_functions.php
        //////////////////////////////////

        select_ticketIDs_from_DB(); // pdo_functions.php
        load_offer($_SESSION['offer_data']); // api_functions.php
    
        echo $twig->render('index.html', array(
            'no_tickets_selected' => $_SESSION['no_tickets_selected'],
            'state' => 'offer_chosen',
        ));
    }
    else{
        echo $twig->render('index.html', array(
            'state' => 'wrongUser',
        ));
    }                    
}

///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_POST["sent_tickets"])){

    check_selected_tickets();
    $result = check_gitlab_tickets($_SESSION['select_project'], $_SESSION["selected_tickets"], $gitlab_token); // api_functions.php
    // transfer data to index.html ///////////////////////////////////////////////////

    if ($_SESSION["selected_tickets"] == null){
        $_SESSION['no_tickets_selected'] = true;
        get_data_pdo(); // pdo_functions.php
        global $moco_token;
        if ($_SESSION["state"] == "loggedIn"){
            // /////////////////////////////////////////
            for ($i = 0; $i < count($_SESSION['offer_id']); $i++)
            {
                if ($_SESSION['offer_id'][$i] == $_SESSION['chosen_offer_id']){
                    $offer_title = $_SESSION['offer_title'][$i];
                }
            }
            $data = load_selected_offer_array($moco_token, $_SESSION['chosen_offer_id']); // api_functions.php
            //////////////////////////////////
            // renders the main frame
            load_frame_offer_chosen($offer_title, $moco_token);
            //////////////////////////////////
            select_ticketIDs_from_DB(); // pdo_functions.php
            load_offer($data); // api_functions.php
    
            echo $twig->render('index.html', array(
                'state' => 'offer_chosen',
                'no_tickets_selected' => $_SESSION['no_tickets_selected'],
            ));
        }
        else{
            echo $twig->render('index.html', array(
                'state' => 'wrongUser',
            ));
        }
    }
    else{
        $ticket_array = $_SESSION["selected_tickets"];

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
            'selected_tickets' => $_SESSION["selected_tickets"]['title'],
            'selected_project' => $_SESSION['select_project'],
        ));
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
// transfer tickets to GitLab ///////////////////////////////////////////////////////////////////////
if (isset($_POST["transfer"])){
    // functions (api_functions.php) creates projects & issues & new database entries 
    insert_project($_SESSION['select_project'], $_SESSION['selected_tickets']['title'], $gitlab_token); // api_functions.php
    insert_project_tickets($_SESSION['select_project'], $_SESSION["selected_tickets"]['title'], $_SESSION['description_array'], $gitlab_token); // api_functions.php
    write_ticketIDs_in_DB($_SESSION["selected_tickets"]); // pdo_functions.php
    $_SESSION['back'] = "after tickets were sent";
    header('Location: start.php');
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_POST["manageUser"])){
    create_frame_manage_users(); // html_snippets.php
    show_users_pdo(); // pdo_functions.php
    echo $twig->render('index.html', array(
        'state' => 'manage',
    ));
}

// loop through $_POST array to check which was set
    foreach($_POST as $key => $value)
    {
        if ($key[$i] != "back"){
            $post_check = 'allow';
        }
    }

if (isset($_POST["back"]) || isset($_SESSION['back_to_main_frame']) &&  $post_check === 'deny'){
    $_SESSION['back'] = "reset";
    get_data_pdo(); // pdo_functions.php
    global $moco_token;
    if ($_SESSION["state"] == "loggedIn"){
        // /////////////////////////////////////////
        for ($i = 0; $i < count($_SESSION['offer_id']); $i++)
        {
            if ($_SESSION['offer_id'][$i] == $_SESSION['chosen_offer_id']){
                $offer_title = $_SESSION['offer_title'][$i];
            }
        }

        $data = load_selected_offer_array($moco_token, $_SESSION['chosen_offer_id']); // api_functions.php

        //////////////////////////////////
        // renders the main frame
        load_frame_offer_chosen($offer_title, $moco_token); // api_functions.php
        //////////////////////////////////
                
        select_ticketIDs_from_DB(); // pdo_functions.php
        load_offer($data); // api_functions.php

        echo $twig->render('index.html', array(
            'state' => 'offer_chosen',          
        ));
    }
    else{
        echo $twig->render('index.html', array(
            'state' => 'wrongUser',
        ));
    }       
}

if (isset($_POST["back2"])){
    create_frame_manage_users(); // html_snippets.php
    show_users_pdo(); // pdo_functions.php
    echo $twig->render('index.html', array(
        'state' => 'manage',
    ));
} 
   
if (isset($_POST["createUser"])){
    echo $twig->render('index.html', array(
        'state' => 'createUser',
    ));
}

if (isset($_REQUEST["edit"])){
    $submitNumber_user = array_pop(array_keys($_REQUEST['edit']));
    $_SESSION["submitNumber_user"] = $submitNumber_user;
    edit_user(); // pdo_functions.php
    $sel = $_SESSION["submitNumber_user"];
    echo $twig->render('index.html', array(
        'state' => 'editUser',
        'lastname' => $lastname_array[$sel],
        'firstname' => $firstname_array[$sel],
        'username' => $username_array[$sel],
        'moco_token' => $moco_token_array[$sel], 
        'gitlab_token' => $gitlab_token_array[$sel],  
        'admin' => $permission_array[$sel],
    ));
}

// save new user into database
if (isset($_POST["saveNewUser"])){
    $_SESSION['saveNewUser'] = true;
    global $id, $active, $moco_token, $gitlab_token, $username, $passwd_hash, $firstname, $lastname, $admin, $superUser, $username_invalid;

    $lastname = $_POST["lastname"];
    $firstname = $_POST["firstname"];
    $username = $_POST["username"];
    $moco_token = $_POST['moco_token'];
    $gitlab_token = $_POST['gitlab_token'];
    $passwd = $_POST["passwd"];
    $admin = $_POST["admin"];

    // translate password into hashcode 
    $passwdArray = ['userPasswd'];
    $passwd_hash = password_hash($passwd, PASSWORD_BCRYPT, $passwdArray);
    
    $pdo = connect_DB_pdo(); // pdo_functions.php

    // check if username already exists
    $sql = 'SELECT * from staff';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $username_array[] = $res['username'];
        }
    }
    else{
        echo "no data found in benutzer";
    }

    for ($i = 0; $i< count($username_array); $i++)
    {
        if ($username_array[$i] == $username){
            $username_invalid = true;
        }
    }

    $textFiel_lastname = $_POST["lastname"];
    $textFiel_firstname = $_POST["firstname"];
    $textFiel_username = $_POST["username"];
    $textFiel_moco_token = $_POST["moco_token"];
    $textFiel_gitlab_token = $_POST["gitlab_token"];
    $textFiel_passwd = $_POST["passwd"];
    $textFiel_passwdRepeat = $_POST["passwdRepeat"];

    if (empty($textFiel_lastname) || empty($textFiel_firstname) || empty($textFiel_username) || empty($textFiel_moco_token) || empty($textFiel_gitlab_token) || empty($textFiel_passwd) || empty($textFiel_passwdRepeat)){
        echo $twig->render('index.html', array(
            'state' => 'UserEmptyField',
            'lastname' => $_POST["lastname"],
            'firstname' => $_POST["firstname"],
            'username' => $_POST["username"],
            'moco_token' => $_POST['moco_token'],
            'gitlab_token' => $_POST['gitlab_token'],
            'passwd' => $_POST["passwd"],
            'admin' => $_POST["admin"],
            'button_check' => true,
        ));
    }
    elseif ($_POST["passwdRepeat"] != $_POST["passwd"]){
        echo $twig->render('index.html', array(
            'state' => 'UserWrongPasswd',
            'lastname' => $_POST["lastname"],
            'firstname' => $_POST["firstname"],
            'username' => $_POST["username"],
            'moco_token' => $_POST['moco_token'],
            'gitlab_token' => $_POST['gitlab_token'],
            'passwd' => $_POST["passwd"],
            'admin' => $_POST["admin"],
            'button_check' => true,
        )); 
    }
    elseif ($username_invalid == true){
        echo $twig->render('index.html', array(
            'state' => 'UserWrongInvalidUser',
            'lastname' => $_POST["lastname"],
            'firstname' => $_POST["firstname"],
            'username' => $_POST["username"],
            'moco_token' => $_POST['moco_token'],
            'gitlab_token' => $_POST['gitlab_token'],
            'passwd' => $_POST["passwd"],
            'admin' => $_POST["admin"],
            'button_check' => true,
        ));
    }       
    else{
            // insert new user into database
            $sql = 'INSERT INTO staff(moco_token, gitlab_token, username, password, firstname, lastname, admin) values(?, ?, ?, ?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$moco_token, $gitlab_token, $username, $passwd_hash, $lastname, $firstname, $admin]);
               
            echo $twig->render('index.html', array(
                'state' => 'dataSaved',
                'firstname' => $firstname,
                'lastname' => $lastname,         
            ));
        }
    $pdo = null;
}

// save user into database
if (isset($_POST["saveUser"])){
    $_SESSION['saveUser'] = true;
    global $id, $active, $moco_token, $gitlab_token, $username, $passwd_hash, $firstname, $lastname, $admin, $superUser, $username_invalid;

    $lastname = $_POST["lastname"];
    $firstname = $_POST["firstname"];
    $username = $_POST["username"];
    $moco_token = $_POST['moco_token'];
    $gitlab_token = $_POST['gitlab_token'];
    $passwd = $_POST["passwd"];
    $admin = $_POST["admin"];

    // translate password into hashcode 
    $passwdArray = ['userPasswd'];
    $passwd_hash = password_hash($passwd, PASSWORD_BCRYPT, $passwdArray);
    
    $pdo = connect_DB_pdo(); // pdo_functions.php

    $sql = 'SELECT * from staff';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $username_array[] = $res['username'];
            $id_array[] = $res['id'];
        }  
    }
    else{
        echo "no data found in benutzer";
    }
    // check if username already exists
    for ($i = 0; $i < count($username_array); $i++)
    {
        if ($username_array[$i] == $username && $id_array[$i] != $id_array[$_SESSION["submitNumber_user"]]){
            $username_invalid = true;
        }
    }

    $textFiel_lastname = $_POST["lastname"];
    $textFiel_firstname = $_POST["firstname"];
    $textFiel_username = $_POST["username"];
    $textFiel_moco_token = $_POST["moco_token"];
    $textFiel_gitlab_token = $_POST["gitlab_token"];
    $textFiel_passwd = $_POST["passwd"];
    $textFiel_passwdRepeat = $_POST["passwdRepeat"];

    if (empty($textFiel_lastname) || empty($textFiel_firstname) || empty($textFiel_username) || empty($textFiel_moco_token) || empty($textFiel_gitlab_token) || empty($textFiel_passwd) || empty($textFiel_passwdRepeat)){
        echo $twig->render('index.html', array(
            'state' => 'editUserEmptyField',
            'lastname' => $_POST["lastname"],
            'firstname' => $_POST["firstname"],
            'username' => $_POST["username"],
            'moco_token' => $_POST['moco_token'],
            'gitlab_token' => $_POST['gitlab_token'],
            'passwd' => $_POST["passwd"],
            'admin' => $_POST["admin"],
            'button_check' => true,
        ));
    }
    elseif ($_POST["passwdRepeat"] != $_POST["passwd"]){
        echo $twig->render('index.html', array(
            'state' => 'editUserWrongPasswd',
            'lastname' => $_POST["lastname"],
            'firstname' => $_POST["firstname"],
            'username' => $_POST["username"],
            'moco_token' => $_POST['moco_token'],
            'gitlab_token' => $_POST['gitlab_token'],
            'passwd' => $_POST["passwd"],
            'admin' => $_POST["admin"],
            'button_check' => true,
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
            'button_check' => true,
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
                'state' => 'dataSaved',
                'firstname' => $firstname,
                'surname' => $surname,              
            ));
    }
    $pdo = null;   
}

    if (isset($_POST["deleteUser"])){
        delete_user_pdo(); // pdo_functions.php
        echo $twig->render('index.html', array(
            'state' => 'userDeleted',
            'firstname' => $firstname,
            'lastname' => $lastname,       
        ));
    }