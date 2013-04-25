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

error_log("CONTROLLER HIT".time());
$data = file_get_contents("php://input");
$jsonData = json_decode($data);
$updatedSurveyData = $jsonData->data;

$idnum = $updatedSurveyData->idSurveyID;
$idnum = intval($idnum);

$bIsNewSurvey = true;

if($idnum != null && $idnum>0){
    error_log("Existing Survey Identified");
    $bIsNewSurvey = false;
}

if($bIsNewSurvey){
    error_log("Getting Blank Survey");
    $existingSurveyData = new Survey();
}else{
    $existingSurveyData = new Survey($idnum,"ALL");
}
//print_r($updatedSurveyData);
error_log("TITLE:".$updatedSurveyData->sTitle);
$existingSurveyData->iCompany = $updatedSurveyData->iCompany;
$existingSurveyData->sPreamble = $updatedSurveyData->sPreamble;
$existingSurveyData->sPreambleSpanish = $updatedSurveyData->sPreambleSpanish;
$existingSurveyData->bResultsAvailable = $updatedSurveyData->bResultsAvailable;
$existingSurveyData->sStatus = $updatedSurveyData->sStatus;
$existingSurveyData->sTitle = $updatedSurveyData->sTitle;
$existingSurveyData->sTitleSpanish = $updatedSurveyData->sTitleSpanish;

//$existingSurveyData->qcQuestions = array(); //reinitialize it, we have to stick our new values in


$existingSurveyData->qcQuestions = $updatedSurveyData->qcQuestions;

$iResult = $existingSurveyData->SaveSurvey(); //going to get back either 0 for update failed, 1 for update succeeded, or the new id

try{
    if($iResult>1){   //means it's a new survey that was just inserted
        foreach($updatedSurveyData->qcQuestions as $question){
            //questions
            $tempQuestion = new Question($question->idQuestionID,"ALL");
            $tempQuestion->idSurvey = $iResult;
            $tempQuestion->iQuestionOrder = $question->iQuestionOrder;
            $tempQuestion->sPreamble = $question->sPreamble;
            $tempQuestion->sPreambleSpanish = $question->sPreambleSpanish;
            $tempQuestion->sQuestionType = $question->sQuestionType;
            $tempQuestion->sText = $question->sText;
            $tempQuestion->sTextSpanish = $question->sTextSpanish;
            $iNewQuestionID = $tempQuestion->SaveQuestion();

            //answers
            foreach($question->aAnswers as $answer){
                $tempAnswer = new Answer($answer->idAnswerID);
                $tempAnswer->idSurveyID = $iResult;
                $tempAnswer->idQuestionID = $iNewQuestionID;
                $tempAnswer->iOrder = $answer->iOrder;
                $tempAnswer->idTriggers = $answer->idTriggers;
                $tempAnswer->sAnswerText = $answer->sAnswerText;
                $tempAnswer->sAnswerTextSpanish = $answer->sAnswerTextSpanish;
                $tempAnswer->sRiskFactorText = $answer->sRiskFactorText;
                $tempAnswer->sRiskFactorTextSpanish = $answer->sRiskFactorTextSpanish;
                $tempAnswer->SaveAnswer();
            }
        }
        $newSurvey = new Survey($iResult,"ALL");
    }else{ //means it's an existing survey that was just updated
        foreach($updatedSurveyData->qcQuestions as $question){
            //questions
            $tempQuestion = new Question($question->idQuestionID,"ALL");
            $tempQuestion->idSurvey = $idnum;
            $tempQuestion->iQuestionOrder = $question->iQuestionOrder;
            $tempQuestion->sPreamble = $question->sPreamble;
            $tempQuestion->sPreambleSpanish = $question->sPreambleSpanish;
            $tempQuestion->sQuestionType = $question->sQuestionType;
            $tempQuestion->sText = $question->sText;
            $tempQuestion->sTextSpanish = $question->sTextSpanish;
            $returnVal = $tempQuestion->SaveQuestion();
            if($returnVal>1){
                $tempQuestion->idQuestionID = $returnVal;
            }

            //answers
            foreach($question->aAnswers as $answer){
                $tempAnswer = new Answer($answer->idAnswerID);
                $tempAnswer->idSurveyID = $idnum;
                $tempAnswer->idQuestionID = $tempQuestion->idQuestionID;
                $tempAnswer->iOrder = $answer->iOrder;
                $tempAnswer->idTriggers = $answer->idTriggers;
                $tempAnswer->sAnswerText = $answer->sAnswerText;
                $tempAnswer->sAnswerTextSpanish = $answer->sAnswerTextSpanish;
                $tempAnswer->sRiskFactorText = $answer->sRiskFactorText;
                $tempAnswer->sRiskFactorTextSpanish = $answer->sRiskFactorTextSpanish;
                $tempAnswer->SaveAnswer();
            }
        }
        $newSurvey = new Survey($idnum,"ALL");
    }
} catch(Exception $e){
    die($e->getMessage());
}


    echo json_encode($newSurvey);

?>
