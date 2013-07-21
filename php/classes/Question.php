<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/8/13
 * Time: 8:24 PM
 * To change this template use File | Settings | File Templates.
 */

foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}

class Question
{
    private $idUserID =0;
    public $idQuestionID = 0;
    public $idSurvey = 0;
    public $sText = "";
    public $sTextSpanish = "";
    public $iQuestionOrder = 0; //if this is 0, then this is a trigger question and shouldn't be shown with the rest
    public $sQuestionType = "Textbox";
    public $aAnswers = array();
    public $sPreamble = "";
    public $sPreambleSpanish = "";
    public $resultsStats = array();
    public $iIsTrigger = 0;

    public $idUsersAnswer = []; //the id of the user's selected answer. Current or Previously saved
    public $sUsersAnswerText=[];
    public $sUserRiskFactor=[];

    public $ActiveLanguage="ENGLISH";

	/**
	 * CONSTRUCTOR
	 * @param int $_questionID
	 * @param string $_language
	 * @param int $_userid
	 */
	function __construct($_questionID = 0,$_language="ENGLISH",$_userid=0){
        $this->idUserID = $_userid;
        $this->idQuestionID = $_questionID;
        $this->ActiveLanguage = $_language;
        $this->GetQuestionData();
        $this->GetAnswers();
        $this->CheckIsTrigger();
        $this->MakeStatsArray();

    }

	/**
	 * check if a question is the trigger and set the id of the triggered question if so
	 */
	public function CheckIsTrigger(){
        $query = "SELECT id FROM answers WHERE triggers=:triggerid";
        $params = array(':triggerid'=>$this->idQuestionID);
        $sql = new SQLConnection();
        $results = $sql->DoSelectQuery($query,$params);
        foreach($results as $row){
            $this->iIsTrigger = $row['id'];
        }
    }

	/**
	 *
	 */
	public function MakeStatsArray(){
        $counter=0;

		//loop through the answers
        foreach($this->aAnswers as $answer){
            $x = Array($answer->sAnswerText,$answer->iUsersAnswered);
            $this->resultsStats[] = $x;
            $counter++;
        }
    }

    public function GetUserAnswer(){
        $query = "SELECT * FROM userdata WHERE question=:questionid AND user=:userid";
        $params = array(":questionid"=>$this->idQuestionID,":userid"=>$this->idUserID);
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery($query,$params);

        foreach($result as $row){
            error_log("SETTING User ANSWER ".$row['answer'].$this->idQuestionID."|".$this->idUserID);
            $this->idUsersAnswer[] = $row['answer'];

            $usersAnswer = new Answer($row['answer'],$this->ActiveLanguage);

            if($this->sQuestionType!="Textbox"){
                $this->sUsersAnswerText[] = $usersAnswer->sAnswerText;
            }else{
               $this->sUsersAnswerText[] = $row['text'];
            }

            $this->sUserRiskFactor[] = $usersAnswer->sRiskFactorText;

        }
    }

    public function GetQuestionData(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT * FROM questions WHERE id=".$this->idQuestionID);
        foreach ($result as $row){
            $this->idSurvey = $row['survey'];
            if($this->ActiveLanguage != "ALL"){
                if($this->ActiveLanguage=="SPANISH"){
                    $this->sText = $row['textSpanish'];

                    if($row['textSpanish']==null || $row['textSpanish']==""){
                        $this->sText = $row['text'];
                    }
                }else{
                    $this->sText = $row['text'];
                }

                if($this->ActiveLanguage=="SPANISH"){
                    $this->sPreamble = $row['preambleSpanish'];

                    if($row['preambleSpanish']==null || $row['preambleSpanish']==""){
                        $this->sPreamble = $row['preamble'];
                    }
                }else{
                    $this->sPreamble = $row['preamble'];
                }


            }else{
                $this->sPreamble = $row['preamble'];
                $this->sPreambleSpanish = $row['preambleSpanish'];
                $this->sText = $row['text'];
                $this->sTextSpanish = $row['textSpanish'];
            }

            $this->iQuestionOrder = $row['qorder'];
            $this->sQuestionType = $row['type'];

            if($this->idUserID >0){
                $this->GetUserAnswer();
            }
        }

    }

    public function SaveUserAnswer($_userID=0,Answer $answer,$qType){
        $params="";
        $sql=null;
        if($_userID>0){
		
	    $xquery = "SELECT id from userdata where question=:questionid and user=:userid";
            $xparams = array(':questionid'=>$answer->idQuestionID,':userid'=>$_userID);

            $sql = new SQLConnection();
            $xresult = $sql->DoSelectQuery($xquery,$xparams);

            $IsNew=0;
            foreach($xresult as $xrow){
                $IsNew=$xrow['id'];
            }
            
		if($qType=="checkbox"){$IsNew=0;}
	if($IsNew>0){
                $query = "UPDATE userdata SET answer=:answerid,text=:answertext,survey=:surveyid WHERE question=:questionid and user=:userid";
            }else{
                $query = "INSERT INTO userdata (answer,question,user,text,survey) VALUES (:answerid,:questionid,:userid,:answertext,:surveyid)";
            }

            $userText=$answer->sAnswerText;
            
            
            if($this->sUsersAnswerText!=""){
                $userText = $this->sUsersAnswerText;
            }
            $params = array(':answerid'=>$answer->idAnswerID , ':questionid'=>$answer->idQuestionID ,':answertext'=>$userText ,':userid'=> $_userID,':surveyid'=>$answer->idSurveyID);
            $sql = new SQLConnection();

            $iResult=0;

            if($IsNew>0){
                error_log("updating answer |".$this->idUsersAnswer);
                $iResult=$sql->DoUpdateQuery($query,$params);
            }else{
                error_log("inserting answer");
                $iResult=$sql->DoInsertQuery($query,$params);
            }
            error_log("iResult is".$iResult);

            if($iResult>'0'){
                error_log("SAVED ANSWER question.php");
                return true;
            }else{
                return false;
            }
        }
    }


    public function DeleteQuestion(){
        $sql = new SQLConnection();
        $query ='';
        if($this->idQuestionID>0){
            $query = 'DELETE FROM questions WHERE id=:questionid LIMIT 1';
            $params = array(':questionid'=>$this->idQuestionID);
            if($sql->DoDeleteQuery($query,$params)){
                echo "SUCCESS";
            }else{
                echo "FAILED";
            }
        }
    }
    public function SaveQuestion(){
        $bIsNewQuestion = true;

        if($this->idQuestionID != null && $this->idQuestionID > 0){
            $bIsNewQuestion = false;
        }

        if(!$bIsNewQuestion){
            $surveyChangeString = "UPDATE questions SET survey = :survey,";
            $surveyChangeString .="preamble=:preamble,";
            $surveyChangeString .="preambleSpanish=:preambleSpanish,";
            $surveyChangeString .="qorder=:qorder,";
            $surveyChangeString .="type=:type,";
            $surveyChangeString .="text=:text,";
            $surveyChangeString .="textSpanish=:textSpanish ";
            $surveyChangeString .="WHERE id=".$this->idQuestionID." LIMIT 1";
        }else{
            //brand new survey

            $surveyChangeString = "INSERT INTO questions (survey,preamble,preambleSpanish,qorder,type,text,textSpanish)";
            $surveyChangeString .=" VALUES(:survey,:preamble,:preambleSpanish,:qorder,:type,:text,:textSpanish)";

        }

        $surveyChangeParams = array(':survey'=>$this->idSurvey,':preamble'=>$this->sPreamble,':preambleSpanish'=>$this->sPreambleSpanish,':qorder'=>$this->iQuestionOrder,':type'=>$this->sQuestionType,':text'=>$this->sText,':textSpanish'=>$this->sTextSpanish);

        $sql = new SQLConnection();
        if($bIsNewQuestion){
            return $sql->DoInsertQuery($surveyChangeString,$surveyChangeParams);
        }else{
            return $sql->DoUpdateQuery($surveyChangeString,$surveyChangeParams);
        }


    }

    public function GetAnswers(){
        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery("SELECT id FROM answers WHERE question=".$this->idQuestionID." AND survey=".$this->idSurvey);
        error_log("ANSWERQUERY:"."SELECT id FROM answers WHERE question=".$this->idQuestionID." AND survey=".$this->idSurvey);
        foreach ($result as $row){
            $this->aAnswers[] = new Answer($row['id'],$this->ActiveLanguage);
        }
    }

    public function ToJSON(){
        echo json_encode($this);
    }
}
