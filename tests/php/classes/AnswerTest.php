<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 7/20/13
 * Time: 4:54 PM
 * To change this template use File | Settings | File Templates.
 */

foreach (glob("/var/www/ha/AIPMHRA/php/classes/*.php") as $filename)
{
	try{
		require_once($filename);
	}catch(Exception $e){
		print $e->getMessage();
	}

}

class AnswerTest extends PHPUnit_Framework_TestCase{

	public function testLoadClass(){
		$a = new Answer();
		$this->assertInstanceOf("Answer",$a);
	}

	public function testGetResults(){

		$mockSql = $this->getMock("SqlConnection",array("DoSelectQuery"));
		$mockSql->expects($this->once())
			->method("DoSelectQuery")
			->will($this->returnValue(array(["num"=>1])));

		$a = new Answer(5,"ENGLISH");

		$this->assertEquals(1,$a->GetResults($mockSql));

	}

}