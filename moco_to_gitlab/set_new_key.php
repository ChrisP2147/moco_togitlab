<?php
require_once('conn.inc.php'); // in "conn.inc.php" is stored the connection data
require_once('pdo_functions.php');
session_start();

// inserts new email adress and GitLab token into database
// ToDos = Plausibilität für Eingabefelder....
if ($_POST['mail'] != "" && $_POST['token'] != ""){
    $mail = $_POST['mail'];
    $token = $_POST['token'];

    $pdo = connect_DB_pdo();

    $sql = 'INSERT INTO tbl_gitlab_token(gitlab_token, gitlab_email) VALUES (?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token, $mail]);

    $pdo = null; 
}



