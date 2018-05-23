<?php
error_reporting(0);
require "vendor/autoload.php";
require_once('pdo_functions.php');
require_once('api_functions.php');
session_start();
$_SESSION["state"] = "not_loggedIn";
$offer_status = 'created';
// $moco_token = "53a856de73a8b8b0a82aa7a604026747";
// $gitlab_token = "Vb23WYp2KmxvPG4xVRhB";
$moco_token = $_SESSION["moco_token"];
$gitlab_token = $_SESSION["gitlab_token"];
// Twig ///////////////////////////////////////////////////////////
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);
///////////////////////////////////////////////////////////////////

// set the different states = ... ->
// "not_loggegIn", "logged_in", "manage", "createUser", "editUser", "editUserWrong", "dataSaved", "userDeleted"
////////////////////////////////////////////////////////////////////

if (empty($_POST)){
    echo $twig->render('index.html', array(
        'state' => 'not_loggedIn',
    ));
}

if (isset($_POST["logout"])){
    echo $twig->render('index.html', array(
        'state' => 'not_loggedIn',
    ));
    session_destroy();  
    $_SESSION = array();
    unset($_POST);
}

if (isset($_POST["login"])){
    get_data_pdo();
    if ($_SESSION["state"] == "loggedIn"){

        echo "<div class='frame frame_logged_in'>"; 
        echo "<h1 class='h1_logged_in >Wilkommen '". $_SESSION["firstname"] ." ". $_SESSION["lastname"] . "</h1>";   
      
            echo "<form action=".$_SERVER["PHP_SELF"]." method='post'>";    
                echo "<h4 class='lbl_choose_api'>API auswählen:</h4>";
                    echo "<div class='projectContainer projectContainer_logged_in'>";
                        echo "<div class='div_tmp1'>";
                            echo "<select class='selectOffer_logged_in' name='sel_chosenOffer'>";                          
                            load_offer_options($moco_token);
                                echo "</select>";
                        echo "</div>";

        echo $twig->render('index.html', array(
            //'API_response' =>  $json->avatar_url,
            'state' => 'logged_in',          
            // 'SESSION_firstname' => $_SESSION["firstname"],
            // 'SESSION_lastname' => $_SESSION["lastname"],
            'superUser' => $superUser,
        ));
    }
    else{
        echo $twig->render('index.html', array(
            'state' => 'wrongUser',
        ));
    }                 
}

if (isset($_POST["btn_chooseAPI"])){
    get_data_pdo();
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

        echo "<div class='frame frame_API_chosen'>";
            // echo "<h1 class='h1_API_chosen' >eingeloggt als ". $_SESSION["firstname"] ." ". $_SESSION["lastname"] . "</h1>";            
                echo "<form action=".$_SERVER["PHP_SELF"]." method='post'>"; 
                echo "<div class='sel_btn_container'>";
                    echo "<h1 class='h1_API_chosen' >eingeloggt als ". $_SESSION["firstname"] ." ". $_SESSION["lastname"] . "</h1>"; 
                    if ($superUser == true){
                        echo "<input type='submit' class='btnSuperUser btn_manageUser' name='manageUser' value='Benutzer Verwalten'/>";
                        echo "<input type='submit' class='button btn_logout' name='logout' value='Ausloggen'/>";
                    }
                    else{
                        echo "<input type='submit' class='button btn_logout' name='logout' value='Ausloggen'/>";
                    }
                echo "</div>";
             
            echo "<div class='spacer'><hr></div>";

            echo "<div class='projectContainer'>";

                // transfer Tickts ///////////////////////////////////////////////////////////////////////////////////////////
                echo "<div class='sent_ticketsContainer'>";
                    echo "<div class='tmp_div1'>";
                        echo "<select class='selectAPI selectAPI_chosen' name='sel_chosenOffer'>";
                        load_offer_options($moco_token);
                        echo "</select>";
                        echo "<input type='submit' class='button btn_chosen_offer' name='btn_chooseAPI' value='wählen' />";
                    echo "</div>";

                    echo "<div class='tmp_div2'>";
                        echo "<select class='selectAPI select_project' name='select_project'>";
                            load_projects();
                        echo "</select>";
                        echo "<input type='submit' class='button btn_sent_tickets' name='sent_tickets' value='Tickets &nbsp &#10004'/>";
                    echo "</div>";
                echo "</div>";
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                echo "<table id='table_id' class='display'>";
                    echo "<thead>";
                    echo "<div class='h2_offer_title'><h2>Angebot: ". $offer_title . "</h2>";
                        echo "<tr>";
                            echo "<th>Angebots-Positionen</th>";
                            echo "<th>als Ticket einfügen</th>";
                        echo "</tr>";
                    echo "</thead>";
                        echo "<tbody>";
                        
                    load_offer($_SESSION['offer_data']);

        echo $twig->render('index.html', array(
            'state' => 'API_chosen',          
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

    // array of all selected checkboxes //////////////////////////////////////////////  
    if(isset($_POST['select_ticket'])){
        foreach($_POST['select_ticket'] as $key => $checked) {
            $submitNumber_ticket[] = $key;
        }
    }
    // array of all selected Tickets ////////////////////////////////////////////////
    $_SESSION["submitNumber_ticket"] = $submitNumber_ticket;

    // load_tickets($_SESSION['offer_data']);

    /// ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

    // foreach ($_SESSION["submitNumber_ticket"] as $value){
    //     $tmp_array['id'][] = $_SESSION['offer_data']['id'][$value];
    //     $tmp_array['title'][] = $_SESSION['offer_data']['title'][$value];
    //     $tmp_array['description'][] = $_SESSION['offer_data']['description'][$value];
    // }

    $_SESSION["selected_tickets"] = $tmp_array;
    // remove all <divs> from array //////////////////////////////////////////////////
    foreach ($tmp_array as $value) {
        $value = preg_replace("/<\/?div[^>]*\>/i", " ", $value);
        $tmp_array_nice[] = $value;
    }
    $_SESSION['selected_tickets_array_nice'] = $tmp_array_nice;
    // selected Project //////////////////////////////////////////////////////////////
    $_SESSION['select_project'] = $_POST['select_project'];

    // transfer data to index.html ///////////////////////////////////////////////////
    if ($_SESSION["selected_tickets"] == null){
        echo $twig->render('index.html', array(
            'state' => 'ticket_sent_noTickets',
            'message' => 'Du hast keine Tickets ausgewählt',
        ));
    }
    else{

        $_SESSION['selected_tickets_array_nice'] = str_replace("&nbsp;", '', $_SESSION['selected_tickets_array_nice']);
        $_SESSION['select_project'] = str_replace("&nbsp;", '', $_SESSION['select_project']);

        echo $twig->render('index.html', array(
            'state' => 'ticket_sent',
            'selected_tickets' => $_SESSION['selected_tickets_array_nice'],
            'selected_project' => $_SESSION['select_project'],
        ));
    }
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_POST["transfer"])){

    try {
        insert_project($_SESSION['select_project'], $_SESSION['selected_tickets_array_nice'], $gitlab_token);
        insert_project_tickets($_SESSION['select_project'], $_SESSION['selected_tickets_array_nice'], $gitlab_token);
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        echo 'da ist was schief gelaufen... :(';
        echo "<input type='submit' class='btnSuperUser btnManage' name='back' value='Zurück' />";
    }

    $_SESSION['send_tickts'] = null;
    echo $twig->render('index.html', array(
        'state' => 'ticket_sent_success',
        'message' => 'Tickets wurden übertragen',
    ));
}

/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_POST["manageUser"])){
    echo "<div class='frame frame_mangeUsers'>";  
    echo "<h1>Alle Benutzer</h1>";  
    echo "<form action=".$_SERVER["PHP_SELF"]." method='post'>";
?>
        <div class="btnContainer btnContainer_manageUsers">
                    <input type="submit" class="btnSuperUser btnManage" name="back" value="Zurück" />
                    <input type="submit" class="btnSuperUser btnManage" name="createUser" value="Benutzer anlegen" />
                    <input type="submit" class="button btnManage" name="logout" value="Ausloggen" />
                </div>

        <table id="table_id" class="display">
        <thead>
            <tr>
                <th>Moco Token</th>
                <th>GitLab Token</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Benutzer bearbeiten</th>
            </tr>
        </thead>
        <tbody>
<?php
            show_users_pdo();
            ?>
            </tbody>
        </table>
    </div>
</form>
<?php
    echo $twig->render('index.html', array(
        'state' => 'manage',
    ));
}
       
if (isset($_POST["back"])){
    get_data_pdo();
    global $moco_token;
    if ($_SESSION["state"] == "loggedIn"){
        // $offer = $_POST['sel_chosenOffer'];
        // // Session data of which offer was chosen 
        // $_SESSION['chosen_offer_id'] = $offer;
        // /////////////////////////////////////////
        for ($i = 0; $i < count($_SESSION['offer_id']); $i++)
        {
            if ($_SESSION['offer_id'][$i] == $_SESSION['chosen_offer_id']){
                $offer_title = $_SESSION['offer_title'][$i];
            }
        }

        // $_SESSION['chosen_offer'] = $offer;

        $data = load_selected_offer_array($moco_token, $_SESSION['chosen_offer_id']);

        echo "<div class='frame frame_API_chosen'>";            
            echo "<form action=".$_SERVER["PHP_SELF"]." method='post'>"; 
            echo "<div class='sel_btn_container'>";
                echo "<h1 class='h1_API_chosen' >eingeloggt als ". $_SESSION["firstname"] ." ". $_SESSION["lastname"] . "</h1>";
                if ($superUser == true){
                    echo "<input type='submit' class='btnSuperUser btn_manageUser' name='manageUser' value='Benutzer Verwalten'/>";
                    echo "<input type='submit' class='button btn_logout' name='logout' value='Ausloggen'/>";
                }
                else{
                    echo "<input type='submit' class='button btn_logout' name='logout' value='Ausloggen'/>";
                }
            echo "</div>";
         
        echo "<div class='spacer'><hr></div>";

        echo "<div class='projectContainer'>";

            // transfer Tickts ///////////////////////////////////////////////////////////////////////////////////////////
            echo "<div class='sent_ticketsContainer'>";
                echo "<div class='tmp_div1'>";
                    echo "<select class='selectAPI selectAPI_chosen' name='sel_chosenOffer'>";
                    load_offer_options($moco_token);
                    echo "</select>";
                    echo "<input type='submit' class='button btn_chosen_offer' name='btn_chooseAPI' value='wählen' />";
                echo "</div>";

                echo "<div class='tmp_div2'>";
                    echo "<select class='selectAPI select_project' name='select_project'>";
                        load_projects();
                    echo "</select>";
                    echo "<input type='submit' class='button btn_sent_tickets' name='sent_tickets' value='Tickets &nbsp &#10004'/>";
                echo "</div>";
            echo "</div>";
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////

            echo "<table id='table_id' class='display'>";
                echo "<thead>";
                echo "<div class='h2_offer_title'><h2>Angebot: ". $offer_title . "</h2>";
                    echo "<tr>";
                        echo "<th>Angebots-Positionen</th>";
                        echo "<th>als Ticket einfügen</th>";
                    echo "</tr>";
                echo "</thead>";
                    echo "<tbody>";
                    
                load_offer($data);

        echo $twig->render('index.html', array(
            'state' => 'API_chosen',          
        ));
    }
    else{
        echo $twig->render('index.html', array(
            'state' => 'wrongUser',
        ));
    }       
}

if (isset($_POST["back2"])){
    echo "<div class='frame frame_mangeUsers'>";  
    echo "<h1>Alle Benutzer</h1>";  
    echo "<form action=".$_SERVER["PHP_SELF"]." method='post'>";
?>
        <div class="btnContainer btnContainer_manageUsers">
                    <input type="submit" class="btnSuperUser btnManage" name="back" value="Zurück" />
                    <input type="submit" class="btnSuperUser btnManage" name="createUser" value="Benutzer anlegen" />
                    <input type="submit" class="button btnManage" name="logout" value="Ausloggen" />
                </div>

        <table id="table_id" class="display">
        <thead>
            <tr>
            <th>Moco Token</th>
                <th>GitLab Token</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Benutzer bearbeiten</th>
            </tr>
        </thead>
        <tbody>
<?php
            show_users_pdo();
            ?>
            </tbody>
        </table>
    </div>
</form>
<?php
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
    edit_user();
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
    
if (isset($_POST["saveNewUser"])){
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
    
    $pdo = connect_DB_pdo();

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
    
if (isset($_POST["saveUser"])){
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
    
    $pdo = connect_DB_pdo();

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
        delete_user_pdo();
        echo $twig->render('index.html', array(
            'state' => 'userDeleted',
            'firstname' => $firstname,
            'lastname' => $lastname,       
        ));
    }

