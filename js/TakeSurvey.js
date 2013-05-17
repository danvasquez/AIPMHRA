function AdminResultsCtrl($scope,$routeParams,$http){
    $scope.$root.CheckLogin(2);
    var TheSurveyID = 0;
    if($routeParams.surveyID != null){
        TheSurveyID = $routeParams.surveyID;
    }
    $scope.url = "./php/SurveyController.php";
    $http.post($scope.url, { "criteria":"ResultsCollection","data":TheSurveyID,"language":$scope.ActiveLanguage,"userID":$scope.$root.LoggedInUser.idUserID}).
        success(function(data, status) {
            $scope.status = status;
            $scope.data = data;
            $scope.results = data;
        })
        .
        error(function(data, status) {
            $scope.data = data || "Request failed";
            $scope.status = status;
        });

    $scope.DownloadPDF = function(){
        var fileString = document.documentElement.outerHTML;
        var fileName = "C"+$scope.$root.LoggedInUser.companyID+"S"+TheSurveyID+"_Results.pdf"
        $http.post("./php/PDFController.php",{"fileString":fileString,"fileName":fileName}).
            success(function(data,status){

                window.open("./php/"+data);
            })
            .error(function(data,status){

            });
    }

}
function UserResultsCtrl($scope,$routeParams,$http){
    $scope.$root.CheckLogin(3);
    $scope.ActiveLanguage = "ENGLISH";
    $scope.SurveyFinished=false;
    var UserAnswers = new Array();
    var TheSurveyID = 0;
    if($routeParams.surveyID != null){
        TheSurveyID = $routeParams.surveyID;
    }

    $scope.DownloadPDF = function(){
        var fileString = document.documentElement.outerHTML;
        var fileName = "U"+$scope.$root.LoggedInUser.idUserID+"S"+TheSurveyID+"_Results.pdf"
        $http.post("./php/PDFController.php",{"fileString":fileString,"fileName":fileName}).
            success(function(data,status){

                window.open("./php/"+data);
            })
            .error(function(data,status){
            console.log('NO RESPONSE');
        });
    }

    $scope.GetResults = function(){
        if(TheSurveyID >0){
            $scope.url = "./php/SurveyController.php";
            $http.post($scope.url, { "criteria":"surveyID","data":TheSurveyID,"language":$scope.ActiveLanguage,"userID":$scope.$root.LoggedInUser.idUserID}).
                success(function(data, status) {
                    $scope.status = status;
                    $scope.data = data;

                    $scope.survey = data;
                    $scope.SidebarQuestionList = new Array();

                    for(var x=0;x<$scope.survey.qcQuestions.length;x++){
                        var xquestion = $scope.survey.qcQuestions[x];
                        if(xquestion.idUsersAnswer!=0 && xquestion.iIsTrigger<=0){
                            $scope.SidebarQuestionList[x] = xquestion;
                        }
                    }

                    if($scope.survey.numTopQuestion <= $scope.SidebarQuestionList.length){
                        $scope.SurveyFinished=true;
                    }else{
                    }

                })
                .
                error(function(data, status) {
                    $scope.data = data || "Request failed";
                    $scope.status = status;
                });
        }
    }


    $scope.ChangeLanguage=function(lang){
        $scope.ActiveLanguage = lang;
        $scope.GetResults();
    }

    $scope.GetResults();
}
function SurveyLandingCtrl($scope,$routeParams,$http){
    $scope.$root.CheckLogin(3);

    $scope.SurveyFinished=false;
    var TheSurveyID = 0;
    if($routeParams.surveyID != null){
        TheSurveyID = $routeParams.surveyID;
    }

    if(TheSurveyID >0){
        $scope.url = "./php/SurveyController.php";
        $http.post($scope.url, { "criteria":"surveyID","data":TheSurveyID,"language":"ALL","userID":$scope.$root.LoggedInUser.idUserID}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;

                $scope.survey = data;
                $scope.SidebarQuestionList = new Array();

                for(var x=0;x<$scope.survey.qcQuestions.length;x++){
                    var xquestion = $scope.survey.qcQuestions[x];
                    if(xquestion.idUsersAnswer!=0 && xquestion.iIsTrigger<=0){
                        $scope.SidebarQuestionList[x] = xquestion;
                    }
                }

                if($scope.survey.numTopQuestion <= $scope.SidebarQuestionList.length){
                    $scope.SurveyFinished=true;
                }
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $scope.StartSurvey=function(lang){
        if(lang=="SPANISH"){
            $scope.$root.SurveyLangage = "SPANISH";
        }
        window.location.href="./#/TakeSurvey/"+TheSurveyID;
    }

}
function TakeSurveyCtrl($scope,$routeParams,$http){
    $scope.$root.CheckLogin(3);

    $scope.ActiveQuestion = null;
    $scope.PreambleDismissed = false;
    $scope.SidebarQuestionList = new Array();
    $scope.SurveyFinished=false;

    var TheSurveyID = 0;
    if($routeParams.surveyID != null){
        TheSurveyID = $routeParams.surveyID;
    }

    $scope.SaveAnswer = function(){
        //info needed
        //surveyid
        if($scope.ActiveQuestion){
            console.log("surveyID");
            console.log($scope.ActiveQuestion.idSurvey);
            //questionid
            console.log("questionID");
            console.log($scope.ActiveQuestion.idQuestionID);
            //answerid
            console.log("useranswerID");
            console.log($scope.ActiveQuestion.idUsersAnswer);
            //userid
            console.log("userID");
            console.log($scope.$root.LoggedInUser.idUserID);
            //answertext
            console.log("answertext");
            console.log($scope.ActiveQuestion.sUsersAnswerText);
        }


        $http.post("./php/SurveyController.php", { "criteria":"SaveUserAnswer","data":$scope.ActiveQuestion,"userID":$scope.$root.LoggedInUser.idUserID}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                var triggerq = 0;
                if(data=='1'){
                    //set the useranswer and text in the main survey model
                    for(var i=0;i<$scope.survey.qcQuestions.length;i++){
                        if($scope.survey.qcQuestions[i].idQuestionID == $scope.ActiveQuestion.idQuestionID){

                            $scope.survey.qcQuestions[i].idUsersAnswer=$scope.ActiveQuestion.idUsersAnswer;
                            $scope.survey.qcQuestions[i].sUsersAnswerText=$scope.ActiveQuestion.sUsersAnswerText;

                            //check for a trigger
                            console.log("looking for a trigger");
                            for(var y=0;y<$scope.survey.qcQuestions[i].aAnswers.length;y++){
                                if($scope.survey.qcQuestions[i].aAnswers[y].idAnswerID==$scope.ActiveQuestion.idUsersAnswer){
                                    triggerq = $scope.survey.qcQuestions[i].aAnswers[y].idTriggers;
                                    console.log("saved answer and got trigger"+triggerq);
                                }
                            }
                        }
                    }
                    $scope.$root.ErrorMessage = "Saved!";
                    $scope.GetQuestion(triggerq);
                }else{
                    $scope.$root.ErrorMessage = "Could Not Save Answer";
                }
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    if(TheSurveyID >0){
        $scope.$root.ErrorMessage="";
        $scope.url = "./php/SurveyController.php";
        $http.post($scope.url, { "criteria":"surveyID","data":TheSurveyID,"language":$scope.$root.SurveyLangage,"userID":$scope.$root.LoggedInUser.idUserID}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                console.log(data);
                $scope.survey = data;

                $scope.GetQuestion();
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $scope.GetQuestion = function(triggerq){
        //get Next Question
        console.log("SURVEY IS: "+$scope.survey);
        console.log('x:'+triggerq);
        triggerq = triggerq || 0;

        for(var x=0;x<$scope.survey.qcQuestions.length;x++){
            var xquestion = $scope.survey.qcQuestions[x];
            if(xquestion.idUsersAnswer!=0 && xquestion.iIsTrigger<=0){
                $scope.SidebarQuestionList[x] = xquestion;
            }
        }

        if($scope.survey.numTopQuestion <= $scope.SidebarQuestionList.length + 1){
            $scope.SurveyFinished=true;
        }


        for(var i=0;i<$scope.survey.qcQuestions.length;i++){
            var question = $scope.survey.qcQuestions[i];
            console.log("checking for trigger "+question.idQuestionID+": "+triggerq);
            //if(question.idQuestionID == $scope.ActiveQuestion)
            if(question.idQuestionID==triggerq){
                console.log('trigger q set');
                $scope.ActiveQuestion = question;
                break;
            }else if(question.idUsersAnswer==0 && question.iIsTrigger<=0){

                if(question.sPreamble != null && $.trim(question.sPreamble) !=""){$scope.PreambleDismissed = false;}else{$scope.PreambleDismissed=true;}

                $scope.ActiveQuestion = question;

                if(question.sQuestionType=="Textbox"){

                    $scope.ActiveQuestion.idUsersAnswer = question.aAnswers[0].idAnswerID;

                }

                i=$scope.survey.qcQuestions.length+1;
                console.log("AC="+$scope.ActiveQuestion);
            }
        }

        //if there's no Active Question at this point it means the user has already done the survey, go back to the beginning
        if($scope.ActiveQuestion == null){
            console.log('never got a trigger');
            $scope.ActiveQuestion = $scope.survey.qcQuestions[0];

            if($scope.ActiveQuestion.sPreamble != null && $.trim($scope.ActiveQuestion.sPreamble) !=""){
                console.log("sPreamble should not be found is:"+$scope.ActiveQuestion.sPreamble);
                $scope.PreambleDismissed = false;
            }else{
                console.log("sPreamble is:"+$scope.ActiveQuestion.sPreamble);
                $scope.PreambleDismissed=true;
            }

        }
    }

    $scope.DismissPreamble = function(){
        console.log("dismiss function");
        $scope.PreambleDismissed = true;
    }

    $scope.GoToQuestion=function(question){
        $scope.ActiveQuestion = question;
    }

}
