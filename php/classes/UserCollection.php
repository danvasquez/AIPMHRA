<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/8/13
 * Time: 7:31 PM
 * To change this template use File | Settings | File Templates.
 */
foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}
class UserCollection
{
    public $List = array();

    function UserCollection($_criteria = 0){
        //should get all the relevant Surveys
        //test data for now
    }

    public function GetByCompanyID($_companyID){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM users WHERE companyid=$_companyID");
        foreach ($result as $row){
            $this->List[] = new User($row['id']);
        }
    }
    public function GetAllUsers(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM users");
        foreach ($result as $row){
            $this->List[] = new User($row['id']);
        }
    }
    public function ToJSON(){
        echo json_encode($this->List);
    }

}
?>