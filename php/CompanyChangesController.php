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
$updatedCompanyData = $jsonData->data;

$bIsNewCompany = true;

if($updatedCompanyData->idCompanyID>0){
    $bIsNewCompany = false;
}

$oldCompany = new Company($updatedCompanyData->idCompanyID);

//set the values

$oldCompany->sCode = $updatedCompanyData->sCode;
$oldCompany->sCompanyName = $updatedCompanyData->sCompanyName;
$oldCompany->txtHometext = $updatedCompanyData->txtHometext;
echo $oldCompany->SaveCompany();
