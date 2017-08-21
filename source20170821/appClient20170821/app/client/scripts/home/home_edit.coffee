'use strict'

angular.module('app.home.edit', [])

.controller('homeEditCtrl', 
    ($rootScope, $scope, $api, $modalInstance, filterFilter, homeStorage, 
        logger, $session, $dialogs, $timeout, home, $location, $chat) ->
        # Close dialog
        $scope.cancel = ->
            $modalInstance.dismiss('cancel')

        # Initialize
        $scope.init = () ->
            $scope.editSummaryMode = false
            $scope.editHomeNameMode = false
            home.org_home_name = home.home_name
            home.org_summary = home.summary
            $scope.home = home

        $scope.init()

        # Edit home name
        $scope.changeHomeName = (home) ->
            home.home_name = home.home_name.trim()
            if home.home_name == home.org_home_name
                $scope.editHomeNameMode = false
                return
            if home.home_name == ""
                home.home_name = home.org_home_name
            else
                params = 
                    home_id: home.home_id
                    home_name: home.home_name

                homeStorage.edit(params, (data) ->
                    if data.err_code != 0
                        logger.logError(data.err_msg)
                    else
                        home.org_home_name = home.home_name
                        $rootScope.cur_home.home_name = home.home_name
                        $rootScope.$broadcast('refresh-home', home)
                )

            $scope.editHomeNameMode = false

        $scope.editHomeName = (home) ->
            if $rootScope.canEditHome()
                $scope.editHomeNameMode = true

        # Edit summary
        $scope.editSummary = () ->
            if $rootScope.canEditHome()
                $scope.editSummaryMode = true
            return

        $scope.exitEditSummary = () ->
            $scope.home.summary = $scope.home.org_summary
            $scope.editSummaryMode = false
            return

        $scope.submitSaveSummary = (home) ->
            params =
                home_id: home.home_id
                summary: home.summary
            homeStorage.edit(params)
            $scope.home.org_summary = $scope.home.summary
            $scope.editSummaryMode = false
            return

        # Upload logo
        $scope.onUploadLogo = (files) ->
            file = files[0]
            homeStorage.upload_logo($scope.home.home_id, file).progress( (evt) ->
                #console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total))
            ).success( (data, status, headers, config) ->
                if data.err_code == 0
                    $scope.home.logo_url = data.logo_url
                    home = homeStorage.get_home($scope.home.home_id)
                    home.logo_url = $scope.home.logo_url
                    $chat.home('refresh-logo', $scope.home.home_id)
                    $rootScope.$broadcast('refresh-home', $scope.home)
                else
                    logger.logError(data.err_msg)
            )

        $scope.onRemoveLogo = () ->
            homeStorage.remove_logo($scope.home.home_id, (res) ->
                if res.err_code == 0
                    $scope.home.logo_url = res.logo_url
                    home = homeStorage.get_home($scope.home.home_id)
                    home.logo_url = $scope.home.logo_url
                    $chat.home('refresh-logo', $scope.home.home_id)
                    $rootScope.$broadcast('refresh-home', $scope.home)
                else
                    logger.logError(res.err_msg)
            )
            return

        return
)