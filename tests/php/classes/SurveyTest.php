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

class SurveyTest extends PHPUnit_Framework_TestCase{

	public function testLoadClass(){
		$c = new Survey();
		$this->assertInstanceOf("Survey",$c);
	}

	public function testSelectQuery(){

	}

}