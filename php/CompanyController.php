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

error_log("CompanyController ObjData=".$objData->criteria);

switch($objData->criteria){
    case "GetCompanies":
        error_log("CompanyController - GetCompanies");

            $companyCollection = new CompanyCollection();
        if($objData->iRole=="1"){
            $companyCollection->GetAllCompanies();
        }else{
            $companyCollection->GetAllCompaniesByID($objData->idCompanyID);
        }
            echo $companyCollection->ToJSON();

        break;
    case "DeleteCompany":
        $TempCo = new Company($objData->data);
        error_log("delete the company "+$objData->data);
        echo $TempCo->Delete();
        break;
        break;
    case "GetCompanyByID":
        error_log("CompanyController - GetByCompanyID ".$objData->data);
        $company = new Company($objData->data);

        echo $company->ToJSON();
        break;
    case "CheckCompanyCode":
        error_log("CompanyController - CheckCompanyCode =".$objData->data);
        $company = new Company();
        $company->sCode = $objData->data;
        $company->GetByCompanyCode();
        echo $company->ToJSON();
        break;
    case "something else":
        echo "junk";
        break;
}



?>
