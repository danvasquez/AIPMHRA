<?php

foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}


$data = file_get_contents("php://input");
$objData = json_decode($data);

if(isset($objData->sUserID) && isset($objData->sPassword)){
    $tempUser = new User();
    $tempUser->CheckLogin($objData->sUserID,$objData->sPassword);
    error_log("Checked For Login id=".$tempUser->idUserID);
    if($tempUser->idUserID >0){
        //success
        echo $tempUser->idUserID;
    }else{
        header('HTTP/1.0 401 Unauthorized');
    }
}else{
    header('HTTP/1.0 401 Unauthorized');
}


?>
