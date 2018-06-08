<?php
// in here located are all functions concerning the database "moco_to_gitlabdb"
///////////////////////////////////////////////////////////////////////////////

require_once('conn.inc.php'); // in "conn.inc.php" is stored the connection data

// global variables
$moco_token=$gitlab_token="";
$id_array = array();

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