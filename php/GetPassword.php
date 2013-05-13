<?php
/**
 * User: danvasquez
 * Date: 5/2/13
 * Time: 7:47 PM
 */

foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}


$data = file_get_contents("php://input");
$objData = json_decode($data);

if(isset($objData)){
    $userID = $objData->sUserID;
}

if(!isset($userID)){
    $userID = "dan@maestroinfo.com";
}

if(isset($userID)){
    try{
        $user = new User();
        $password = $user->GetPassword($userID);
        if(isset($password)){
            //mail it to the id
            mail($sUserID,"Your HRA Survey Password","Please store this password in a safe place.\n Your Password is: $password");
            echo 1;
        }

    }catch(Exception $e){
        echo -1;
    }
}else{
    echo 0;
}