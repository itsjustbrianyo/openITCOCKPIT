angular.module('openITCOCKPIT').directive('acknowledgeHost', function($http, SudoService, $timeout){
    return {
        restrict: 'E',
        templateUrl: '/angular/acknowledge_host.html',

        controller: function($scope){

            $scope.doHostAck = false;
            $scope.hostAckType = 'hostOnly';
            $scope.ack = {
                comment: '',
                sticky: false,
                error: false
            };

            var objects = {};
            var author = '';

            $scope.setObjects = function(_objects){
                objects = _objects;
            };

            $scope.setAuthor = function(_author){
                author = _author;
            };

            $scope.doAcknowledgeHost = function(){
                $scope.ack.error = false;
                if($scope.ack.comment === ''){
                    $scope.ack.error = true;
                    return false;
                }

                var sticky = 0;
                if($scope.ack.sticky === true){
                    sticky = 2;
                }

                var count = Object.keys(objects).length;
                var i = 0;
                $scope.percentage = 0;
                $scope.doHostAck = true;

                $scope.percentage = Math.round(i / count * 100);
                for(var id in objects){
                    var object = objects[id];
                    i++;
                    $scope.percentage = Math.round(i / count * 100);
                    SudoService.send(SudoService.toJson('submitHoststateAck', [
                        object.Host.uuid,
                        $scope.ack.comment,
                        author,
                        sticky,
                        $scope.hostAckType
                    ]));
                }
                $timeout(function(){
                    $scope.doHostAck = false;
                    $scope.percentage = 0;
                    $('#angularacknowledgeHostModal').modal('hide');
                }, 500);
            };

        },

        link: function($scope, element, attr){
            $scope.acknowledgeHost = function(objects){
                if(Object.keys(objects).length === 0){
                    return;
                }
                $scope.setObjects(objects);

                $scope.setAuthor(attr.author);

                $('#angularacknowledgeHostModal').modal('show');
            };
        }
    };
});