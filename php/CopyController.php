<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 7/21/13
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

	foreach (glob("classes/*.php") as $filename)
	{
		require_once($filename);
	}

	class CopyController {

		private $surveyToCopyFrom;
		private $surveyToCopyTo;
		private $SurveyBeingCopied;
		private $SurveyBeingFilled;

		function __construct($_copyFrom, $_copyTo){
			$this->surveyToCopyFrom = $_copyFrom;
			$this->surveyToCopyTo = $_copyTo;
			$this->SurveyBeingCopied = new Survey($this->surveyToCopyFrom);
		}

		public function CopyTheSurvey(){

			//loop through the questions in the survey being copied
			foreach($this->SurveyBeingCopied->qcQuestions as $q){
				$qid = $this->SaveQuestion($q);

				if($qid > 0){
					//loop through the answers
					foreach($q->aAnswers as $a){
						$aid = $this->SaveAnswer($a,$qid);
					}
				}
			}

			//get the new survey and return it
			return (new Survey($this->surveyToCopyTo));
		}

		public function SaveAnswer( Answer $a,$qid){
			$oldID = $a->idAnswerID;

			$a->idAnswerID = null;
			$a->idQuestionID = $qid;
			$a->idSurveyID = $this->surveyToCopyTo;

			$newID = 0;

			try{
				$newID = $a->SaveAnswer(); //return the last insert id
				if($newID <= 0){
					throw new Exception("Nothing inserted");
				}
			}catch(Exception $e){
				error_log("Answer {$oldID} did not save. error:{$e->getMessage()}");
			}

			return $newID;

		}

		public function SaveQuestion(Question $q){
			//reset the new info
			$oldID = $q->idQuestionID;

			$q->idQuestionID = null;
			$q->idSurvey = $this->surveyToCopyTo;

			$newID = 0;

			try{
				$newID = $q->SaveQuestion(); //return the last insert id
				if($newID <= 0){
					throw new Exception("Nothing inserted");
				}
			}catch(Exception $e){
				error_log("question {$oldID} did not save. error:{$e->getMessage()}");
			}

			return $newID;

		}

		public function getSurveyCopyFrom(){
			return $this->surveyToCopyFrom;
		}

		public function GetSurveyBeingCopied(){
			return $this->SurveyBeingCopied;
		}

	}

/*****************
 * procedural code
 */

	$data = file_get_contents("php://input");
	$objData = json_decode($data);

	if(isset($objData) && $objData != null){
		$controller = new CopyController($objData->copyFrom,$objData->copyTo);
		$x = $controller->CopyTheSurvey();
		$x->ToJSON();
	}

	return 0;


