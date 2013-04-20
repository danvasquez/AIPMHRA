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
    private $pass = "";

    function SQLConnection(){

    }

    public function DoSelectQuery($_sqlQuery,$_params=array()){
        $this->sqlConnection = $this->MakeSQLConnection();
        $query = $this->sqlConnection->prepare($_sqlQuery,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $results = array();
        try{
            if($query->execute($_params)){
                error_log('execute save answer success');
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $results[]=$row;
                }
            }

            $this->sqlConnection = null;
            return $results;
        } catch (PDOException $e){
            print "Error!: " . $e->getMessage() . "<br/>Query:".$_sqlQuery;
            die();
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
               error_log("QUERY DELETE FAILED");
               return false;
           }
       } catch (PDOException $e){

           print "Error!: " . $e->getMessage() . "<br/>Query:".$_query;
           die();
       }
   }

    public function DoInsertQuery($_query,$_params){
        error_log("IN DO INSERTQUERY");
        $this->sqlConnection = $this->MakeSQLConnection();
        $query = $this->sqlConnection->prepare($_query,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $results = null;
        try{
            if($query->execute($_params)){
                error_log("SAVED ANSWER");
                $results =  $this->sqlConnection->lastInsertId();
                error_log($results);
                $this->sqlConnection = null;
            }else{
                error_log("SAVED ANSWER IS FALSE");
                $results = "0";
                $this->sqlConnection = null;
            }
        } catch (PDOException $e){
            error_log("HIT ERROR");
            error_log($e->getCode()."|".$e->getLine());
            print "Error!: " . $e->getMessage() . "<br/>Query:".$_query;
            die();
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
                error_log("QUERY UPDATE FAILED");
                return false;
            }
        } catch (PDOException $e){

            print "Error!: " . $e->getMessage() . "<br/>Query:".$_query;
            die();
        }
    }
    private function MakeSQLConnection(){
        try {
            $dbh = new PDO('mysql:host=localhost;dbname=aipmsurveys', $this->user, $this->pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbh;
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }
}
