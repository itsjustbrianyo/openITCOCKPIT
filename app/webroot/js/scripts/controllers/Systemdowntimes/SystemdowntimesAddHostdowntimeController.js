angular.module('openITCOCKPIT')
    .controller('SystemdowntimesAddHostdowntimeController', function($scope, $state, $http, QueryStringService, $stateParams, NotyService){

        $scope.init = true;
        $scope.errors = null;

        $scope.data = {
            hostIds: []
        };

        if($stateParams.id !== null){
            $scope.data.hostIds.push($stateParams.id);
        }

        $scope.Downtime = {
            is_recurring: false
        };


        $scope.post = {
            params: {
                'angular': true
            },
            Systemdowntime: {
                is_recurring: 0,
                weekdays: {},
                day_of_month: null,
                from_date: null,
                from_time: null,
                to_date: null,
                to_time: null,
                duration: null,
                downtimetype: 'host',
                downtimetype_id: 0,
                objecttype_id: 256,     //1 << 8
                object_id: null,
                comment: null
            }
        };

        $scope.loadRefillData = function(){
            $http.get("/angular/downtime_host.json", {
                params: {
                    'angular': true
                }
            }).then(function(result){
                $scope.post.Systemdowntime.downtimetype_id = result.data.preselectedDowntimetype;
            });
            $http.get("/angular/getDowntimeData.json", {
                params: {
                    'angular': true
                }
            }).then(function(result){
                $scope.post.Systemdowntime.from_date = result.data.refill.from_date;
                $scope.post.Systemdowntime.from_time = result.data.refill.from_time;
                $scope.post.Systemdowntime.to_date = result.data.refill.to_date;
                $scope.post.Systemdowntime.to_time = result.data.refill.to_time;
                $scope.post.Systemdowntime.comment = result.data.refill.comment;
                $scope.post.Systemdowntime.duration = result.data.refill.duration;
                $scope.errors = null;
            }, function errorCallback(result){
                console.error(result);
                if(result.data.hasOwnProperty('error')){
                    $scope.errors = result.data.error;
                }
            });
        };

        $scope.loadRefillData();

        $scope.saveNewHostDowntime = function(){
            $scope.post.Systemdowntime.object_id = $scope.data.hostIds;
            if($scope.post.Systemdowntime.is_recurring){
                $scope.post.Systemdowntime.to_time = null;
                $scope.post.Systemdowntime.to_date = null;
                $scope.post.Systemdowntime.from_date = null;
            }
            console.log($scope.post);
            $http.post("/systemdowntimes/addHostdowntime.json?angular=true", $scope.post).then(
                function(result){
                    $scope.errors = null;
                    NotyService.genericSuccess();
                    if($scope.post.Systemdowntime.is_recurring){
                        $state.go('SystemdowntimesHost');
                    }else{
                        $state.go('DowntimesHost');
                    }
                },
                function errorCallback(result){
                    console.error(result.data);
                    if(result.data.hasOwnProperty('error')){
                        $scope.errors = result.data.error[0];
                        NotyService.genericError();
                    }
                }
            );
        };

        $scope.loadHosts = function(searchString){
            $http.get("/hosts/loadHostsByString/1.json", {
                params: {
                    'angular': true,
                    'filter[Hosts.name]': searchString,
                    'selected[]': $scope.data.hostIds
                }
            }).then(function(result){
                $scope.hosts = result.data.hosts;
            });
        };

        $scope.loadHosts('');

        $scope.$watch('Downtime.is_recurring', function(){
            if($scope.Downtime.is_recurring === true){
                $scope.post.Systemdowntime.is_recurring = 1;
                if($scope.errors && $scope.errors['from_time']){
                    delete $scope.errors['from_time'];
                }
            }
            if($scope.Downtime.is_recurring === false){
                $scope.post.Systemdowntime.is_recurring = 0;
            }
        });
    });