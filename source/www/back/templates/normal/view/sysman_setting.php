<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>システム設定</h2>
	</div>
</header>

<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<form id="form" action="sysman/setting_save_ajax" class="form-signin form-horizontal" method="post">
					<div class="row">
						<div class="span6">
							<h3>1. <?php l("データベース設定");?></h3>
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
							<h3>2. <?php l("メール設定");?></h3>
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
								<div class="controls"><?php $this->oConfig->input("mail_smtp_server", array("placeholder" => "例: smtp.google.com")); ?></div>
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
						<div class="span6">
							<h3>3. <?php l("Google APIのためのOAuth設定");?></h3>
							<fieldset class="control-group">
								<label class="control-label" for="google_enable"><?php l("Googleとの連携");?></label>
								<div class="controls"><?php $this->oConfig->checkbox_single("google_enable", "Googleカレンダーと連携します。"); ?></div>
							</fieldset> <!-- /fieldset -->
							<fieldset class="control-group" id="group_google_client_id">
								<label class="control-label" for="google_client_id"><?php l("CLIENT ID");?></label>
								<div class="controls"><?php $this->oConfig->input("google_client_id", array("class" => "input-xlarge", "placeholder" => "例: 943226656786-8uh6vbqlmf4c5vsv4ak7gsl8dn8bo9on.apps.googleusercontent.com")); ?></div>
							</fieldset> <!-- /fieldset -->
							<fieldset class="control-group" id="group_google_client_secret">
								<label class="control-label" for="google_client_secret"><?php l("CLIENT SECRET");?></label>
								<div class="controls"><?php $this->oConfig->input("google_client_secret", array("class" => "input-xlarge", "placeholder" => "例: kcCZOrkUKWSFHqKcMc4jAiN0")); ?></div>
							</fieldset> <!-- /fieldset -->
							<p>クライアントIDとSECRETを得るには、<a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a>へログインしてください。<br/>
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
						<div class="span6">
							<h3>4. <?php l("Facebook設定");?></h3>
							<fieldset class="control-group">
								<label class="control-label" for="facebook_enable"><?php l("Facebook");?></label>
								<div class="controls"><?php $this->oConfig->checkbox_single("facebook_enable", "Facebookログイン機能を使用可能とする。"); ?></div>
							</fieldset> <!-- /fieldset -->
							<fieldset class="control-group" id="group_facebook_app_id">
								<label class="control-label" for="facebook_app_id"><?php l("APP ID");?></label>
								<div class="controls"><?php $this->oConfig->input("facebook_app_id", array("class" => "input-xlarge", "placeholder" => "例: 700225073390454")); ?></div>
							</fieldset> <!-- /fieldset -->
							<fieldset class="control-group" id="group_facebook_app_secret">
								<label class="control-label" for="facebook_app_secret"><?php l("APP SECRET");?></label>
								<div class="controls"><?php $this->oConfig->input("facebook_app_secret", array("class" => "input-xlarge", "placeholder" => "例: b23404cbe3d19dd2a4bf3555a16f7928")); ?></div>
							</fieldset> <!-- /fieldset -->
							<p>APP IDとSECRETを得るには、<a href="https://developers.facebook.com/" target="_blank">Facebook App Development</a>へログインしてください。<br/>
								My Appsに新たなアプリを新規作成しサイトのURLを下記のように設定してください。<br/>
								App DetailsのIconsにはapp/client/images/logo_1024.pngの画像をアップロードしてください。
							</p>
							<fieldset class="control-group">
								<label class="control-label"><?php l("SITE URL");?></label>
								<div class="controls text-detail"><?php $this->oConfig->detail("facebook_site_url"); ?></div>
							</fieldset> <!-- /fieldset -->
							<h3>5. <?php l("チャットサーバー設定");?></h3>
							<fieldset class="control-group" id="group_cserver_host">
								<label class="control-label" for="cserver_host"><?php l("サーバーＩＰ");?></label>
								<div class="controls"><?php $this->oConfig->input("cserver_host", array("class" => "input-xlarge", "placeholder" => "例: 133.222.44.27")); ?></div>
							</fieldset> <!-- /fieldset -->
							<fieldset class="control-group" id="group_cserver_port">
								<label class="control-label" for="cserver_port"><?php l("ポート");?></label>
								<div class="controls"><?php $this->oConfig->input("cserver_port", array("class" => "input-large", "placeholder" => "例: 9000")); ?></div>
							</fieldset> <!-- /fieldset -->
							<fieldset class="control-group">
								<label class="control-label" for="cserver_ssl"><?php l("SSL");?></label>
								<div class="controls"><?php $this->oConfig->checkbox_single("cserver_ssl", "SSLソケット使用"); ?></div>
							</fieldset> <!-- /fieldset -->
								<label class="control-label" for="cserver_debug"><?php l("デバッグ");?></label>
								<div class="controls"><?php $this->oConfig->checkbox_single("cserver_debug", "デバッグ情報を出力する。"); ?></div>
							</fieldset> <!-- /fieldset -->
							<h3>6. <?php l("キャッシューサーバー設定");?></h3>
							<fieldset class="control-group" id="group_memcache_server">
								<label class="control-label" for="memcache_server"><?php l("サーバーＩＰ（複数）");?></label>
								<div class="controls"><?php $this->oConfig->input("memcache_server", array("class" => "input-xlarge", "placeholder" => "例: localhost,133.222.44.27")); ?></div>
							</fieldset> <!-- /fieldset -->
							<fieldset class="control-group" id="group_memcache_port">
								<label class="control-label" for="memcache_port"><?php l("ポート");?></label>
								<div class="controls"><?php $this->oConfig->input("memcache_port", array("class" => "input-large", "placeholder" => "例: 9000")); ?></div>
							</fieldset> <!-- /fieldset -->
							<fieldset class="control-group" id="group_memcache_uri">
								<label class="control-label" for="memcache_uri"><?php l("URI");?></label>
								<div class="controls"><?php $this->oConfig->input("memcache_uri", array("class" => "input-xlarge", "placeholder" => "例: ")); ?></div>
							</fieldset> <!-- /fieldset -->
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<h3>7. <?php l("ユーザープラン設定");?></h3>
							<table class="table table-bordered">
								<tr>
									<th></th>
									<th><?php $this->oPlanConfig0->detail("plan_type_string"); ?></th>
									<th><?php $this->oPlanConfig1->detail("plan_type_string"); ?></th>
									<th><?php $this->oPlanConfig2->detail("plan_type_string"); ?></th>
									<th><?php $this->oPlanConfig3->detail("plan_type_string"); ?></th>
								</tr>
								<tr>
									<th>月額費用／ユーザ<br/><small>（※ご契約は組合せで5ユーザ～）</small></th>
									<td>￥<?php $this->oPlanConfig0->detail("month_price"); ?></td>
									<td>￥<?php $this->oPlanConfig1->detail("month_price"); ?></td>
									<td>￥<?php $this->oPlanConfig2->detail("month_price"); ?></td>
									<td>￥<?php $this->oPlanConfig3->detail("month_price"); ?></td>
								</tr>
								<tr>
									<th>（年額費用）</th>
									<td>￥<?php $this->oPlanConfig0->detail("year_price"); ?></td>
									<td>￥<?php $this->oPlanConfig1->detail("year_price"); ?></td>
									<td>￥<?php $this->oPlanConfig2->detail("year_price"); ?></td>
									<td>￥<?php $this->oPlanConfig3->detail("year_price"); ?></td>
								</tr>
								<tr>
									<th>グループ数</th>
									<td><?php $this->oPlanConfig0->detail("max_homes"); ?></td>
									<td><?php $this->oPlanConfig1->detail("max_homes"); ?></td>
									<td><?php $this->oPlanConfig2->detail("max_homes"); ?></td>
									<td><?php $this->oPlanConfig3->detail("max_homes"); ?></td>
								</tr>
								<tr>
									<th>チャットルーム数</th>
									<td><?php $this->oPlanConfig0->detail("max_missions"); ?></td>
									<td><?php $this->oPlanConfig1->detail("max_missions"); ?></td>
									<td><?php $this->oPlanConfig2->detail("max_missions"); ?></td>
									<td><?php $this->oPlanConfig3->detail("max_missions"); ?></td>
								</tr>
								<tr>
									<th>テンプレート数</th>
									<td><?php $this->oPlanConfig0->detail("max_templates"); ?></td>
									<td><?php $this->oPlanConfig1->detail("max_templates"); ?></td>
									<td><?php $this->oPlanConfig2->detail("max_templates"); ?></td>
									<td><?php $this->oPlanConfig3->detail("max_templates"); ?></td>
								</tr>
								<tr>
									<th>リピート（繰り返し）設定</th>
									<td><?php p($this->oPlanConfig0->repeat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig1->repeat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig2->repeat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig3->repeat_flag ? "◎" : "-"); ?></td>
								</tr>
								<tr>
									<th>ファイル添付</th>
									<td><?php $this->oPlanConfig0->detail("max_upload"); ?>GB</td>
									<td><?php $this->oPlanConfig1->detail("max_upload"); ?>GB</td>
									<td><?php $this->oPlanConfig2->detail("max_upload"); ?>GB</td>
									<td><?php $this->oPlanConfig3->detail("max_upload"); ?>GB</td>
								</tr>
								<tr>
									<th>背景設定</th>
									<td><?php p($this->oPlanConfig0->back_image_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig1->back_image_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig2->back_image_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig3->back_image_flag ? "◎" : "-"); ?></td>
								</tr>
								<tr>
									<th>タスク実績CSV出力</th>
									<td><?php p($this->oPlanConfig0->job_csv_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig1->job_csv_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig2->job_csv_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig3->job_csv_flag ? "◎" : "-"); ?></td>
								</tr>
								<tr>
									<th>フォームお問合せ</th>
									<td><?php p($this->oPlanConfig0->contact_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig1->contact_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig2->contact_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig3->contact_flag ? "◎" : "-"); ?></td>
								</tr>
								<tr>
									<th>専用チャット</th>
									<td><?php p($this->oPlanConfig0->chat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig1->chat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig2->chat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig3->chat_flag ? "◎" : "-"); ?></td>
								</tr>
								<tr>
									<th>電話・Skype・ビデオチャット</th>
									<td><?php p($this->oPlanConfig0->superchat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig1->superchat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig2->superchat_flag ? "◎" : "-"); ?></td>
									<td><?php p($this->oPlanConfig3->superchat_flag ? "◎" : "-"); ?></td>
								</tr>
								<tr>
									<th>スキルレポート作成代行</th>
									<td>1レポートあたり<?php $this->oPlanConfig0->detail("skill_report"); ?>円</td>
									<td>1レポートあたり<?php $this->oPlanConfig1->detail("skill_report"); ?>円</td>
									<td>1レポートあたり<?php $this->oPlanConfig2->detail("skill_report"); ?>円</td>
									<td>1レポートあたり<?php $this->oPlanConfig3->detail("skill_report"); ?>円</td>
								</tr>
								<tr>
									<th>アウトソーシング・サービス</th>
									<td>納品物の<?php $this->oPlanConfig0->detail("outsourcing_fee"); ?>%</td>
									<td>納品物の<?php $this->oPlanConfig1->detail("outsourcing_fee"); ?>%</td>
									<td>納品物の<?php $this->oPlanConfig2->detail("outsourcing_fee"); ?>%</td>
									<td>納品物の<?php $this->oPlanConfig3->detail("outsourcing_fee"); ?>%</td>
								</tr>
								<tr>
									<th>訪問サービス（遠方地の交通費別途）</th>
									<td><?php $this->oPlanConfig0->detail("visit_service_price"); ?>円／回</td>
									<td><?php $this->oPlanConfig1->detail("visit_service_price"); ?>円／回</td>
									<td><?php $this->oPlanConfig2->detail("visit_service_price"); ?>円／回</td>
									<td><?php $this->oPlanConfig3->detail("visit_service_price"); ?>円／回</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="text-right">
						<button type="button" class="btn btn-primary" id="btnstart"><i class="fa fa-check"></i> <?php l("保存");?></button>
					</div>
				</form>
			</section>
		</div>
	</div>
</div> <!-- /container -->
<?php } else { ?>
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
			google_client_id: {
				required: true,
			},
			google_client_secret: {
				required: true,
			},
			facebook_app_id: {
				required: true,
			},
			facebook_app_secret: {
				required: true,
			},
			cserver_host: {
				required: true,
			},
			cserver_port: {
				required: true,
			},
			memcache_server: {
				required: true,
			},
			memcache_port: {
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
			},
			facebook_app_id: {
				required: "<?php l('APP IDを入力してください。');?>"
			},
			facebook_app_secret: {
				required: "<?php l('APP SECRETを入力してください。');?>"
			},
			cserver_host: {
				required: "<?php l('サーバーＩＰを入力してください。');?>"
			},
			cserver_port: {
				required: "<?php l('ポートを入力してください。');?>"
			},
			memcache_server: {
				required: "<?php l('サーバーＩＰを入力してください。複数指定の場合「,」区切りしてください。');?>"
			},
			memcache_port: {
				required: "<?php l('ポートを入力してください。');?>"
			}
		}
	}, getValidationRules()));

	$('#form').ajaxForm({
		dataType : 'json',
		success: function(ret, statusText, xhr, form) {
			try {
				if (ret.err_code == 0)
				{
					alertBox("<?php l('設定完了');?>", "<?php l('システム設定が完了しました。');?>", function() {
					});
					return;
				}
				else {
					hideMask();
					errorBox("<?php l('設定エラー');?>", "<?php l('すみません。システム設定中にエラーが発生しました。');?>");
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
			var ret = confirm("<?php l('システムを設定しましょうか？');?>");
			if (ret)
			{
				$('#form').submit();
			}
		}
	});

	$('.btn-testdb').click(function() {
		$.ajax({
			url :"sysman/testdb_ajax",
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

	$('#facebook_enable').change(function() {
		if ($(this).isChecked())
		{
			$('#group_facebook_app_id').show();
			$('#group_facebook_app_secret').show();
		}
		else {
			$('#group_facebook_app_id').hide();
			$('#group_facebook_app_secret').hide();
		}
	});

	$('#mail_smtp_auth').change();
	$('#google_enable').change();
	$('#facebook_enable').change();
});
</script>
<?php } ?>