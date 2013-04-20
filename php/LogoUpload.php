<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/21/13
 * Time: 3:28 PM
 * To change this template use File | Settings | File Templates.
 */
foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}

error_log("Uploading image...");

if(isset($_FILES["UploadedLogo"])){
    error_log("Parsing Upload");
    $allowedExts = array("jpg", "jpeg", "gif", "png");
    $extension = end(explode(".", $_FILES["UploadedLogo"]["name"]));
    if ((($_FILES["UploadedLogo"]["type"] == "image/gif") || ($_FILES["UploadedLogo"]["type"] == "image/jpeg") || ($_FILES["UploadedLogo"]["type"] == "image/png") || ($_FILES["UploadedLogo"]["type"] == "image/pjpeg")) && ($_FILES["UploadedLogo"]["size"] < 2000000) && in_array($extension, $allowedExts)){
        error_log("Level1 OK");
        if (!$_FILES["UploadedLogo"]["error"] > 0){
            error_log("Level2 OK");
            if(isset($_POST['idCompanyID']) && $_POST['idCompanyID']>0){
                error_log("Level3 OK");
                //do the stuff
                $logofilename = "upload/" . $_FILES["UploadedLogo"]["name"];
                $logofilename = mysql_escape_string($logofilename);

                move_uploaded_file($_FILES["UploadedLogo"]["tmp_name"], $logofilename);

                $queryString = "UPDATE companies SET companylogo=:companylogo WHERE id=:companyid LIMIT 1";
                $logofilename = './php/'.$logofilename;
                $queryParams = array(':companylogo'=>$logofilename,':companyid'=>$_POST['idCompanyID']);

                $sql = new SQLConnection();
                $sql->DoUpdateQuery($queryString,$queryParams);
            }

        }
    }
}
//UPDATE
error_log('REFERRER='.$_SERVER['HTTP_REFERER']);
header("Location: ".$_SERVER['HTTP_REFERER']."#/company/".$_POST['idCompanyID']);
