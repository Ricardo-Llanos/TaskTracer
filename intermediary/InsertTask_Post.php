<?php

include_once '../.env/config.php';
include_once (ROOT_PATH."backend".DIRECTORY_SEPARATOR."InsertTask.php");

if ($_POST){
    // $status = $_POST['status-task'];
    
    $name = isset($_POST['name-task']) ? $_POST['name-task'] : null;
   
    // $name = isset($_POST['name-task']) ?? null; //Esta función está mal, solo devuelve true o null


    $obj_insert = new InsertTask();
    $obj_insert->insert($name);
}

?>