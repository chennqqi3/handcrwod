angular.module('app.chatroom.emoticon_add', [])

.controller('emoticonAddCtrl', 
    function($scope, $rootScope, $state, $stateParams, $api, missionStorage, logger, CONFIG, $ionicHistory) {
		$scope.onUpload = function(files, type) {
		    var file;
		    if (files.length === 0) {
		        return;
		    }
		    file = files[0];
		    return missionStorage.upload_emoticon($scope.cur_mission.mission_id, file).progress(function(evt) {}).success(function(data, status, headers, config) {
		        if (data.err_code === 0) {
		            $scope.emoticon.image = CONFIG.BASE + data.image;
		        } else {
		            logger.logError(data.err_msg);
		        }
		    });
		};

		$scope.add = function() {
		    return missionStorage.add_emoticon($scope.emoticon, function(res) {
		        if (res.err_code === 0) {
		            $api.init_emoticon(res.emoticon);
		            $rootScope.emoticons.push(res.emoticon);
		            
  					$ionicHistory.goBack();    
		        } else {
		            logger.logError(res.err_msg);
		        }
		    });
		};

		$scope.init = function() {
		    $scope.emoticon = {
		        mission_id: $rootScope.cur_mission.mission_id,
		        alt: '',
		        title: '',
		        image: ''
		    };
		};

		$scope.init();
});    	