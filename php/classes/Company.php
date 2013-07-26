<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/20/13
 * Time: 1:29 AM
 * To change this template use File | Settings | File Templates.
 */
foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}
class Company
{
    public $idCompanyID = 0;
    public $sCompanyName = "";
    public $sCode="";
    public $urlCompanyLogo="";
    public $txtHometext="";

    function Company($_companyID=0){
        $this->idCompanyID = $_companyID;
        error_log("NEW COMPANY OBJECT ID=".$this->idCompanyID);
        $this->GetCompanyInfo();

    }

    private function GetCompanyInfo(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT * FROM companies WHERE id=".$this->idCompanyID);
        foreach($result as $row){
            $this->sCompanyName = $row['name'];
            $this->sCode = $row['code'];
            $this->urlCompanyLogo = $row['companylogo'];
            $this->txtHometext = $row['hometext'];
        }
    }

    public function GetByCompanyCode(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT * FROM companies WHERE code='".$this->sCode."'");
        foreach($result as $row){
            $this->sCompanyName = $row['name'];
            $this->idCompanyID = $row['id'];
            $this->sCode = $row['code'];
            $this->urlCompanyLogo = $row['companylogo'];
            $this->txtHometext = $row['hometext'];
        }
    }

    public function Delete(){
        if($this->idCompanyID > 0){
            $sql = new SQLConnection();
            return $sql->DoDeleteQuery("DELETE FROM companies WHERE id=:id LIMIT 1",array("id"=>$this->idCompanyID));
        }else{return false;}
    }

    public function SaveCompany(){
        $bIsNewCompany = true;
        if($this->idCompanyID>0){
            $bIsNewCompany = false;
        }

        if(!$bIsNewCompany){
            $companyQuery = "UPDATE companies SET name=:name,code=:code,companylogo=:companylogo,hometext=:hometext WHERE id=".$this->idCompanyID;
        }else{
            $companyQuery = "INSERT companies (name,code,companylogo,hometext) VALUES (:name,:code,:companylogo,:hometext)";
        }

        $companyParams = array(':name'=>$this->sCompanyName,':code'=>$this->sCode,':companylogo'=>$this->urlCompanyLogo,':hometext'=>$this->txtHometext);
        $sql = new SQLConnection();

        if($bIsNewCompany){
            return $sql->DoInsertQuery($companyQuery,$companyParams);
        }else{
            if($sql->DoUpdateQuery($companyQuery,$companyParams)){
                return 1;
            }else{
                return 0;
            }
        }

    }

    public function ToJSON(){
        echo json_encode($this);
    }
}
