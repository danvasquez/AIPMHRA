<?php

require_once("/var/www/ha/AIPMHRA/php/CopyController.php");
foreach (glob("/var/www/ha/AIPMHRA/php/classes/*.php") as $filename)
{
	try{
		require_once($filename);
	}catch(Exception $e){
		print $e->getMessage();
	}

}

class CopyControllerTest extends PHPUnit_Framework_TestCase{

	public function testLoadClass(){
		$a = new CopyController(2,1);
		$this->assertInstanceOf("CopyController",$a);
	}

	public function testControllerConstructor(){
		$a = new CopyController(2,1);
		$this->assertEquals(2,$a->getSurveyCopyFrom());
	}

	public function testGetSurveyBeingCopied(){
		$a = new CopyController(14,1);
		$b = $a->GetSurveyBeingCopied();
		$this->assertInstanceOf("Survey",$b);

		$this->assertGreaterThan(0,count($b->qcQuestions));
	}

	public function testCopyTheSurvey(){
		$a = new CopyController(12,23);

		$b = $a->CopyTheSurvey();

		$this->assertInstanceOf("Survey",$b);
		$this->assertGreaterThan(0,count($b->qcQuestions));
	}


}