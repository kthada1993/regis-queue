<?php 
    include 'config.php';
    $getData = new getData();
    $row = $getData->getOvstHosXp();
    print_r($row);
?>