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


$data = file_get_contents("php://input");
$objData = json_decode($data);

error_log("UserController ObjData=".$objData->criteria);

switch($objData->criteria){
    case "GetUsers":
        error_log("UserController - GetUSers");
        $userCollection = new UserCollection();
        if($objData->iRole!='1'){
            $userCollection->GetByCompanyID($objData->idCompanyID);
        }else{ $userCollection->GetAllUsers();}

        echo $userCollection->ToJSON();
        break;
    case "DeleteUser":
        $TempUser = new User($objData->data);
        error_log("delete the user "+$objData->data);
        echo $TempUser->Delete();
        break;
    case "GetSingleUser":
        error_log("UserController - GetSingleUser ".$objData->data);
        $user = new User($objData->data);

        echo $user->ToJSON();
        break;
    case "CheckUserIDExists":
        error_log("Checking for User ID ".$objData->data. " In database already ");
        $tempUser = new User();
        $result = $tempUser->CheckIDExists($objData->data);
        error_log("RESULT OF USERID CHECK=".$result);
        echo $result;
        break;
    case "something else":
        echo "junk";
        break;
}



?>
