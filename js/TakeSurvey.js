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
        var fileName = "C"+$scope.$root.LoggedInUser.idCompanyID+"S"+TheSurveyID+"_Results.pdf"
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
    $scope.$root.ErrorMessage = "";
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
                }else{
                    $scope.SurveyFinished = false;
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
        
	//if its a checkbox, loop through and add to answer array
	if($scope.ActiveQuestion.sQuestionType=="checkbox"){
		//clear the array first, it's persistent otherwise
		$scope.ActiveQuestion.idUsersAnswer = [];
		for(var x=0;x<$scope.ActiveQuestion.aAnswers.length;x++){
			if($scope.ActiveQuestion.aAnswers[x].checked == true){
				$scope.ActiveQuestion.idUsersAnswer.push($scope.ActiveQuestion.aAnswers[x].idAnswerID);
			}
		}
	}

        $http.post("./php/SurveyController.php", { "criteria":"SaveUserAnswer","data":$scope.ActiveQuestion,"userID":$scope.$root.LoggedInUser.idUserID,"qtype":$scope.sQuestionType}).
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
                            //loop through all answers in the question
                            for(var y=0;y<$scope.survey.qcQuestions[i].aAnswers.length;y++){
                                //is the question a multiple option?
                                if($.isArray($scope.ActiveQuestion.idUsersAnswer)){
                                    //loop through all the answers given
                                    $scope.ActiveQuestion.idUsersAnswer.map(function(item){
                                        if($scope.survey.qcQuestions[i].aAnswers[y].idAnswerID==item){
                                            triggerq = $scope.survey.qcQuestions[i].aAnswers[y].idTriggers;
                                        }
                                    });
                                }else{
                                    if($scope.survey.qcQuestions[i].aAnswers[y].idAnswerID==$scope.ActiveQuestion.idUsersAnswer){
                                        triggerq = $scope.survey.qcQuestions[i].aAnswers[y].idTriggers;
                                    }
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
        
        triggerq = triggerq || 0;

	//loop through questions to add to the sidebar
        for(var x=0;x<$scope.survey.qcQuestions.length;x++){
            var xquestion = $scope.survey.qcQuestions[x];
            if(xquestion.idUsersAnswer!=0 && xquestion.iIsTrigger<=0){
                $scope.SidebarQuestionList[x] = xquestion;
            }
        }

      //set the next active question
        for(var i=0;i<$scope.survey.qcQuestions.length;i++){
            var question = $scope.survey.qcQuestions[i];
            
            //if(question.idQuestionID == $scope.ActiveQuestion)
            if(question.idQuestionID==triggerq){
                
                $scope.ActiveQuestion = question;
                $scope.AddCheckedProperty();
                break;
            }else if(question.idUsersAnswer.length==0 && question.iIsTrigger<=0)
            {
            
                if(question.sPreamble != null && $.trim(question.sPreamble) !=""){
                    $scope.PreambleDismissed = false;}else{$scope.PreambleDismissed=true;
                }

                $scope.ActiveQuestion = question;
                if(question.sQuestionType=="Textbox"){
                        $scope.ActiveQuestion.idUsersAnswer = question.aAnswers[0].idAnswerID;
                }
                break;
            }
            //survey complete
            if(i==$scope.survey.qcQuestions.length -1){
                $scope.ActiveQuestion = $scope.survey.qcQuestions[0];
                $scope.AddCheckedProperty();
                $scope.PreambleDismissed=true;
                $scope.SurveyFinished = true;
            }else{
                $scope.SurveyFinished = false;
            }
        }
		
        $scope.AddCheckedProperty();

      }

    $scope.AddCheckedProperty = function(){
        //add in the checked property if needed
        if($scope.ActiveQuestion.sQuestionType=="checkbox"){
            for(var x=0;x<$scope.ActiveQuestion.aAnswers.length;x++){
                var answerID = $scope.ActiveQuestion.aAnswers[x].idAnswerID;
                var currentAnswers = $scope.ActiveQuestion.idUsersAnswer;
                $scope.ActiveQuestion.aAnswers[x]["checked"]=false;
                currentAnswers.map(function(item){
                    if(item == answerID){
                        $scope.ActiveQuestion.aAnswers[x]["checked"]=true;
                    }
                });
            }
        }
    }

    $scope.DismissPreamble = function(){
        
        $scope.PreambleDismissed = true;
    }

    $scope.GoToQuestion=function(question){
        $scope.ActiveQuestion = question;
        $scope.AddCheckedProperty();
    }

}
