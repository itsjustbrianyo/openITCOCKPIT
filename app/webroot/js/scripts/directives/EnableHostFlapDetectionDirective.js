angular.module('openITCOCKPIT').directive('enableHostFlapDetection', function($http, SudoService, $timeout){
    return {
        restrict: 'E',
        templateUrl: '/angular/enable_host_flap_detection.html',

        controller: function($scope){

            var objects = {};
            $scope.isEnableingHostFlapDetection = false;

            $scope.setEnableHostFlapDetectionObjects = function(_objects){
                objects = _objects;
            };

            $scope.doEnableHostFlapDetection = function(){
                var count = Object.keys(objects).length;
                var i = 0;
                $scope.percentage = 0;
                $scope.isEnableingHostFlapDetection = true;


                $scope.percentage = Math.round(i / count * 100);
                for(var id in objects){
                    var object = objects[id];
                    i++;
                    $scope.percentage = Math.round(i / count * 100);
                    SudoService.send(SudoService.toJson('enableOrDisableHostFlapdetection', [
                        object.Host.uuid,
                        1 //Enable flap detection
                    ]));
                }
                $timeout(function(){
                    $scope.isEnableingHostFlapDetection = false;
                    $scope.percentage = 0;
                    $('#angularEnableHostFalpDetectionModal').modal('hide');
                }, 500);
            };

        },

        link: function($scope, element, attr){
            $scope.enableHostFlapDetection = function(objects){
                if(Object.keys(objects).length === 0){
                    return;
                }
                $('#angularEnableHostFalpDetectionModal').modal('show');
                $scope.setEnableHostFlapDetectionObjects(objects);
            };
        }
    };
});