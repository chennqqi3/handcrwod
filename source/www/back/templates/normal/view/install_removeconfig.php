<?php if (!$this->script_mode()) { ?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

	<base href="<?php p(SITE_BASEURL);?>">

    <link rel="shortcut icon" href="favicon.png">
	<link rel="icon" href="favicon.ico" type="image/x-icon">

    <title><?php p(PRODUCT_NAME); ?></title>

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">

	<link href="css/jquery-ui-1.10.3.css" rel="stylesheet">

    <link href="css/common.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" media="screen" href="css/install.css">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->

  </head>

  <body>
	<div class="container">
		<div class="masthead">
			<h3 class="logo"><?php p(PRODUCT_NAME); ?></h3>
			<h1><?php l("ハンドクラウドインストールページ");?>
				<p><?php l("このページはハンドクラウドシステムのインストールを案内します。");?></p></h1>
		</div>

		<div class="main">
			<div class="important"><?php l("システムを再インストールするには、config.incファイルを削除し、以下の確認ボタンを押下してください。"); ?><br/> <?php l("インストール後、データベースのすべてのデータが削除されるので、インストール前にデータをバックアップしてください。"); ?></div>

			<div class="text-right">
				<a href="home" class="btn"><i class="fa fa-times"></i> <?php l("戻る"); ?></a>
				<a href="install" class="btn btn-primary"><i class="fa fa-check"></i> <?php l("確認"); ?></a>
			</div>
		</div>
    </div> <!-- /container -->
  </body>
</html>
<?php } else { } ?>
