<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->
    <title><?php p(PRODUCT_NAME); ?></title>

    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">

	<base href="<?php p(SITE_BASEURL);?>">

	<!-- Basic Styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">

	<link href="css/jquery-ui-1.10.3.css" rel="stylesheet">

    <link href="css/backend-common.css" rel="stylesheet">
    
	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="ico/favicon.png">
	<link rel="icon" href="ico/favicon.ico" type="image/x-icon">

  </head>
  <body class="popup">

	<?php $this->include_view(); ?>	

	<?php $this->include_css(); ?>

	<script src="js/jquery-1.6.min.js"></script>
	<script src="js/jquery-ui-1.10.3.min.js"></script>
	<script src="js/jquery-form/jquery-form.min.js"></script>
	<script src="js/jquery-validate/jquery.validate.min.js"></script>
	<script src="js/jquery-validate/additional-methods.js"></script>
	<script src="js/masked-input/jquery.maskedinput.min.js"></script>
	<script src="js/notification/SmartNotification.js"></script>

	<!-- jquery autocomplete -->
	<script src='js/autocomplete/lib/jquery.ajaxQueue.js'></script>
	<script src='js/autocomplete/lib/thickbox-compressed.js'></script>
	<script src='js/autocomplete/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="js/autocomplete/jquery.autocomplete.css" />
	<link rel="stylesheet" type="text/css" href="js/autocomplete/lib/thickbox.css" />

	<!-- jquery fancybox -->
	<script type="text/javascript" src="js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
	<script type="text/javascript" src="js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="js/fancybox/jquery.fancybox-1.3.4.css" media="screen" />

	<?php $this->include_js(); ?>

	<script src="js/utility.js?<?php p(VERSION); ?>"></script>

	<?php $this->include_viewjs(); ?>
  </body>
</html>
