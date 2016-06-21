<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>プロファイル編集</h2>
	</div>
</header>

<div class="container">
	<div class="row">
		<div class="span7">
			<section id="main">
				<form id="save_form" action="myinfo/save_ajax" class="form-horizontal" method="post" novalidate="novalidate">
					<div class="row-fluid">
						<div class="span3">
							<fieldset>
								<div class="control-group">
									<div>
										<a href="common/booth" class="fancybox" fancy-width=560 fancy-height=390><img class="avartar" id="avartar" src="<?php p(_avartar_url($this->mUser->user_id)); ?>"/></a>
										<?php $this->mUser->input("photo", array("class" => "input-null")); ?>
										<a href="common/booth" class="btn fancybox" fancy-width=560 fancy-height=390><i class="fa fa-camera"></i> 写真変更</a>
									</div>
								</div>	
							</fieldset>
						</div>
						<div class="span9">
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="user_name">名前</label>
									<div class="controls">
										<?php $this->mUser->input("user_name"); ?> 
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="email">メールアドレス</label>
									<div class="controls">
										<?php $this->mUser->input("email"); ?>  
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					<div class="form-actions">
						<button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-save"></i> 保存</button>
					</div>
				</form>
			</section>
		</div>
		<?php if (_utype() == UTYPE_ADMIN) { ?>
		<div class="span5">
			<h3><i class="fa fa-key"></i> パスワード変更</h3>
			<form id="password_form" action="myinfo/password_ajax" class="form-horizontal" method="post" novalidate="novalidate">
				<div class="row-fluid">
					<div class="span12">
						<fieldset>
							<div class="control-group">
								<label class="control-label" for="old_password">以前のパスワード</label>
								<div class="controls">
									<input type="password" name="old_password" id="old_password" class="input-medium">
								</div>
							</div>
						</fieldset>
						<fieldset>
							<div class="control-group">
								<label class="control-label" for="new_password">新しいパスワード</label>
								<div class="controls">
									<input type="password" name="new_password" id="new_password" class="input-medium">
								</div>
							</div>
						</fieldset>
						<fieldset>
							<div class="control-group">
								<label class="control-label" for="confirm_new_password">新しいパスワード確認</label>
								<div class="controls">
									<input type="password" name="confirm_new_password" id="confirm_new_password" class="input-medium">
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="form-actions">
					<button type="submit" class="btn"><i class="fa fa-fw fa-save"></i> パスワード変更</button>
				</div>
			</form>
		</div>
		<?php } ?>
	</div>
</div>
<?php } else { ?>
<script type="text/javascript">
$(function () {
	var $save_form = $('#save_form').validate($.extend({
		rules : {
			user_name: {
				required: true
			},
			email: {
				required: true,
				email: true
			}
		},

		// Messages for form validation
		messages : {
			user_name : {
				required : '名前を入力してください。'
			},
			email: {
				required: 'メールアドレスを入力してください。',
				email: 'メールアドレスを入力してください。'
			}
		}
	}, getValidationRules()));

	$('#save_form').ajaxForm({
		dataType : 'json',
		success: function(ret, statusText, xhr, form) {
			try {
				if (ret.err_code == 0)
				{	
					alertBox("保存完了", "プロファイル情報が成功に保存されました。");
				}
				else if (ret.err_msg != "")
				{
					errorBox("保存エラー", ret.err_msg);
				}
			}
			finally {
			}
		}
	});

	var $password_form = $('#password_form').validate($.extend({
		rules : {
			old_password: {
				required: true
			},
			new_password: {
				required: true
			},
			confirm_new_password: {
				equalTo: $('#new_password')
			}
		},

		// Messages for form validation
		messages : {
			old_password : {
				required : '以前のパスワードを入力してください。'
			},
			new_password : {
				required : '新しいパスワードを入力してください。'
			},
			confirm_new_password: {
				equalTo : '新しいパスワードを再度入力してください。'
			}
		}
	}, getValidationRules()));

	$('#password_form').ajaxForm({
		dataType : 'json',
		success: function(ret, statusText, xhr, form) {
			try {
				if (ret.err_code == 0)
				{	
					alertBox("保存完了", "パスワードが成功に変更されました。");
				}
				else if (ret.err_msg != "")
				{
					errorBox("保存エラー", ret.err_msg);
				}
			}
			finally {
			}
		}
	});

});

function onBoothComplete(path)
{
	if (path != "")
	{
		$('#avartar').attr("src", path);
		$('#photo').val(path);
	}
}
</script>
<?php } ?>