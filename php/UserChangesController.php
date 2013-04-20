<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/8/13
 * Time: 7:03 PM
 * To change this template use File | Settings | File Templates.
 */
$loggedin=true;
if(!$loggedin){
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}

error_log("CONTROLLER HIT".time());
$data = file_get_contents("php://input");
$jsonData = json_decode($data);
$updatedUserData = $jsonData->data;

$bIsNewUser = true;

if($updatedUserData->idUserID>0){
    $bIsNewUser = false;
}

$oldUser = new User($updatedUserData->idUserID);

//set the values

$oldUser->sFullName = $updatedUserData->sFullName;
$oldUser->sUserID = $updatedUserData->sUserID;
$oldUser->sNationalID = $updatedUserData->sNationalID;
$oldUser->iRole = $updatedUserData->iRole;

$tempCompany = new Company($updatedUserData->idCompanyID);
$oldUser->sCompanyName = $tempCompany->sCompanyName;
$oldUser->idCompanyID = $updatedUserData->idCompanyID;

if($updatedUserData->pPassword !=""){
    $oldUser->pPassword = $updatedUserData->pPassword;
}
echo $oldUser->SaveUser();
