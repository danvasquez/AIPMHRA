<?php

foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}


$data = file_get_contents("php://input");
$objData = json_decode($data);

if(isset($objData->sUserID) && isset($objData->sPassword)){
    try{
        $tempUser = new User();
        $tempUser->CheckLogin($objData->sUserID,$objData->sPassword);
    }catch(Exception $e){
        echo $e->getMessage();
        die();
    }

    if($tempUser->idUserID >0){
        //success
        echo $tempUser->idUserID;
    }else{
        echo 0;
    }
}else{
    echo 0;
}


?>
