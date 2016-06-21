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
		<form id="form" action="install/start_ajax" class="form-signin form-horizontal" method="post">
			<?php $this->oConfig->hidden("step"); ?>
			<div class="masthead">
				<h3 class="logo"><?php p(PRODUCT_NAME); ?></h3>
				<h1><?php l("ハンドクラウドインストールページ");?>
					<p><?php l("このページはハンドクラウドシステムのインストールを案内します。");?></p></h1>
			</div>
			<div class="main">
				<div class="row">
					<div class="span6">
						<h3>1. <?php l("環境チェック");?></h3>
						<fieldset class="control-group">
							<label class="control-label">Apacheバージョン</label>
							<div class="controls text-detail"><?php p(apache_get_version()); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label">MySQL Extension</label>
							<div class="controls text-detail"><?php $this->oConfig->installed("installed_mysql"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label">mbstring Extension</label>
							<div class="controls text-detail"><?php $this->oConfig->installed("installed_mbstring"); ?></div>
						</fieldset> <!-- /fieldset -->
					</div>
					<div class="span6">
						<h3>&nbsp;</h3>
						<fieldset class="control-group">
							<label class="control-label">PHPバージョン</label>
							<div class="controls text-detail"><?php p(phpversion()); $this->oConfig->input("require_php_ver", array("class"=>"input-null")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label">gd Extension</label>
							<div class="controls text-detail"><?php $this->oConfig->installed("installed_gd"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label">SimpleXML Extension</label>
							<div class="controls text-detail"><?php $this->oConfig->installed("installed_simplexml"); ?></div>
						</fieldset> <!-- /fieldset -->
					</div>
				</div>
				<div class="row">
					<div class="span6">
						<h3>2. <?php l("データベース設定");?></h3>
						<fieldset class="control-group">
							<label class="control-label" for="db_hostname">MySQL<?php l("サーバーアドレス");?></label>
							<div class="controls"><?php $this->oConfig->input("db_hostname", array("placeholder" => "例: localhost, 192.168.224.55")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="db_user"><?php l("ユーザーID");?></label>
							<div class="controls"><?php $this->oConfig->input("db_user", array("placeholder" => "データベース作成権限必要")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="db_password"><?php l("パスワード");?></label>
							<div class="controls"><?php $this->oConfig->password("db_password"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="db_name"><?php l("データベース名");?></label>
							<div class="controls"><?php $this->oConfig->input("db_name", array("placeholder" => "例: handcrowd")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="db_name"><?php l("ポート");?></label>
							<div class="controls"><?php $this->oConfig->input("db_port", array("class" => "input-mini", "placeholder" => "例: 3306")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<div class="controls"><button type="button" class="btn btn-testdb btn-mini"><i class="fa fa-warning"></i> <?php l("接続テスト");?></button></div>
						</fieldset> <!-- /fieldset -->
					</div>
					<div class="span6">
						<h3>3. <?php l("メール設定");?></h3>
						<fieldset class="control-group">
							<label class="control-label" for="mail_from"><?php l("発送用メールアドレス");?></label>
							<div class="controls"><?php $this->oConfig->input("mail_from", array("placeholder" => "例: webmaster@handcrowd.com")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="mail_fromname"><?php l("ユーザー名");?></label>
							<div class="controls"><?php $this->oConfig->input("mail_fromname", array("class" => "input-large")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="mail_smtp_auth">SMTP<?php l("認証");?></label>
							<div class="controls"><?php $this->oConfig->checkbox_single("mail_smtp_auth", "SMTP認証使用"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group" id="group_mail_smtp_server">
							<label class="control-label" for="mail_smtp_server">SMTP<?php l("サーバーアドレス");?></label>
							<div class="controls"><?php $this->oConfig->input("mail_smtp_server", array("placeholder" => "例: mail, smtp.google.com")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group" id="group_mail_smtp_user">
							<label class="control-label" for="mail_smtp_user">SMTP<?php l("ユーザーID");?></label>
							<div class="controls"><?php $this->oConfig->input("mail_smtp_user", array("placeholder" => "例: yamada")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group" id="group_mail_smtp_password">
							<label class="control-label" for="mail_smtp_password">SMTP<?php l("パスワード");?></label>
							<div class="controls"><?php $this->oConfig->password("mail_smtp_password"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group" id="group_mail_smtp_port">
							<label class="control-label" for="mail_smtp_port">SMTP<?php l("ポート");?></label>
							<div class="controls"><?php $this->oConfig->input("mail_smtp_port", array("class" => "input-mini", "placeholder" => "例: 25")); ?></div>
						</fieldset> <!-- /fieldset -->
					</div>
				</div>
				<div class="row">
					<div class="span12">
						<h3>4. <?php l("Google APIのためのOAuth設定");?></h3>
						<fieldset class="control-group">
							<label class="control-label" for="google_enable"><?php l("Googleとの連携");?></label>
							<div class="controls"><?php $this->oConfig->checkbox_single("google_enable", "Googleカレンダーと連携します。"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group" id="group_google_client_id">
							<label class="control-label" for="google_client_id"><?php l("CLIENT ID");?></label>
							<div class="controls"><?php $this->oConfig->input("google_client_id", array("class" => "input-xxlarge", "placeholder" => "例: 943226656786-8uh6vbqlmf4c5vsv4ak7gsl8dn8bo9on.apps.googleusercontent.com")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group" id="group_google_client_secret">
							<label class="control-label" for="google_client_secret"><?php l("CLIENT SECRET");?></label>
							<div class="controls"><?php $this->oConfig->input("google_client_secret", array("class" => "input-xxlarge", "placeholder" => "例: kcCZOrkUKWSFHqKcMc4jAiN0")); ?></div>
						</fieldset> <!-- /fieldset -->
						<p>クライアントIDとSECRETを得るには、<a href="https://console.developers.google.com/">Google Developers Console</a>へログインしてください。<br/>
							APIs &amp; auth > OAuth > Client ID for web applicationで以下のパラメーターを設定してください。
						</p>
						<fieldset class="control-group">
							<label class="control-label"><?php l("REDIRECT URIS");?></label>
							<div class="controls text-detail"><?php $this->oConfig->detail("google_redirect_uris"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label"><?php l("JAVASCRIPT ORIGINS");?></label>
							<div class="controls text-detail"><?php $this->oConfig->detail("google_javascript_origins"); ?></div>
						</fieldset> <!-- /fieldset -->
						<p>APIs &amp; auth > Consent Screenで以下のパラメーターを設定してください。</p>
						<fieldset class="control-group">
							<label class="control-label"><?php l("HOMEPAGE URL");?></label>
							<div class="controls text-detail"><?php $this->oConfig->detail("google_homepage_url"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label"><?php l("PRODUCT LOGO");?></label>
							<div class="controls text-detail"><?php $this->oConfig->detail("google_product_logo"); ?></div>
						</fieldset> <!-- /fieldset -->
					</div>
				</div>
				<div class="row">
					<div class="span6">
						<h3>5. <?php l("その他設定");?></h3>
						<fieldset class="control-group">
							<label class="control-label" for="admin_email"><?php l("管理者メールアドレス");?></label>
							<div class="controls"><?php $this->oConfig->input("admin_email", array("maxlength" => 50, "placeholder" => "管理者用メールアドレスを入力してください")); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="admin_password"><?php l("パスワード");?></label>
							<div class="controls"><?php $this->oConfig->password("admin_password"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="admin_password_confirm"><?php l("パスワード再入力");?></label>
							<div class="controls"><?php $this->oConfig->password("admin_password_confirm"); ?></div>
						</fieldset> <!-- /fieldset -->
						<fieldset class="control-group">
							<label class="control-label" for="install_sample"><?php l("サンプル");?></label>
							<div class="controls"><?php $this->oConfig->checkbox_single("install_sample", _l("サンプルデーターをインストールします。")); ?></div>
						</fieldset> <!-- /fieldset -->
					</div>
				</div>
				<div class="text-right">
					<button type="button" class="btn btn-primary" id="btnstart"><i class="fa fa-check"></i> <?php l("インストール開始");?></button>
				</div>
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

	<script src="js/utility.js"></script>

	<script type="text/javascript">
	disable_alarm = true;

	$(function () {

		var $form = $('#form').validate($.extend({
			rules : {
				require_php_ver: {
					required: true
				},
				installed_mysql: {
					required: true
				},
				installed_mbstring: {
					required: true
				},
				installed_simplexml: {
					required: true
				},
				installed_gd: {
					required: true
				},
				db_hostname: {
					required: true
				},
				db_user: {
					required: true
				},
				db_name: {
					required: true
				},
				db_port: {
					required: true,
					digits: true
				},
				admin_name: {
					required: true
				},
				admin_email: {
					required: true,
					email: true
				},
				admin_password: {
					required: true
				},
				admin_password_confirm: {
					equalTo: $('#admin_password')
				},
				mail_from: {
					required: true,
					email: true
				},
				mail_fromname: {
					required: true
				},
				mail_smtp_server: {
					required: true
				},
				mail_smtp_user: {
					required: true
				},
				mail_smtp_password: {
					required: true
				},
				mail_smtp_port: {
					required: true,
					digits: true
				},
				ldap_server: {
					required: true
				},
				google_client_id: {
					required: true,
				},
				google_client_secret: {
					required: true,
				}
			},

			// Messages for form validation
			messages : {
				require_php_ver: {
					required: "<?php l('このシステムPHP ' . MIN_PHP_VER . '以上でだけ動作します。');?>"
				},
				installed_mysql: {
					required: "<?php l('データベースを利用するにはMySQLエクステンションが必要です。');?>"
				},
				installed_mbstring: {
					required: "<?php l('多国語対応のためにmbstringクステンションが必要です。');?>"
				},
				installed_simplexml: {
					required: "<?php l('XMLファイルを読み出すにはSimpleXMLエクステンションが必要です。');?>"
				},
				installed_gd: {
					required: "<?php l('イメージ処理のためにはgdエクステンションが必要です。');?>"
				},
				db_hostname: {
					required: "<?php l('MySQLサーバーアドレスを入力してください。');?>"
				},
				db_user: {
					required: "<?php l('ユーザーIDを入力してください。');?>"
				},
				db_name: {
					required: "<?php l('データベース名を入力してください。');?>"
				},
				db_port: {
					required: "<?php l('ポートを入力してください。');?>",
					digits: "<?php l('数値を入力してください。');?>"
				},
				admin_name: {
					required: "<?php l('管理者IDを入力してください。');?>"
				},
				admin_email: {
					required: "<?php l('管理者メールアドレスを入力してください。');?>",
					email: "<?php l('メールアドレスが有効ではありません。');?>"
				},
				admin_password: {
					required: "<?php l('パスワードを入力してください。');?>"
				},
				admin_password_confirm: {
					equalTo: "<?php l('同じ値を入力してください。');?>"
				},
				mail_from: {
					required: "<?php l('発送用メールアドレスを入力してください。');?>",
					email: "<?php l('メールアドレスが有効ではありません。');?>"
				},
				mail_fromname: {
					required: "<?php l('発送用ユーザー名を入力してください。');?>"
				},
				mail_smtp_server: {
					required: "<?php l('SMTPサーバーアドレスを入力してください。');?>"
				},
				mail_smtp_user: {
					required: "<?php l('SMTPユーザーIDを入力してください。');?>"
				},
				mail_smtp_password: {
					required: "<?php l('SMTPパスワードを入力してください。');?>"
				},
				mail_smtp_port: {
					required: "<?php l('SMTPポートを入力してください。');?>",
					digits: "<?php l('数値を入力してください。');?>"
				},
				google_client_id: {
					required: "<?php l('CLIENT IDを入力してください。');?>"
				},
				google_client_secret: {
					required: "<?php l('CLIENT SECRETを入力してください。');?>"
				}
			}
		}, getValidationRules()));

		$('#form').ajaxForm({
			dataType : 'json',
			success: function(ret, statusText, xhr, form) {
				try {
					if (ret.err_code == 0)
					{
						var step = parseInt($('#step').val());
						if (step == 3)
						{
							hideMask();
							alertBox("<?php l('インストール完了');?>", "<?php l('システムインストールが完了しました。');?>", function() {
								goto_url("home");
							});
							return;
						}
						$('#step').val(step + 1);
						$('#form').submit();
					}
					else {
						hideMask();
						errorBox("<?php l('インストールエラー');?>", "<?php l('すみません、インストール中にエラーが発生しました。');?>");
						$('#step').val(0);
					}
				}
				finally {
				}
			}
		});

		$('#btnstart').click(function() {		
			if ($('#form').valid())
			{
				var ret = confirm("<?php l('インストールを開始します。');?>");
				if (ret)
				{
					$('#form').submit();
					showMask(true, "インストール中です...");
				}
			}
		});

		$('.btn-testdb').click(function() {
			$.ajax({
				url :"install/testdb_ajax",
				type : "post",
				dataType : 'json',
				data : { 
					db_hostname : $('#db_hostname').val(), 
					db_user : $('#db_user').val(), 
					db_password : $('#db_password').val(), 
					db_name : $('#db_name').val()
				},
				success : function(data){
					if (data.err_code == 0)
					{
						alertBox("<?php l('接続成功');?>", "<?php l('データベースに接続することができます。');?>");
					}
					else {
						errorBox("<?php l('接続失敗');?>", "<?php l('データベースに接続することができません。データーベース設定を再度確認してください。');?>");
					}
				},
				error : function() {
				},
				complete : function() {
				}
			});
		});

		$('#mail_smtp_auth').change(function() {
			if ($(this).isChecked())
			{
				$('#group_mail_smtp_server').show();
				$('#group_mail_smtp_user').show();
				$('#group_mail_smtp_password').show();
				$('#group_mail_smtp_port').show();
			}
			else {
				$('#group_mail_smtp_server').hide();
				$('#group_mail_smtp_user').hide();
				$('#group_mail_smtp_password').hide();
				$('#group_mail_smtp_port').hide();
			}
		});

		$('#google_enable').change(function() {
			if ($(this).isChecked())
			{
				$('#group_google_client_id').show();
				$('#group_google_client_secret').show();
			}
			else {
				$('#group_google_client_id').hide();
				$('#group_google_client_secret').hide();
			}
		});

	});
	</script>
  </body>
</html>
<?php } else { } ?>