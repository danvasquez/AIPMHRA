<?php

foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}

function CopySurvey(){
    $surveyID = 6;
    $survey = new Survey(6);

    //loop through and set all the IDs to null
    $survey->idSurveyID = null;
    for($x=0;$x<count($survey->qcQuestions);$x++){
        $survey->qcQuestions[$x]->idQuestionID=null;
        $survey->qcQuestions[$x]->idSurvey=null;

        for($y=0;$y>count($survey->qcQuestions[$x]->aAnswers);$y++){
            $survey->qcQuestions[$x]->aAnswers[$y]->idAnswerID = null;
            $survey->qcQuestions[$x]->aAnswers[$y]->idSurveyID = null;
            $survey->qcQuestions[$x]->aAnswers[$y]->idQuestionID = null;
            $survey->qcQuestions[$x]->aAnswers[$y]->idTriggers = null;
        }
    }

    var_dump($survey);
}

CopySurvey();

