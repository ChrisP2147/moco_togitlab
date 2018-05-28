<?php
// in here located are all functions concerning the database "moco_to_gitlabdb"
///////////////////////////////////////////////////////////////////////////////

require_once('conn.inc.php'); // in "conn.inc.php" is stored the connection data
$superUser=false; // a superuser is the one with admin permissions

// global variables
$id=$active=$moco_token=$gitlab_token=$username=$passwd_hash=$firstname=$lastname=$admin = "";
$id_array=$active_array=$moco_token_array=$gitlab_token_array=$project_name_array=$username_array=$passwd_hash_array=$firstname_array=$lastname_array=$permission_array=$sel_data_array = array();
$userCount = $submitNumber_user = 0;
$username_invalid = false;
// the Moco-Token is stored in Session variable when user is logged in
$moco_token = $_SESSION['moco_token'];
// Connect to DB /////////////////////////////////////////////
function connect_DB_pdo()
{
    global $host, $dbname, $user, $password;
    $dsn = 'mysql:host='. $host . ';dbname=' . $dbname;
    $pdo = new PDO($dsn, $user, $password);
    return $pdo;
}

// get data from DB (USER LOGIN) /////////////////////////////
function get_data_pdo()
{   
    global $id, $active, $moco_token, $gitlab_token, $username, $passwd_hash, $firstname, $lastname, $admin, $superUser;
    $pdo = connect_DB_pdo();

    if ($_POST["user"] != "" && $_POST["passwd"] != ""){
        $_SESSION["user"] = $_POST["user"];
        $_SESSION["passwd"] = $_POST["passwd"]; 
    }

    $sql = 'SELECT * FROM staff WHERE username = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["user"]]);
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $id = $res['id'];
            $active = $res['active'];
            $moco_token = $res['moco_token'];
            $gitlab_token = $res['gitlab_token'];
            $username = $res['username'];
            $passwd_hash = $res['password'];
            $firstname = $res['firstname'];
            $lastname = $res['lastname'];
            $admin = $res['admin'];
        }
        $_SESSION["firstname"] = $firstname;
        $_SESSION["lastname"] = $lastname;
        $_SESSION["moco_token"] = $moco_token;
        $_SESSION["gitlab_token"] = $gitlab_token;
                
        // verify password hash
        if (password_verify($_SESSION["passwd"], $passwd_hash)) {
            $_SESSION["state"] = "loggedIn";
            // admin is value 1 (stored in DB)
            if ($admin == 1){
                $superUser = true;
                $_SESSION['superUser'] = $superUser;
            }
        } else {
            $_SESSION["state"] = "wrongUser";
        }  
    }
    else{
        $_SESSION["state"] = "wrongUser";
    }

    return $admin;
    $pdo = null;             
}

// show all users /////////////////////////////////////////////
function show_users_pdo()
{
    global $id_array, $active_array, $gitlab_token_array, $moco_token_array, $username_array, $passwd_hash_array, $firstname_array, $lastname_array, $permission_array;

    load_user_pdo();

    $idCount = count($id_array);

    for ($i=0; $i<$idCount; $i++)
        {                                                        
            echo"<tr>";
            // echo "<td>".$api_id_array[$i]."</td>";
            echo "<td>".$moco_token_array[$i]."</td>";
            echo "<td>".$gitlab_token_array[$i]."</td>";
            echo "<td>".$firstname_array[$i]."</td>";
            echo "<td>".$lastname_array[$i]."</td>";
            echo "<td><input type='submit' class='btnEdit' name='edit[$i]' value='bearbeiten' /></td>";
            echo "</tr>";                
        }
    $pdo = null; 
}
// load all user data /////////////////////////////////////////////
function load_user_pdo()
{
    global $firstname, $lastname, $id_array, $active_array, $gitlab_token_array, $moco_token_array, $username_array, $passwd_hash_array, $firstname_array, $lastname_array, $permission_array;
    
    $pdo = connect_DB_pdo();

    $sql = 'SELECT * FROM staff';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $id_array[] = $res['id'];
            $active_array[] = $res['active'];
            $moco_token_array[] = $res['moco_token'];
            $gitlab_token_array[] = $res['gitlab_token'];
            $username_array[] = $res['username'];
            $passwd_hash_array[] = $res['password'];
            $firstname_array[] = $res['firstname'];
            $lastname_array[] = $res['lastname'];
            $permission_array[] = $res['admin'];
        }    
    }
    else{
        echo "no data found in table staff";
    }
}

// translate selected user in new FORM template //////////////// 
function edit_user()
{
    global $id_array, $active_array, $gitlab_token_array, $moco_token_array, $username_array, $passwd_hash_array, $firstname_array, $lastname_array, $permission_array;
    $pdo = connect_DB_pdo();

    $sql = 'SELECT * from staff';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $id_array[] = $res['id'];
            $active_array[] = $res['active'];
            $moco_token_array[] = $res['moco_token'];
            $gitlab_token_array[] = $res['gitlab_token'];
            $username_array[] = $res['username'];
            $passwd_hash_array[] = $res['password'];
            $firstname_array[] = $res['firstname'];
            $lastname_array[] = $res['lastname'];
            $permission_array[] = $res['admin'];
        }
        $_SESSION["firstname"] = $firstname;
        $_SESSION["lastname"] = $lastname;    
    }
    else{
        echo "no data found in benutzer";
    }
       
$pdo = null;   
}

// delete one user /////////////////////////////////////////////
function delete_user_pdo()
{
    global $id, $username, $firstname, $lastname;

    $username = $_POST["username"];

    $pdo = connect_DB_pdo();

    $sql = 'SELECT id FROM staff WHERE username = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $userID = $stmt->fetch(PDO::FETCH_OBJ);

    $sql = 'DELETE from staff Where id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID->id]);

    $pdo = null;
}

// write all transferred ticket IDs in DataBase ////////////////
function write_ticketIDs_in_DB($ticket_array)
{
    $pdo = connect_DB_pdo();

    for ($i = 0; $i < count($ticket_array['id']); $i++)
    {
        // insert all Offer Positions und IDs into database
        $sql = 'INSERT INTO tickets_check(ticket_id, title) values(?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ticket_array['id'][$i], $ticket_array['title'][$i]]);
    }

    $pdo = null;
}

// select all transferred ticket IDs in DataBase ////////////////
function select_ticketIDs_from_DB()
{
    $_SESSION['id_array_db'] = array();

    $pdo = connect_DB_pdo();

    $sql = 'SELECT * from tickets_check';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $id_array[] = $res['ticket_id'];
        }    
    }

    $_SESSION['id_array_db'] = $id_array;

    $pdo = null; 
}