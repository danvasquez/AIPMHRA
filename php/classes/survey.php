<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/8/13
 * Time: 5:28 PM
 * To change this template use File | Settings | File Templates.
 */
foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}
class Survey
{
    private $_userID=0;
    public $idSurveyID = 0;
    public $iCompany = 0;
    public $sCompanyName="";
    public $sTitle = "Blank Survey";
    public $sTitleSpanish = "Blanco Survey";

    public $sStatus = "Inactive";
    public $bResultsAvailable = false;
    public $qcQuestions = array();
    public $numTopQuestion = 0;


    public $sPreamble = "Survey Preamble";
    public $sPreambleSpanish = "Survey Preamble in Spanish";
    public $ActiveLanguage = "ENGLISH";

    function Survey($_iSurveyID = 0,$_language="ENGLISH",$_userID=0){
        $this->idSurveyID = $_iSurveyID;
        $this->ActiveLanguage = $_language;
        if($this->idSurveyID>0){
            $this->GetSurveyDataByID();
        }
        $this->_userID =$_userID;

        $this->GetSurveyQuestions();
    }


    public function GetSurveyDataByID(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT * FROM surveys WHERE id=".$this->idSurveyID);
        foreach ($result as $row){
            if($this->ActiveLanguage!="ALL"){
                if($this->ActiveLanguage=="SPANISH"){
                    $this->sTitle = $row['titleSpanish'];
                }else{
                    $this->sTitle = $row['title'];
                }
                if($this->ActiveLanguage=="SPANISH"){
                    $this->sPreamble = $row['preambleSpanish'];
                }else{
                    $this->sPreamble = $row['preamble'];
                }
            }else{
                $this->sTitle = $row['title'];
                $this->sTitleSpanish = $row['titleSpanish'];
                $this->sPreamble = $row['preamble'];
                $this->sPreambleSpanish = $row['preambleSpanish'];
            }
            $this->iCompany = $row['company'];
            $this->dtDateActive = $row['date_active'];
            $this->dtDateFinished = $row['date_finished'];
            $this->sStatus = $row['status'];
            $this->bResultsAvailable = $row['results_available'];
            $this->sCompanyName = $row['companyName'];

        }
    }
    public function GetSurveyQuestions(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM questions WHERE survey=".$this->idSurveyID." ORDER BY qorder,id");
        $counter=0;
        foreach ($result as $row){
            $_question = new Question($row['id'],$this->ActiveLanguage,$this->_userID);
            $this->qcQuestions[] = $_question;
            if($_question->iIsTrigger<=0){
                $counter++;
            }
            $this->numTopQuestion=$counter;
        }
    }

    public function Delete(){
        if($this->idSurveyID>0){
            $sql = new SQLConnection();
            return $sql->DoDeleteQuery("DELETE FROM SURVEYS WHERE id=:id",array("id"=>$this->idSurveyID));
        }else{return false;}
    }

    public function SaveSurvey(){
        $bIsNewSurvey = true;

        if($this->idSurveyID != null && $this->idSurveyID > 0){
            $bIsNewSurvey = false;
        }

        if(!$bIsNewSurvey){
            $surveyChangeString = "UPDATE surveys SET company = :company,";
            $surveyChangeString .="preamble=:preamble,";
            $surveyChangeString .="preambleSpanish=:preambleSpanish,";
            $surveyChangeString .="results_available=:results_available,";
            $surveyChangeString .="status=:status,";
            $surveyChangeString .="title=:title,";
            $surveyChangeString .="companyName=:companyName,";
            $surveyChangeString .="titleSpanish=:titleSpanish ";
            $surveyChangeString .="WHERE id=".$this->idSurveyID." LIMIT 1";
        }else{
            //brand new survey
            //first get the company name
            $tempCompany = new Company($this->iCompany);
            $this->sCompanyName = $tempCompany->sCompanyName;
            error_log("CompanyName=".$this->sCompanyName);
            $surveyChangeString = "INSERT INTO surveys (company,preamble,preambleSpanish,results_available,status,title,companyName,titleSpanish)";
            $surveyChangeString .=" VALUES(:company,:preamble,:preambleSpanish,:results_available,:status,:title,:companyName,:titleSpanish)";

        }

        $surveyChangeParams = array(':company'=>$this->iCompany,':preamble'=>$this->sPreamble,':preambleSpanish'=>$this->sPreambleSpanish,':results_available'=>$this->bResultsAvailable,':status'=>$this->sStatus,':title'=>$this->sTitle,':companyName'=>$this->sCompanyName,':titleSpanish'=>$this->sTitleSpanish);

        $sql = new SQLConnection();
        if($bIsNewSurvey){
            return $sql->DoInsertQuery($surveyChangeString,$surveyChangeParams);

        }else{
            if($sql->DoUpdateQuery($surveyChangeString,$surveyChangeParams)){
                return 1; //for true
            }else{
                return 0; //for false
            }
        }
    }

    public function GetNextQuestionOrder(){
        //first we need an array of just the order numbers
        $questionOrderNumbers = array();
        $questionOrderNumbers[] = 0;
        foreach($this->qcQuestions as $question){
            $questionOrderNumbers[] = $question->iQuestionOrder;
        }
        return max($questionOrderNumbers)+1;
    }

    public function ToJSON(){
        echo json_encode($this);
    }


}
