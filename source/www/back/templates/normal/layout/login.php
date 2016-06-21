<!DOCTYPE html>
<html lang="ja">
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

    <link href="css/backend-login.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">

      <form id="form" class="form-signin form-horizontal" role="form" method="post">
        <h2 class="form-signin-heading"><span class="logo"><?php p(PRODUCT_NAME); ?></span>管理ページ</h2>
		<div class="row">
			<fieldset>
				<div class="control-group">
					<label for="email" class="control-label"><i class="fa fa-envelope"></i></label>
					<div class="controls">
						<input type="text" class="form-control input-xlarge" id="email" name="email" placeholder="メールアドレスを入力してください。" autofocus>
					</div>
				</div>
			</fieldset> <!-- /fieldset -->
			<fieldset>
				<div class="control-group">
					<label for="password" class="control-label"><i class="fa fa-key"></i></label>				
					<div class="controls">
						<input type="password" class="form-control input-xlarge" id="password" name="password" placeholder="パスワードを入力してください。">
					</div>
				</div>
			</fieldset> <!-- /fieldset -->
			<fieldset>
				<div class="control-group">
					<label for="password" class="control-label"></label>				
					<div class="controls">
						<label><input type="checkbox" name="auto_login" value="1">　ログイン状態を保持します。</label>
					</div>
				</div>
			</fieldset> <!-- /fieldset -->
			<fieldset>
				<div class="control-group">
					<div class="controls control-action">
						<button class="btn btn-large btn-primary" type="submit"><i class="fa fa-sign-in"></i> ログイン</button>
					</div>
				</div>
			</fieldset> <!-- /fieldset -->
		</div>
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

	<script src="js/utility.js?<?php p(VERSION); ?>"></script>

	<script type="text/javascript">
		$(function () {

			var $form = $('#form').validate($.extend({
				rules : {
					email: {
						required: true
					},
					password: {
						required: true
					}
				},

				// Messages for form validation
				messages : {
					email : {
						required : '<?php l("メールアドレスを入力してください。"); ?>'
					},
					password : {
						required : '<?php l("パスワードを入力してください。"); ?>'
					}
				}
			}, getValidationRules()));

			$('.btn-submit').click(function() {
				$('#login_error').hide();
			});
		});
	</script>
  </body>
</html>