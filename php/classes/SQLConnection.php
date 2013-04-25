<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/8/13
 * Time: 9:25 PM
 * To change this template use File | Settings | File Templates.
 */

class SQLConnection
{
    public $sqlConnection = null;
    private $user = "root";
    private $pass = "danV6394";

    function SQLConnection(){

    }

    public function DoSelectQuery($_sqlQuery,array $_params = null){
        $this->sqlConnection = $this->MakeSQLConnection();
        $query = $this->sqlConnection->prepare($_sqlQuery,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $results = array();
        try{
            if($query->execute($_params)){
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $results[]=$row;
                }
            }

            $this->sqlConnection = null;
            return $results;
        } catch (PDOException $e){
            throw new Exception("SQLConnection'\DoSelectQuery Failed:".$e->getMessage());
        }

    }

   public function DoDeleteQuery($_query,$_params){
       $this->sqlConnection = $this->MakeSQLConnection();
       $query = $this->sqlConnection->prepare($_query,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       try{
           if($query->execute($_params)){
               $this->sqlConnection = null;
               return true;
           }else{
               $this->sqlConnection = null;
               return false;
           }
       } catch (PDOException $e){
            throw new \Exception($e->getMessage());
       }
   }

    public function DoInsertQuery($_query,$_params){
        $this->sqlConnection = $this->MakeSQLConnection();
        $query = $this->sqlConnection->prepare($_query,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $results = null;
        try{
            if($query->execute($_params)){

                $results =  $this->sqlConnection->lastInsertId();

                $this->sqlConnection = null;
            }else{

                $results = "0";
                $this->sqlConnection = null;
            }
        } catch (PDOException $e){
            throw new \Exception($e->getMessage());
        }

        return $results;
    }

    public function DoUpdateQuery($_query,$_params){
        $this->sqlConnection = $this->MakeSQLConnection();
        $query = $this->sqlConnection->prepare($_query,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try{
            if($query->execute($_params)){
                $this->sqlConnection = null;
                return true;
            }else{
                $this->sqlConnection = null;

                return false;
            }
        } catch (PDOException $e){
            throw new \Exception($e->getMessage());
        }
    }
    private function MakeSQLConnection(){
        try {
            $dbh = new PDO('mysql:host=localhost;dbname=aipmsurveys', $this->user, $this->pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbh;
        } catch (PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function TestSQLConnection(){
        $conn=null;

        try{
            $conn = $this->MakeSQLConnection();
            $conn = null;
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}
