<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 4/20/13
 * Time: 8:07 PM
 * To change this template use File | Settings | File Templates.
 */


require_once('./Classes/SQLConnection.php');

class SQLConnectionTest extends \PHPUnit_Framework_TestCase {

    public function testCanRunAtAll(){
        $x = 1;
        $this->assertEquals(1,$x);
    }

    public function testSQLConnectionTest(){

        $sql = new SQLConnection();
        $sql->TestSQLConnection();
    }

    public function testDoSelectQuery(){
        $sql = new SQLConnection();
        $results = $sql->DoSelectQuery("SELECT id FROM users");
        $this->assertGreaterThan(1,count($results));
    }

}