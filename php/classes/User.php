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
class User
{
    public $idUserID = 0;
    public $idCompanyID = 0;
    public $sCompanyName = "";
    public $sUserID="";
    public $urlCompanyLogo="";
    public $txtHometext = "";
    public $pPassword="";
    public $sFullName="";
    public $iRole = 3;
    public $sNationalID="";

    function User($_userID=0){
        $this->idUserID = $_userID;
        error_log("NEW User OBJECT ID=".$this->idUserID);
        $this->GetUserInfo();
        $this->GetCompanyInfo();

    }
    public function Delete(){
        if($this->idUserID > 0){
            $sql = new SQLConnection();
            return $sql->DoDeleteQuery("DELETE FROM users WHERE id=:id LIMIT 1",array("id"=>$this->idUserID));
        }else{return false;}
    }

    private function GetUserInfo(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT * FROM users WHERE id=".$this->idUserID);
        foreach($result as $row){
            $this->sFullName = $row['fullname'];
            $this->idCompanyID = $row['companyid'];
            $this->pPassword = $row['password'];
            $this->sUserID = $row['userid'];
            $this->iRole= $row['role'];
            $this->sNationalID= $row['nationalID'];
        }
    }

    private function GetCompanyInfo(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT * FROM companies WHERE id=".$this->idCompanyID);
        foreach($result as $row){
            $this->sCompanyName = $row['name'];
            $this->urlCompanyLogo = $row['companylogo'];
            $this->txtHometext = $row['hometext'];
        }
    }
    public function CheckIDExists($_sUserID){
        error_log("checking ".$_sUserID);
        $userQuery = "Select * from users WHERE userid=:userid LIMIT 1";
        $userParams = array(':userid'=>$_sUserID);

        $sql = new SQLConnection();

        $Results = $sql->DoSelectQuery($userQuery,$userParams);

        if(is_array($Results)){

            error_log("Results");
            if(isset($Results[0]['id']) && $Results[0]['id'] >0 ){
                return true;
            }else{
                return false;
            }

        }else{
            return false;
        }
    }
    public function CheckLogin($_userID,$_password){
        error_log("checking login =".$_userID." ".$_password);
        $userQuery = "Select * from users WHERE userid=:userid AND password=:password LIMIT 1";
        $userParams = array(':userid'=>$_userID,':password'=>$_password);

        $sql = new SQLConnection();

        $Results = $sql->DoSelectQuery($userQuery,$userParams);

        if(is_array($Results)){
            error_log("Got Login" . $Results[0]['id']);
            if(isset($Results[0]['id']) && $Results[0]['id']>0){
                $this->idUserID = $Results[0]['id'];
                $this->GetUserInfo();
                $this->GetCompanyInfo();
            }

        }
    }

    public function SaveUser(){
        $bIsNewUser = true;
        error_log("Savin User Data");
        if($this->idUserID>0){
            error_log("Not a new user");
            $bIsNewUser = false;
        }

        if(!$bIsNewUser){
            $userQuery = "UPDATE users SET fullname=:fullname,role=:role,companyid=:companyid,companyname=:companyname,userid=:userid,nationalID=:nationalID,password=:password WHERE id=".$this->idUserID." LIMIT 1";
        }else{
            $userQuery = "INSERT INTO users (fullname,role,companyid,companyname,userid,nationalID,password) VALUES (:fullname,:role,:companyid,:companyname,:userid,:nationalID,:password)";
        }

        $userParams = array(':fullname'=>$this->sFullName,':role'=>$this->iRole,':companyid'=>$this->idCompanyID,':companyname'=>$this->sCompanyName,':userid'=>$this->sUserID,':nationalID'=>$this->sNationalID,':password'=>$this->pPassword);
        $sql = new SQLConnection();

        if($bIsNewUser){
            error_log("saving the new user");
            return $sql->DoInsertQuery($userQuery,$userParams);
        }else{
            if($sql->DoUpdateQuery($userQuery,$userParams)){
                error_log("user update success");
                return 1;
            }else{
                error_log("user update fail");
                return 0;
            }
        }

    }

    public function ToJSON(){
        echo json_encode($this);
    }
}
