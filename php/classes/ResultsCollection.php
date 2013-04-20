<?php
/**
 * Created by JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/20/13
 * Time: 1:29 AM
 * To change this template use File | Settings | File Templates.
 */
foreach (glob("classes/*.php") as $filename)
{
    require_once($filename);
}
class ResultsCollection
{
    public $idSurveyID=0;
    public $survey = null;
    public $usersCompleted = array();
    public $usersAttempted = array();
    public $usersAvailable = array();
    public $resultsStats = array();

    public $numberOfQuestions = 0;

    function ResultsCollection($_surveyID=0){
        $this->idSurveyID = $_surveyID;
        $this->survey = new Survey($this->idSurveyID);
        $this->numberOfQuestions = count($this->survey->qcQuestions);

        error_log('getting attempted users');
        //first get a list of all the users who have answered any questions
        $this->GetAttemptedUsers();

        error_log('getting completed users');
        //now check and see which of these answered all the questions
        $this->GetCompletedUsers();

        error_log('getting all users');
        //get the number of users available (by company);
        $this->usersAvailable = new UserCollection();
        $this->usersAvailable->GetByCompanyID($this->survey->iCompany);

    }

    public function GetAttemptedUsers(){
        $query = "SELECT user from userdata where survey=:surveyID group by user order by user ASC ";
        $params = array(":surveyID"=>$this->idSurveyID);

        $sql = new SQLConnection();
        $result = $sql->DoSelectQuery($query,$params);

        foreach($result as $row){
            $this->usersAttempted[] = $row['user'];
        }
    }


    public function GetCompletedUsers(){
        foreach($this->usersAttempted as $u){
            $query = "SELECT count(user) as num FROM userdata where user=:userid and survey=:surveyid";
            $params = array(':userid'=>$u,'surveyid'=>$this->idSurveyID);
            $sql = new SQLConnection();

            $result = $sql->DoSelectQuery($query,$params);
            foreach($result as $row){
                error_log("user count from sql=".$row['num']." from php ".$this->numberOfQuestions);
                if($row['num']>=$this->numberOfQuestions){
                    $this->usersCompleted[] = $u;
                }
            }
        }
    }

    public function ToJSON(){
        echo json_encode($this);
    }
}
