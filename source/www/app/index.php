<!doctype html>
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>ハンドクラウド</title>
        <meta name="description" content="ハンドクラウド(HandCrowd）は、仕事を分解しフローにすることで個人やチームの業務改善をサポートするクラウド型ToDoリスト">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
		<link rel="shortcut icon" href="favicon.png">
		<link rel="icon" href="favicon.ico" type="image/x-icon">
        <!-- needs images, font... therefore can not be part of ui.css -->
        <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
        <!-- end needs images -->

        <link rel="stylesheet" href="styles/main.css?v=2.8.4">
        <link rel="apple-touch-icon" href="images/webapp.png">
        <link rel="apple-touch-icon-precompsed" href="images/webapp.png">
        <link rel="apple-touch-startup-image" href="images/webapp.png">

        <meta name="thumbnail" content="https://www.handcrowd.com/app/images/logo_1024.png">
        <meta property="og:title" content="ハンドクラウド">
        <meta property="og:image" content="https://www.handcrowd.com/app/images/logo_1024.png">
        <meta property="og:description" content="ハンドクラウド(HandCrowd）は、仕事を分解しフローにすることで個人やチームの業務改善をサポートするクラウド型ToDoリスト">
        <meta property="og:url" content="https://www.handcrowd.com/app">

    </head>
    <body data-ng-app="app" id="app" data-custom-background>
        <!--[if lt IE 9]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <div data-ng-controller="AppCtrl">
            <aside data-ng-include=" 'views/nav_home.html' + ver " id="nav-home-container" data-ng-hide="isSpecificPage()" data-ng-cloak class="no-print"></aside>
            <aside data-ng-include=" 'views/nav.html' + ver " id="nav-container" data-ng-hide="isSpecificPage()" data-ng-cloak class="no-print"></aside>

            <div class="view-container">
                <section data-ng-view id="content"></section>
            </div>
        </div>

        <div class="good_job"><img src="images/good_job.png"></div>
        <div class="mission_complete"><img src="images/mission_complete.png"></div>

        <div class="error-bar" ng-cloak>
            <div class="error-connection" ng-class="{'show': error_disconnected}"><i class="fa fa-warning"></i> サーバーに接続できません。</div>
        </div>

        <script src="scripts/vendor.js?v=2.8.4"></script>

        <script src="scripts/ui.js?v=2.8.4"></script>

        <script src="scripts/config.js?v=2.8.4"></script>

        <script src="scripts/app.js?v=2.8.4"></script>
    </body>
</html>