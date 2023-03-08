<?php

//DATABASE
$sql_serveur = "MacBook-Pro-de-Mike.local"; //Nom du host
$sql_login = "root";  // Nom d'utilisateur pour la base de donnÈe
$sql_pass = "";      // Le mot de passe  pour la base de donnÈe
$sql_bdd = "resto";  // Nom de la base de donnÈe*/ 

$useMySQL = false;
// $connection = mysqli_connect($sql_serveur,$sql_login,$sql_pass,$sql_bdd) or die(mysqli_error($connection));
$connection = $useMySQL ?  mysqli_connect($sql_serveur,$sql_login,$sql_pass,$sql_bdd) : sqlite_open('include/database.sqlite', SQLITE3_OPEN_READWRITE);

if ($useMySQL == false) {
    sqlite_create_function($connection, 'DATEDIFF');
    sqlite_create_function($connection, 'DAYOFWEEK');
    sqlite_create_function($connection, 'DATE_FORMAT', 'DATEFORMAT');
    sqlite_create_function($connection, 'WEEKDAY');
    sqlite_create_function($connection, 'SUBDATE');
    sqlite_create_function($connection, 'NOW');
    // sqlite_create_function($connection, 'ENCRYPT', 'ENCRYPT');
    // sqlite_create_function($connection, 'DECRYPT', 'DECRYPT');
}