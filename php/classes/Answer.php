<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/8/13
 * Time: 9:02 PM
 * To change this template use File | Settings | File Templates.
 */
foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}

class Answer
{
    public $idAnswerID = 0;
    public $idSurveyID = 0;
    public $idQuestionID = 0;
    public $sAnswerText = "";
    public $sAnswerTextSpanish = "";
    public $iOrder = 0;
    public $sRiskFactorText = "";
    public $sRiskFactorTextSpanish = "";
    public $ActiveLanguage = "ENGLISH";
    public $iUsersAnswered = 0;
    public $idTriggers =-1; //if > 0 , then this answer triggers another question

    function Answer($_answerID = 0,$_language="ENGLISH"){
       $this->idAnswerID = $_answerID;
       $this->ActiveLanguage = $_language;
       $this->GetAnswerInfo();
       $this->GetResults();
    }

    public function GetResults(){
        $query = "SELECT count(user) as num from userdata where answer=:answerid";
        $params = array(':answerid'=>$this->idAnswerID);
        $sql = new SQLConnection();

        $results = $sql->DoSelectQuery($query,$params);
        foreach($results as $row){
            $this->iUsersAnswered = $row['num'];
        }
    }

    public function GetAnswerInfo(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT * FROM answers WHERE id=".$this->idAnswerID);
        foreach ($result as $row){
            $this->idSurveyID = $row['survey'];
            $this->idQuestionID = $row['question'];
            if($this->ActiveLanguage != "ALL"){
                if($this->ActiveLanguage=="SPANISH"){
                    $this->sAnswerText = $row['answertextSpanish'];
                    $this->sRiskFactorText = $row['RiskFactorTextSpanish'];

                    if($row['RiskFactorTextSpanish']==null || $row['RiskFactorTextSpanish']==''){
                        $this->sRiskFactorText = $row['RiskFactorText'];
                    }
                    if($row['answertextSpanish']==null || $row['answertextSpanish']==''){
                        $this->sAnswerText = $row['answertext'];
                    }
                }else{
                    $this->sAnswerText = $row['answertext'];
                    $this->sRiskFactorText = $row['RiskFactorText'];
                }

            }else{
                $this->sAnswerText = $row['answertext'];
                $this->sAnswerTextSpanish = $row['answertextSpanish'];
                $this->sRiskFactorText = $row['RiskFactorText'];
                $this->sRiskFactorTextSpanish = $row['RiskFactorTextSpanish'];
            }

            $this->idTriggers = $row['triggers'];
        }
    }

    public function DeleteAnswer(){
    $sql = new SQLConnection();
    if($this->idAnswerID>0){
       $query = "DELETE FROM answers WHERE id=:answerid LIMIT 1";
       $params = array(':answerid'=>$this->idAnswerID);
       if($sql->DoDeleteQuery($query,$params)){
           echo "SUCCESS";
       }else{
           echo "FAILED";
       }
    }
}


    public function SaveAnswer(){
        $bIsNewAnswer = true;

        if($this->idAnswerID != null && $this->idAnswerID > 0){
            error_log("ANSWERID=".$this->idAnswerID);
            $bIsNewAnswer = false;
        }
//bookmark
        if(!$bIsNewAnswer){
            error_log("UPDATING ANSWER".$this->idAnswerID);
            $surveyChangeString = "UPDATE answers SET answertext = :answerText,";
            $surveyChangeString .="answertextSpanish=:answerTextSpanish,";
            $surveyChangeString .="question=:question,";

            $surveyChangeString .="survey=:survey,";
            $surveyChangeString .="triggers=:triggers,";
            $surveyChangeString .="RiskFactorText=:RiskFactorText,";
            $surveyChangeString .="RiskFactorTextSpanish=:RiskFactorTextSpanish ";
            $surveyChangeString .="WHERE id=".$this->idAnswerID." LIMIT 1";
        }else{
            //brand new answer
            error_log("INSERTING ANSWER Q=".$this->idQuestionID);
            $surveyChangeString = "INSERT INTO answers (answertext,answertextSpanish,question,survey,triggers,RiskFactorText,RiskFactorTextSpanish)";
            $surveyChangeString .=" VALUES(:answerText,:answerTextSpanish,:question,:survey,:triggers,:RiskFactorText,:RiskFactorTextSpanish)";

        }

        $surveyChangeParams = array(':answerText'=>$this->sAnswerText,':answerTextSpanish'=>$this->sAnswerTextSpanish,':question'=>$this->idQuestionID,':survey'=>$this->idSurveyID,':triggers'=>$this->idTriggers,':RiskFactorText'=>$this->sRiskFactorText,':RiskFactorTextSpanish'=>$this->sRiskFactorTextSpanish);



        $sql = new SQLConnection();
        if($bIsNewAnswer){
            error_log("PERFORMING ANSWER INSERT");
            $iResult = $sql->DoInsertQuery($surveyChangeString,$surveyChangeParams);
            if($iResult > 1){
                error_log("INSERT SUCCESS");
                return true;
            }
        }else{
            error_log("PERFORMING ANSWER UPDATE");
            return $sql->DoUpdateQuery($surveyChangeString,$surveyChangeParams);
        }


    }

    public function ToJSON(){
        echo json_encode($this);
    }
}
