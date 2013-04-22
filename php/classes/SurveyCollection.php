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
class SurveyCollection
{
    public $List = array();

    function SurveyCollection($_criteria = 0){
        //should get all the relevant Surveys
        //test data for now
    }

    public function GetByCompanyIDActive($_companyID,$_language){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM surveys WHERE status='Active' AND company=$_companyID");
        foreach ($result as $row){
            $this->List[] = new Survey($row['id'],$_language);
        }
    }
    public function GetByCompanyID($_companyID,$_language){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM surveys WHERE company=$_companyID");
        foreach ($result as $row){
            $this->List[] = new Survey($row['id'],$_language);
        }
    }
    public function GetAllSurveys(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM surveys");
        $_language = ""; //TODO clean this up
        foreach ($result as $row){
            $this->List[] = new Survey($row['id'],$_language);
        }
    }
    public function ToJSON(){
        echo json_encode($this->List);
    }

}
?>

