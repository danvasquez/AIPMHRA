/**
 * Created with JetBrains PhpStorm.
 * User: danvasquez
 * Date: 2/10/13
 * Time: 9:18 PM
 * To change this template use File | Settings | File Templates.
 */
var app = angular.module('healthsurvey',['ngCookies','ngSanitize','charts.pie','SharedServices']);
app.config(['$routeProvider', function($routeProvider) {
    $routeProvider.
        when('/admin-surveys', {templateUrl: 'partials/admin-surveys.html',   controller: SurveyAdminCtrl}).
        when('/survey/:surveyID', {templateUrl: 'partials/survey-detail.html', controller: SurveyEditCtrl}).
        when('/user/:userID', {templateUrl: 'partials/user-detail.html', controller: UserEditCtrl}).
        when('/company/:companyID', {templateUrl: 'partials/company-detail.html', controller: CompanyEditCtrl}).
        when('/admin-users', {templateUrl: 'partials/admin-users.html', controller: UserAdminCtrl}).
        when('/user-surveys', {templateUrl: 'partials/user-surveys.html', controller: UserSurveyCtrl}).
        when('/admin-companies', {templateUrl: 'partials/admin-companies.html', controller: CompanyAdminCtrl}).
        when('/login', {templateUrl: 'partials/login.html', controller: LoginCtrl}).
        when('/home', {templateUrl: 'partials/home.html', controller: HomeCtrl}).
        when('/enrollment-file', {templateUrl: 'partials/enrollment-file.html', controller: EnrollmentCtrl}).
        when('/register', {templateUrl: 'partials/register.html', controller: RegisterCtrl}).
        when('/SurveyLanding/:surveyID', {templateUrl: 'partials/SurveyLanding.html', controller: SurveyLandingCtrl}).
        when('/TakeSurvey/:surveyID', {templateUrl: 'partials/TakeSurvey.html', controller: TakeSurveyCtrl}).
        when('/ViewResults/:surveyID', {templateUrl: 'partials/ViewResults.html', controller: UserResultsCtrl}).
        when('/AdminViewResults/:surveyID', {templateUrl: 'partials/AdminViewResults.html', controller: AdminResultsCtrl}).
        otherwise({redirectTo: '/login', controller:LoginCtrl});
    }]);

app.run(function($rootScope,$http,$cookies,$location) {
    $rootScope.SurveyLangage="ENGLISH";
    $rootScope.QuestionTypes = [{"value":"Textbox","text":"TextBox"},{"value":"option","text":"Option"},{"value":"checkbox","text":"Multiple Choice"}];
    

    $rootScope.ErrorMessage = "";
    $rootScope.companyList = "";
    $rootScope.companyFilter="";

    $rootScope.FillCompanyFilterList = function(){
        $http.post('./php/CompanyController.php',{"criteria":"GetCompanies","iRole":$rootScope.LoggedInUser.iRole,"idCompanyID":$rootScope.LoggedInUser.idCompanyID}).
            success(function(data,status){
                
                $rootScope.companyList = data;
            })
            .
            error(function(data,status){
                
            })
    }

    $rootScope.logout = function(){
        $rootScope.LoggedInUser = null;
        delete $cookies['LoggedInUserID'];
        window.location.href="#/login";
    }

    $rootScope.CheckLogin = function(req){

	    $rootScope.ErrorMessage = "";
        
        if($cookies.LoggedInUserID<1){
            window.location.href="#/login";
        }

        if($rootScope.LoggedInUser==null){
            $rootScope.FillUserData();

        }else{

            if($rootScope.LoggedInUser.iRole==null){
                
                window.location.href="#/login";
            }else if($rootScope.LoggedInUser.iRole>req){
                $rootScope.ErrorMessage = "You are not authorized for that action";
                window.location.href="#/home";
            }
        }

    }

    $rootScope.FillUserData = function(uid){

        if(uid == null){
            uid= $cookies.LoggedInUserID;
        }

        if(uid!=null){

        

            $http.post('./php/UserController.php', { "criteria":"GetSingleUser","data":uid}).
                success(function(data, status) {
                    
                    $rootScope.LoggedInUser = data;
                    $location.path('home');
                })
                .
                error(function(data, status) {
                    $scope.$root.ErrorMessage = "Could not get user";

                });


        }
    }

    //$rootScope.FillUserData();
});

app.directive('passwordValidate', function() {
    return {
        require: 'ngModel',
        link: function(scope, elm, attrs, ctrl) {
            ctrl.$parsers.unshift(function(viewValue) {

                scope.pwdValidLength = (viewValue && viewValue.length >= 8 ? 'valid' : undefined);
                scope.pwdHasLetter = (viewValue && /[A-z]/.test(viewValue)) ? 'valid' : undefined;
                scope.pwdHasNumber = (viewValue && /\d/.test(viewValue)) ? 'valid' : undefined;

                if(scope.pwdValidLength && scope.pwdHasLetter && scope.pwdHasNumber) {
                    ctrl.$setValidity('pwd', true);
                    return viewValue;
                } else {
                    ctrl.$setValidity('pwd', false);
                    return undefined;
                }

            });
        }
    };
});


angular.module('SharedServices', [])
    .config(function ($httpProvider) {
        $httpProvider.responseInterceptors.push('myHttpInterceptor');
        var spinnerFunction = function (data, headersGetter) {
            // todo start the spinner here
            $('#loading').show();
            return data;
        };
        $httpProvider.defaults.transformRequest.push(spinnerFunction);
    })
// register the interceptor as a service, intercepts ALL angular ajax http calls
    .factory('myHttpInterceptor', function ($q, $window) {
        return function (promise) {
            return promise.then(function (response) {
                // do something on success
                // todo hide the spinner
                $('#loading').hide();
                return response;

            }, function (response) {
                // do something on error
                // todo hide the spinner
                $('#loading').hide();
                return $q.reject(response);
            });
        };
    })

angular.module('charts.pie', [
])
    .directive('qnPiechart', [
    function() {
        return {
            require: '?ngModel',
            link: function(scope, element, attr, controller) {
                var settings = {
                    is3D: true

                };

                var getOptions = function() {
                    return angular.extend({ }, settings, scope.$eval(attr.qnPiechart));
                };

                // creates instance of datatable and adds columns from settings
                var getDataTable = function() {
                    var columns = scope.$eval(attr.qnColumns);
                    var data = new google.visualization.DataTable();
                    angular.forEach(columns, function(column) {

                        data.addColumn(column.type, column.name);
                    });
                    return data;
                };

                var init = function() {
                    var options = getOptions();
                    if (controller) {

                        var drawChart = function() {
                            var data = getDataTable();
                            // set model
                            

                            var o = new Array();

                            for(var x=0;x<controller.$viewValue.length;x++){
                                var row = controller.$viewValue[x];
                                var val = row[1];
                                row[1] = parseInt(val);
                           
                                o.push(row);
                            }

                          
                            data.addRows(o);

                            // Instantiate and draw our chart, passing in some options.
                            var pie = new google.visualization.BarChart(element[0]);
                            pie.draw(data, options);
                        };

                        controller.$render = function() {
                            drawChart();
                        };
                    }

                    if (controller) {
                        // Force a render to override
                        controller.$render();
                    }
                };

                // Watch for changes to the directives options
                scope.$watch(getOptions, init, true);
                scope.$watch(getDataTable, init, true);
            }
        };
    }
]);
