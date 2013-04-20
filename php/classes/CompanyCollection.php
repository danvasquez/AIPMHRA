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
class CompanyCollection
{
    public $List = array();

    function CompanyCollection($_criteria = 0){
        //should get all the relevant Surveys
        //test data for now
    }

    public function GetAllCompanies(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM companies");
        foreach ($result as $row){
            $this->List[] = new Company($row['id']);
        }
    }
    public function GetAllCompaniesByID($companyid){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM companies WHERE id=".$companyid);
        foreach ($result as $row){
            $this->List[] = new Company($row['id']);
        }
    }
    public function ToJSON(){
        echo json_encode($this->List);
    }

}
?>