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
		<form id="form" action="patch/patch_ajax" class="form-signin form-horizontal" method="post">
			<div class="masthead">
				<h3 class="logo"><?php p(PRODUCT_NAME); ?></h3>
				<h1><?php l("ハンドクラウドパッチページ");?>
					<p><?php l("このページはハンドクラウドシステムのパッチを案内します。");?></p></h1>
			</div>
			<div class="main">
				<div class="row">
					<div class="span12">
						<table class="table table-striped">
							<tr>
								<th width="90px">バージョン</th>
								<th>説明</th>
							</tr>
						<?php 
							foreach($this->must_patches as $p) {
						?>
							<tr>
								<td><?php p($p["version"]); ?></td>
								<td><?php p(_str2html($p["description"])); ?></td>
							</tr>
						<?php 
							}
						?>
						</table>
						<div class="text-right">
							<button type="button" class="btn btn-primary" id="btnStart"><i class="fa fa-check"></i> 開始</button>
						</div>
					</div>
				</div>
			</div>
		    <div class="clr"></div>
		</form>

    </div> <!-- /container -->

	<script src="js/jquery-1.7.2.min.js"></script>
    <script src="js/bootstrap.js"></script>
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

	<script src="js/utility.js"></script>

	<script type="text/javascript">
		disable_alarm = true;

		$(function () {
			$('#form').ajaxForm({
				dataType : 'json',
				success: function(ret, statusText, xhr, form) {
					try {
						if (ret.err_code == 0)
						{
							alertBox("<?php l('パッチ完了');?>", "<?php l('システムパッチが完了しました。');?>", function() {
								goto_url("home");
							});
						}
						else {
							errorBox("<?php l('パッチ失敗');?>", "<?php l('すみません。パッチが失敗しました。');?>");
						}
					}
					finally {
					}
				}
			});

			$('#btnStart').click(function() {
				var ret = confirm("パッチを開始します。");
				if (ret)
				{
					$('#form').submit();
				}
			});
		});
	</script>
  </body>
</html>
<?php } else { } ?>