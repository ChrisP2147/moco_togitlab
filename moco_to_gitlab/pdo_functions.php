<?php
// in here located are all functions concerning the database "moco_to_gitlabdb"
///////////////////////////////////////////////////////////////////////////////

require_once('conn.inc.php'); // in "conn.inc.php" is stored the connection data

// global variables
$moco_token=$gitlab_token="";
$id_array = $gitlab_id_array = $gitlab_token_array = $gitlab_email_array = array();

// Connect to DB /////////////////////////////////////////////
function connect_DB_pdo()
{
    global $host, $dbname, $user, $password;
    $dsn = 'mysql:host='. $host . ';dbname=' . $dbname;
    $pdo = new PDO($dsn, $user, $password);
    return $pdo;
}

// get GitLab API-Key from Database////////////////////////////
function get_gitlab_API_key()
{
    $pdo = connect_DB_pdo();

    $sql = 'SELECT * FROM tbl_gitlab_token';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $gitlab_token[] = $res['gitlab_token'];
            $gitlab_email[] = $res['gitlab_email'];
            $gitlab_id[] = $res['id'];
        }
    }

    $pdo = null; 

    for ($i=0; $i < count($gitlab_email); $i++)
    {
        if ($_SESSION['gitlab_token'] === $gitlab_token[$i]){

                echo "<option class='optionCenter' value='" . $gitlab_token[$i] . "'selected>" . $gitlab_email[$i] . "</option>";
            }
        else{
            echo "<option class='optionCenter' value='" . $gitlab_token[$i] . "'>" . $gitlab_email[$i] . "</option>";
        }
    }
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

///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
// GitLab Token functions /////////////////////////////////////

$id_array = $gitlab_token_array = $gitlab_email_array = array();

function show_token_pdo()
{
    global $id_array, $gitlab_token_array, $gitlab_email_array;
    load_token_pdo();

    $idCount = count($id_array);

    for ($i=0; $i<$idCount; $i++)
        {                                                        
            echo"<tr>";
            echo "<td>".$gitlab_email_array[$i]."</td>";
            echo "<td>".$gitlab_token_array[$i]."</td>";
            echo "<td><input type='submit' class='button btn_gitlab_key' name='edit[$i]' value='edit' /></td>";
            echo "</tr>";                
        }
    $pdo = null; 
}
// load all user data /////////////////////////////////////////////
function load_token_pdo()
{
    global $id_array, $gitlab_token_array, $gitlab_email_array;
    $pdo = connect_DB_pdo();

    $sql = 'SELECT * FROM tbl_gitlab_token';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $id_array[] = $res['id'];
            $gitlab_token_array[] = $res['gitlab_token'];
            $gitlab_email_array[] = $res['gitlab_email'];
        }    
    }
}

// translate selected user in new FORM template //////////////// 
function edit_token()
{
    global $gitlab_id_array, $gitlab_token_array, $gitlab_email_array;
    $pdo = connect_DB_pdo();

    $sql = 'SELECT * FROM tbl_gitlab_token';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $gitlab_id_array[] = $res['id'];
            $gitlab_token_array[] = $res['gitlab_token'];
            $gitlab_email_array[] = $res['gitlab_email'];
        }   
    }
    else{
        echo "no data found in tbl_gitlab_token";
    } 
$pdo = null;   
}


// delete one user /////////////////////////////////////////////
function save_token_pdo()
{
    $_SESSION['saveUser'] = true;
    global $gitlab_id_array, $gitlab_token_array, $gitlab_email_array;

    $gitlab_email = $_POST["gitlab_email"];
    $gitlab_token = $_POST["gitlab_token"];
    
    $pdo = connect_DB_pdo(); // pdo_functions.php

    $sql = 'SELECT * from tbl_gitlab_token';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row_count = $stmt->rowCount();

    if ($row_count > 0){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $res)
        {
            $gitlab_id_array[] = $res['id'];
            $gitlab_token_array[] = $res['gitlab_token'];
            $gitlab_email_array[] = $res['gitlab_email'];
        }  
    }
    else{
        echo "no data found in tbl_gitlab_token";
    }
    // check if username already exists
    for ($i = 0; $i < count($gitlab_email_array); $i++)
    {
        if ($gitlab_email_array[$i] == $gitlab_email && $gitlab_id_array[$i] != $gitlab_id_array[$_SESSION["submitNumber_token"]]){
            $username_invalid = true;
        }
    }
}

// delete one user /////////////////////////////////////////////
function delete_token_pdo()
{
    $email = $_POST["gitlab_email"];

    $pdo = connect_DB_pdo();

    $sql = 'SELECT id FROM tbl_gitlab_token WHERE gitlab_email = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $tokenID = $stmt->fetch(PDO::FETCH_OBJ);

    $sql = 'DELETE from tbl_gitlab_token Where id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tokenID->id]);

    $pdo = null;
}
