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

if(!isset($objData)){
    echo "nothing";
}

switch($objData->criteria){
    case "ResultsCollection":
        $surveyID = $objData->data;
        $userID = $objData->userID;

        //check the credentials of the user
        $_user = new User($userID);
        $_survey = new Survey($surveyID);

        if($_user->iRole>2){
            echo "0";
        }
        if($_user->iRole==2 && $_user->idCompanyID!=$_survey->iCompany){
            echo "0";
        }

        $_results = new ResultsCollection($surveyID);
        echo $_results->ToJSON();
        break;
    case "DeleteSurvey":
        try{
            $TempSurvey = new Survey($objData->data);
            error_log("delete the survey "+$objData->data);
            echo $TempSurvey->Delete();
        }catch(Exception $e){
            die($e->getMessage());
        }

        break;
    case "NewSurvey":
        $NewSurvey = new Survey();
        $NewSurvey->iCompany = $objData->data;
        error_log("CREATING NEW SURVEY FOR:".$NewSurvey->iCompany);
        try{
            echo $NewSurvey->SaveSurvey();
        }catch(Exception $e){
            error_log("EXCEPTION CREATING SURVEY:".$e->getMessage());
            echo "0"; //for false
        }
        break;
    case "allSurveys":
        $surveyCollection = new SurveyCollection();
        $surveyCollection->GetAllSurveys();
        echo $surveyCollection->ToJSON();
        break;
    case "companyID":
        $surveyCollection = new SurveyCollection();
        if(isset($objData->iRole) && $objData->iRole==3){
            $surveyCollection->GetByCompanyIDActive($objData->idCompanyID,"ENGLISH");
        }else{
            $surveyCollection->GetByCompanyID($objData->idCompanyID,"ENGLISH");
        }

        echo $surveyCollection->ToJSON();
        break;
    case "surveyID":

        $survey = new Survey($objData->data,$objData->language,$objData->userID);
        echo $survey->ToJSON();

        break;
    case "BlankQuestion":
        $question = new Question(0,"ALL");
        $question->idSurvey = $objData->data;
        $question->sText = "New Question";
        $answer = new Answer(0,"ALL");
        $answer->idSurveyID = $objData->data;
        $question->aAnswers[]=$answer;
        echo $question->ToJSON();
        break;
    case "BlankAnswer":
        $answer = new Answer(0,"ALL");
        $answer->idQuestionID = $objData->data;
        $answer->idSurveyID = $objData->surveyID;
        echo $answer->ToJSON();
        break;
    case "DeleteAnswer":
        $answer= new Answer($objData->data);
        echo $answer->DeleteAnswer();
        break;
    case "DeleteQuestion":
        $question = new Question($objData->data);
        echo $question->DeleteQuestion();
        break;
    case "SaveUserAnswer":
        $userAnswer = $objData->data;
	$qType = $userAnswer->sQuestionType; 
	error_log("STARTING TO SAVE::::");
try{

	if(is_array($userAnswer->idUsersAnswer)){

		//if its a checkbox delete all answers
		if($qType == "checkbox"){
			$delQuery = "delete from userdata where question=:question and survey=:survey and user=:user";
			$params = array(':question'=>$userAnswer->idQuestionID,':survey'=>$userAnswer->idSurvey,':user'=>$objData->userID);
			$sql = new SQLConnection();
			$sql->DoDeleteQuery($delQuery,$params);
		}
		$count = 0;
		foreach($userAnswer->idUsersAnswer as $ans){
			$count++;
			$answer = new Answer($ans);
			error_log("ans: ".$ans);
			 $question = new Question($userAnswer->idQuestionID);
				$question->idUsersAnswer = $ans;
				//$question->sUsersAnswerText = $ans->sAnswerText;
			if($question->SaveUserAnswer($objData->userID,$answer,$qType)!=1){
				throw new Exception("Problem Saving Answer");
			}
		}
	echo 1;
	}else{
		$answer = new Answer($userAnswer->idUsersAnswer);
			$question = new Question($answer->idQuestionID);
			$question->idUsersAnswer = $answer->idAnswerID;
			$question->sUsersAnswerText = $userAnswer->sUsersAnswerText;

		if($question->SaveUserAnswer($objData->userID,$answer,$qType)!=1){
			throw new Exception("Problem Saving Answer");
		}else{
			echo 1;
		}

	}
	}catch(Exception $e){
	echo $e->getMessage();
}
        break;
    case "CopySurvey":
        $surveyID = $objData->data;
        $survey = new Survey($surveyID);
        $newID = $objData->newID;
        $companyID = $objData->companyID;

        //loop through and set all the IDs to null
        $survey->idSurveyID = $newID;
        $survey->iCompany = $companyID;

        for($x=0;$x<count($survey->qcQuestions);$x++){
            $survey->qcQuestions[$x]->idQuestionID=0;
            $survey->qcQuestions[$x]->idSurvey=0;

            for($y=0;$y>count($survey->qcQuestions[$x]->aAnswers);$y++){
                $survey->qcQuestions[$x]->aAnswers[$y]->idAnswerID = 0;
                $survey->qcQuestions[$x]->aAnswers[$y]->idSurveyID = 0;
                $survey->qcQuestions[$x]->aAnswers[$y]->idQuestionID = 0;
                $survey->qcQuestions[$x]->aAnswers[$y]->idTriggers = 0;
            }
        }

        echo $survey->ToJSON();
        break;

}



?>
