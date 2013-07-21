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

		function __construct($_copyFrom, $_copyTo){
			$this->surveyToCopyFrom = $_copyFrom;
			$this->surveyToCopyTo = $_copyTo;
			$this->SurveyBeingCopied = new Survey($this->surveyToCopyFrom);
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
	}


