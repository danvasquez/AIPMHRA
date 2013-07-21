/**
 * Created with JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/10/13
 * Time: 9:29 PM
 * To change this template use File | Settings | File Templates.
 */
function EnrollmentCtrl($scope,$http){
    $scope.$root.CheckLogin(1);

    $scope.FileName="";
    $scope.FileType="";
    $scope.FileExt="";
    $scope.FileContents="";
    $scope.FileHeaders="";
    $scope.NationalIDColumn=0;
    $scope.CompanyID=0;
    $scope.UsersList=null;
    $scope.FoundUsers=new Array();
    $scope.$root.ErrorMessage="";

    $scope.$root.FillCompanyFilterList();

    $scope.getTimes=function(n){
        var x = new Array();
        for(var i=0;i<n;i++){
            x.push(i);
        }
        return x;
    };

    $scope.CheckUsers=function(){
        //get all the users of this company
        $scope.url = './php/UserController.php';
        

            $http.post($scope.url, { "criteria":"GetUsers","iRole":$scope.$root.LoggedInUser.iRole,"idCompanyID":$scope.companyID}).
                success(function(data, status) {
                    $scope.status = status;
                    $scope.data = data;
                    
                    $scope.UsersList = data;

                    $scope.$root.ErrorMessage = "Looking for users...";
                    //loop through all the list
                    for(var n=0;n<$scope.FileContents.length;n++){
                        var row = $scope.FileContents[n];
                        var pid = row[$scope.NationalIDCol];
                        for(var m=0;m<$scope.UsersList.length;m++){
                            var xrow = $scope.UsersList[m];
                            var xpid = xrow.sNationalID;

                            if($.trim(pid)== $.trim(xpid)){
                                $scope.FoundUsers.push(xrow);
                            }else{
                                //do nothing
                            }
                        }
                    }
                    $scope.$root.ErrorMessage = "Done.";
                })
                .
                error(function(data, status) {
                    $scope.data = data || "Request failed";
                    $scope.$root.ErrorMessage = "Could not get users";
                    $scope.status = status;
                });


    }

    $scope.CheckUpload=function(evt){
        var file = evt.target.files[0]; // FileList object

        $scope.$apply(function(){
            $scope.FileName = file.name;
            $scope.FileType = file.type;
        });

        var reader = new FileReader();
        // Closure to capture the file information.
        reader.onload = (function(theFile) {
                var allTextLines = theFile.target.result.split(/\r\n|\n/);
                
                var headers = allTextLines[0].split(',');
                var lines = [];

                for (var i=1; i<allTextLines.length; i++) {
                    var data = allTextLines[i].split(',');
                    if (data.length == headers.length) {

                        var tarr = [];
                        for (var j=0; j<headers.length; j++) {
                            tarr.push(data[j]);
                        }
                        lines.push(tarr);
                    }
                }
            $scope.$apply(function(){
                $scope.FileContents = lines;
                $scope.FileHeaders = headers;
            });

        });

        // Read in the image file as a data URL.
        reader.readAsText(file);




    }

    $(function(){
        // Check for the various File API support.
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            // Great success! All the File APIs are supported.
            document.getElementById('file').addEventListener('change', $scope.CheckUpload, false);
        } else {
            alert('The File APIs are not fully supported in this browser. Please try a more modern browser for this function.');
        }
    });



}
function CompanyAdminCtrl($scope,$http){
    $scope.$root.CheckLogin(1);
    $scope.url = './php/CompanyController.php';
    $scope.companies = null;

    $scope.GetSurveys = function(){
        $http.post($scope.url, { "criteria":"GetCompanies","iRole":$scope.$root.LoggedInUser.iRole,"idCompanyID":$scope.$root.LoggedInUser.idCompanyID}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                
                $scope.companies = data;
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $scope.GetCompanyDetails = function(companyID){
        window.location.href = '#/company/'+companyID;
    }

    $scope.GetSurveys();

}
function UserAdminCtrl($scope,$http){
    $scope.$root.CheckLogin(2);

    $scope.url = './php/UserController.php';
    $scope.users = null;

    $scope.GetUsers = function(){
        $http.post($scope.url, { "criteria":"GetUsers","iRole":$scope.$root.LoggedInUser.iRole,"idCompanyID":$scope.$root.LoggedInUser.idCompanyID}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                
                $scope.users = data;
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.$root.ErrorMessage = "Could not get users";
                $scope.status = status;
            });
    }

    $scope.GetUserDetails = function(userID){
        window.location.href = '#/user/'+userID;
    }

    $scope.GetUsers();
    $scope.$root.FillCompanyFilterList();

}
function RegisterCtrl($scope,$routeParams,$http,$cookies){

    $http.post('./php/UserController.php', { "criteria":"GetSingleUser","data":0}).
        success(function(data, status) {
            $scope.status = status;
            $scope.data = data;
            
            $scope.user = data;
        })
        .
        error(function(data, status) {
            $scope.data = data || "Request failed";
            $scope.$root.ErrorMessage = "Could not get user";
            $scope.status = status;
        });

    $scope.SaveUser = function(){
        //check if user ID exists already
        $http.post("./php/UserController.php", { "criteria":"CheckUserIDExists","data":$scope.user.sUserID}).
            success(function(data, status) {
                if(data==1){
                    //it already exists
                    
                    $scope.$root.ErrorMessage="That User ID is already registered.";
                }else{
                    
                    //check that the company Code is good
                    $http.post("./php/CompanyController.php", { "criteria":"CheckCompanyCode","data":$scope.sCompanyCode}).
                        success(function(data, status) {
                            
                            if(data.idCompanyID != null && data.idCompanyID!='0'){
                                //it already exists
                                //do stuff to get correct company info before saving
                                $scope.user.idCompanyID = data.idCompanyID;
                                $scope.user.sCompanyName = data.sCompanyName;
                                $scope.user.urlCompanyLogo = data.urlCompanyLogo;

                                //save the user
                                if($scope.NewPassword.length>0){
                                    $scope.user.pPassword = $scope.NewPassword;
                                }

                                $http.post('./php/UserChangesController.php', { "data":$scope.user}).
                                    success(function(data, status) {
                                        $scope.status = status;
                                        $scope.data = data;
                                        if(data == '0'){
                                            $scope.$root.ErrorMessage = "Problem: Could not save user";
                                        }else if(data>"0"){
                                            $scope.user.idUserID = data;
                                            $scope.$root.ErrorMessage = "User Saved";
                                            $cookies.LoggedInUserID = data;
                                            $scope.$root.FillUserData(data);
                                            window.location.href="#/home";
                                        }else{
                                            $scope.user.idUserID = data;
                                            $scope.$root.ErrorMessage = "User Could Not Be Saved";
                                        }

                                    })
                                    .
                                    error(function(data, status) {
                                        $scope.data = data || "Request failed";
                                        $scope.$root.ErrorMessage = "Could not get user";
                                        $scope.status = status;
                                    });

                            }else{
                                $scope.$root.ErrorMessage="Invalid Company Code";
                            }

                        })
                        .
                        error(function(data, status) {
                            $scope.data = data || "Request failed";
                            $scope.$root.ErrorMessage = "Could not get user data";
                            $scope.status = status;
                        });
                }

            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.$root.ErrorMessage = "Could not get user data";
                $scope.status = status;
            });

        //check if company code exists

        //save

        //retrieve the login stuff and redirect as normal
    }
}
function UserEditCtrl($scope,$routeParams,$http){
    $scope.$root.CheckLogin(2);
    $scope.userID = $routeParams.userID;
    $scope.NewPassword = "";
    $scope.HasNewPassword = false;
    $scope.NewPasswordIsValid = false;
    

    $scope.DeleteUser = function(){
        $http.post("./php/UserController.php", { "criteria":"DeleteUser","data" : $scope.userID}).
            success(function(data, status) {
                $scope.status = status;
                if(data=="1"){
                    $scope.$root.ErrorMessage = "User Deleted";
                    window.location.href = '#/admin-users';
                }else{
                    $scope.$root.ErrorMessage =  "Could not delete user";
                }
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $scope.SaveUser = function(user){
        //check for a password

        if($scope.NewPassword.length>0){
            user.pPassword = $scope.NewPassword;
        }

        $http.post('./php/UserChangesController.php', { "data":user}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
               
                if(data == '0'){
                    $scope.$root.ErrorMessage = "Problem: Could not save user";
                }else if(data=='1'){
                    $scope.$root.ErrorMessage = "User Saved";
                }else{
                    $scope.user.idUserID = data;
                    $scope.$root.ErrorMessage = "User Saved";
                }

            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.$root.ErrorMessage = "Could not get user";
                $scope.status = status;
            });
    }


    $http.post('./php/UserController.php', { "criteria":"GetSingleUser","data":$scope.userID}).
        success(function(data, status) {
            $scope.status = status;
            $scope.data = data;
            $scope.user = data;
        })
        .
        error(function(data, status) {
            $scope.data = data || "Request failed";
            $scope.$root.ErrorMessage = "Could not get user";
            $scope.status = status;
        });

    $scope.$root.FillCompanyFilterList();

}
function CompanyEditCtrl($scope,$routeParams,$http){
    $scope.$root.CheckLogin(1);
    $scope.url = './php/CompanyController.php';
    $scope.companyID = $routeParams.companyID;
    

    $scope.UploadNewLogo = function(){
        $scope.$root.ErrorMessage = $('#frmUploadLogo').serialize();
   
  
    }

    $scope.DeleteCompany = function(){
        $http.post("./php/CompanyController.php", { "criteria":"DeleteCompany","data" : $scope.companyID}).
            success(function(data, status) {
                $scope.status = status;
                if(data=="1"){
                    $scope.$root.ErrorMessage = "Company Deleted";
                    window.location.href = '#/admin-companies';
                }else{
                    $scope.$root.ErrorMessage =  "Could not delete company";
                }
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $scope.SaveCompany = function(company){
        $http.post("./php/CompanyChangesController.php", { "data":company}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
  
                if(data>1){
                    $scope.company.idCompanyID = data;
                }else if(data==1){
                    $scope.$root.ErrorMessage = "Company Saved!";
                }else{
                    $scope.$root.ErrorMessage = "Could Not Save Company";
                }

            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
                $scope.$root.ErrorMessage = status;
            });
    }

    $http.post($scope.url, { "criteria":"GetCompanyByID","data":$scope.companyID}).
        success(function(data, status) {
            $scope.status = status;
            $scope.data = data;
            $scope.company = data;
        })
        .
        error(function(data, status) {
            $scope.data = data || "Request failed";
            $scope.status = status;
            $scope.$root.ErrorMessage = status;
        });

}

function UserSurveyCtrl($scope,$http){
    $scope.url = './php/SurveyController.php';
    $scope.GetSurveys = function(){
        var Criteria = "companyID";
        if($scope.$root.LoggedInUser.iRole==1){Criteria="allSurveys"}
        $http.post($scope.url, { "criteria":Criteria,"iRole":$scope.$root.LoggedInUser.iRole,"idCompanyID":$scope.$root.LoggedInUser.idCompanyID}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                $scope.surveys = data;
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $scope.TakeSurvey = function(surveyID){
        if(surveyID>0){
            window.location.href="./#/SurveyLanding/"+surveyID;
        }

    }

    $scope.GetSurveys();
}
function HomeCtrl($scope,$http){
    $scope.$root.CheckLogin(3);

    $scope.homeHTML = unescape($scope.$root.LoggedInUser.txtHometext);

    //$scope.$apply();
}

function ShowPass(){
    $('#passwordForm').toggle();
}
function LoginCtrl($scope,$http,$cookies){
    $scope.ShowPasswordForm=false;
    $scope.url = './php/LoginController.php';

    $scope.ShowPass = function(val){
        $scope.ShowPasswordForm = !(val);
    }

    $scope.MailPassword = function(email){
        $http.post('./php/GetPassword.php', { "sUserID":email}).
            success(function(data, status) {
                $scope.status = status;
                if(data>"0"){
                    alert('password sent!');
                }else{
                    alert('Login not found!');
                }

            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
                $scope.ErrorMessage = "Invalid Login, please try again";
            });
    }

    $scope.Login = function(){

 
        $http.post($scope.url, { "sUserID":$scope.LogInUserID,"sPassword":$scope.LogInUserPWD }).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                if(data>"0"){
                    $scope.$root.FillUserData(data);
                    $cookies.LoggedInUserID = data;
                    $scope.ErrorMessage = "Welcome";
                }else{
                    $scope.ErrorMessage = "Invalid Login, please try again";
                }

            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
                $scope.ErrorMessage = "Invalid Login, please try again";
            });
    }
}
function SurveyAdminCtrl($scope,$http){

    $scope.$root.CheckLogin(2);
    $scope.url = './php/SurveyController.php';
    $scope.companyList = null;

    $scope.GetSurveys = function(){
        var Criteria = "companyID";
        if($scope.$root.LoggedInUser.iRole==1){Criteria="allSurveys"}
        $http.post($scope.url, { "criteria":Criteria,"iRole":$scope.$root.LoggedInUser.iRole,"idCompanyID":$scope.$root.LoggedInUser.idCompanyID}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;

                $scope.surveys = data;

            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $scope.GetSurveyDetails = function(surveyID){
        window.location.href = '#/survey/'+surveyID;
    }

    $scope.ShowCompanyList = function(){
        alert($scope.$root.companyList[1].sCompanyName);
    }

    $scope.CreateSurvey = function(){
        if($scope.NewSurveyCompany >0){

            $http.post($scope.url, { "criteria":"NewSurvey","data" : $scope.NewSurveyCompany}).
                success(function(data, status) {
                    $scope.status = status;
                    if(data > 0){

                        window.location.href = '#/survey/'+data;
                    }else{

                        $scope.$root.ErrorMessage = "Could not add new survey";
                    }
                })
                .
                error(function(data, status) {
                    $scope.data = data || "Request failed";
                    $scope.status = status;
                });
        }else{
            $scope.$root.ErrorMessage = "Please select a company to create a survey for.";
        }
    }

    $scope.GetSurveys();
    $scope.$root.FillCompanyFilterList();
};

function SurveyEditCtrl($scope,$routeParams,$http){
    $scope.$root.CheckLogin(1);

    $scope.surveyID = $routeParams.surveyID;
    $scope.url = './php/SurveyController.php';


    $scope.activeQuestion = 0;
    $scope.ActiveTriggerQuestion = null;
    $scope.ActiveLanguage = "ENGLISH";

    $scope.divCopySurvey = false
//BOOKMARK

    $scope.GetSurveysToCopy = function(){
        var Criteria = "companyID";
        if($scope.$root.LoggedInUser.iRole==1){Criteria="allSurveys"}
        $http.post($scope.url, { "criteria":Criteria,"iRole":$scope.$root.LoggedInUser.iRole,"idCompanyID":$scope.$root.LoggedInUser.idCompanyID}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;

                data.pop();
                $scope.copysurveys = data;
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $scope.ShowCopySurvey = function(){

        if($scope.divCopySurvey==true){
            $scope.divCopySurvey = false;
        }else{
            $scope.divCopySurvey = true;
            $scope.GetSurveysToCopy();
        }
    }

	$scope.CopyQuestions = function(copySurveyID){
		console.log(copySurveyID);
	}

    $scope.copySurvey = function($copySurveyID){
        var companyid = $scope.survey.iCompany;
        $http.post($scope.url, { "criteria":"CopySurvey","data" : $copySurveyID,"newID": $scope.surveyID,"companyID":companyid,"language":"ALL"}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;

                $scope.survey = data;
                $scope.apply();
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

    $http.post($scope.url, { "criteria":"surveyID","data" : $scope.surveyID,"language":"ALL"}).
        success(function(data, status) {
            $scope.status = status;
            $scope.data = data;
            $scope.survey = data;
        })
        .
        error(function(data, status) {
            $scope.data = data || "Request failed";
            $scope.status = status;
        });

    $scope.SetActiveQuestion = function(inputQuestion){
        $scope.activeQuestion = inputQuestion;

    }

    $scope.GetActiveQuestionAnswers = function(itemToFilter){
        if(itemToFilter.idQuestionID = $scope.activeQuestionID){
            return true;
        }
        return false;
    }

    $scope.updateTriggerQuestion = function(newTriggerQuestion){

        $scope.ActiveTriggerQuestion = newTriggerQuestion;
    }

    $scope.SaveSurvey = function(surveyObject){
        $http.post("./php/SurveyChangesController.php", { "data" : surveyObject}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                $scope.survey = null;
                $scope.survey = data;
                $scope.$root.ErrorMessage = "Survey Saved!";
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
                $scope.$root.ErrorMessage = "Could not save Survey";
            });
    }
    $scope.AddNewQuestion = function(){
        $http.post("./php/SurveyController.php", { "criteria":"BlankQuestion","data" : $scope.surveyID,"language":"ALL"}).
            success(function(data, status) {
                $scope.status = status;

                var BlankQuestion = data;
                BlankQuestion.iQuestionOrder = $scope.survey.qcQuestions.length + 1;
                $scope.survey.qcQuestions.push(BlankQuestion);
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }
    $scope.AddNewAnswer = function(question){
        if(question.sQuestionType!="Textbox"){
            $http.post("./php/SurveyController.php", { "criteria":"BlankAnswer","data" : question.idQuestionID,"surveyID":question.idSurvey,"language":"ALL"}).
                success(function(data, status) {
                    $scope.status = status;
                    question.aAnswers.push(data);
                })
                .
                error(function(data, status) {
                    $scope.data = data || "Request failed";
                    $scope.status = status;
                });
        }else{
            $scope.$root.ErrorMessage = "Can't add answer to this type. Switch to Question Type to add multiple answers."
        }
    }
    $scope.DeleteQuestion = function(question){
        //check if it's been saved in the DB yet
        if(question.idQuestionID >0){
            //delete from db
            for(var i=0;i<$scope.survey.qcQuestions.length;i++){
                if($scope.survey.qcQuestions[i].idQuestionID == question.idQuestionID){
                    
                    var questionIndex=i;
                    $http.post("./php/SurveyController.php", { "criteria":"DeleteQuestion","data" : question.idQuestionID}).
                        success(function(data, status) {
                            $scope.status = status;
                            if(data="SUCCESS"){
                                

                                $scope.survey.qcQuestions.splice(questionIndex,1);
                            }else{
                                $scope.$root.ErrorMessage = "Could Not Delete Question";
                               
                            }
                        })
                        .
                        error(function(data, status) {
                            $scope.data = data || "Request failed";
                            $scope.status = status;
                            $scope.$root.ErrorMessage = "Could Not Delete Question";
                       });
                }
            }
        }else
        {
            //it hasnt been saved yet, just remove it from view
            for(var i=0;i<$scope.survey.qcQuestions.length;i++){
                if($scope.survey.qcQuestions[i].idQuestionID == question.idQuestionID && $scope.survey.qcQuestions[i].sText == question.sText){
                    $scope.survey.qcQuestions.splice(i,1);
                }
            }
        }

    }
    $scope.DeleteAnswer = function(answer,question){
        //check if it's been saved in the DB yet
        if(answer.idAnswerID>0){
            //delete it from db
            for(var i=0;i<question.aAnswers.length;i++){
               
                if(question.aAnswers[i].idAnswerID ==answer.idAnswerID && question.aAnswers[i].sAnswerText==answer.sAnswerText){
                    var AnswerIndex= i; //save it to a different variable in case the POST doesn't come back before i increments
                    $http.post("./php/SurveyController.php", { "criteria":"DeleteAnswer","data" : answer.idAnswerID}).
                        success(function(data, status) {
                            $scope.status = status;
                            if(data="SUCCESS"){
                                question.aAnswers.splice(AnswerIndex,1);
                            }else{
                                $scope.$root.ErrorMessage = "Could Not Delete Answer";
                                                            }
                        })
                        .
                        error(function(data, status) {
                            $scope.data = data || "Request failed";
                            $scope.status = status;
                            $scope.$root.ErrorMessage = "Could Not Delete Answer";
                                                    });
                    return;
                }
            }
        }else{
            //just remove from the array
            for(var i=0;i<question.aAnswers.length;i++){
                
                if(question.aAnswers[i].idAnswerID ==answer.idAnswerID && question.aAnswers[i].sAnswerText==answer.sAnswerText){
                    question.aAnswers.splice(i,1);
                    return;
                }
            }
        }
    }
    $scope.CheckTypes = function(){
        alert($scope.QuestionTypes);
    }
    $scope.QuestionOrderUp = function(question)
    {

        for(var i=0;i<$scope.survey.qcQuestions.length;i++){
            if($scope.survey.qcQuestions[i].iQuestionOrder == question.iQuestionOrder-1){
                $scope.survey.qcQuestions[i].iQuestionOrder = question.iQuestionOrder;
                question.iQuestionOrder--;
            }
        }
        $http.post("./php/SurveyChangesController.php", { "data" : $scope.survey}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                $scope.survey = null;
                $scope.survey = data;
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }
    $scope.QuestionOrderDown = function(question)
    {
                for(var i=0;i<$scope.survey.qcQuestions.length;i++){
            if($scope.survey.qcQuestions[i].iQuestionOrder == parseFloat(question.iQuestionOrder)+1){
                $scope.survey.qcQuestions[i].iQuestionOrder = question.iQuestionOrder;
                question.iQuestionOrder++;

                break;
            }
        }
        $http.post("./php/SurveyChangesController.php", { "data" : $scope.survey}).
            success(function(data, status) {
                $scope.status = status;
                $scope.data = data;
                $scope.survey = null;
                $scope.survey = data;
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }
    $scope.QuestionReorder = function(){
        for(var i=0;i<$scope.survey.qcQuestions.length;i++){
            $scope.survey.qcQuestions[i].iQuestionOrder = i+1;
        }
        $scope.$root.ErrorMessage = "Order cleaned up!";
    }
    $scope.DeleteSurvey = function(survey){
        $http.post("./php/SurveyController.php", { "criteria":"DeleteSurvey","data" : survey.idSurveyID}).
            success(function(data, status) {
                $scope.status = status;
                if(data=="1"){
                    $scope.$root.ErrorMessage = "Survey Deleted";
                    window.location.href = '#/admin-surveys';
                }else{
                    $scope.$root.ErrorMessage =  "Could not delete survey";
                }
            })
            .
            error(function(data, status) {
                $scope.data = data || "Request failed";
                $scope.status = status;
            });
    }

}
