<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

	<base href="<?php p(SITE_BASEURL);?>">

    <link rel="shortcut icon" href="ico/favicon.png">
	<link rel="icon" href="ico/favicon.ico" type="image/x-icon">

    <title><?php p(PRODUCT_NAME); ?></title>

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">

	<link href="css/jquery-ui-1.10.3.css" rel="stylesheet">

    <link href="css/common.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
	<div class="container error-page">
		<div class="row">
			<div class="span2 text-center">
				<i class="fa fa-exclamation-triangle error-mark"></i>
			</div>
			<div class="span10 txt-color-white">
				<h1><?php p($this->err_title); ?></h1>
				<p><?php p($this->err_msg); ?></p>
			</div>
		</div>
	</div>
  </body>
</html>
