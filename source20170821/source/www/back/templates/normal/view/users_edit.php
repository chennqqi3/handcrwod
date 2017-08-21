<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<?php if ($this->mUser->user_id == null) { ?>
		<h2>ユーザー登録</h2>
		<?php } else { ?>
		<h2>ユーザー編集</h2>
		<?php } ?>
	</div>
</header>

<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<form id="save_form" action="users/save_ajax/<?php p($this->mUser->user_id); ?>" class="form-horizontal" method="post" novalidate="novalidate">
					<?php $this->mUser->hidden("user_id"); ?>  
					<div class="navbar">
						<div class="navbar-inner">
							<div class="navbar-form pull-right">
								<button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-save"></i> 保存</button>
								<a href="users" class="btn"><i class="fa fa-fw fa-times"></i> 取消</a>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="span2">
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
						<div class="span10">
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="user_type">権限</label>
									<div class="controls">
										<?php $this->mUser->select_utype("user_type"); ?>  
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="user_name">名前</label>
									<div class="controls">
										<?php $this->mUser->input("user_name"); ?> 
									</div>
								</div>
							</fieldset>
							<?php if ($this->mUser->user_id == null) { ?>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="password">パスワード</label>
									<div class="controls">
										<input type="password" name="password" id="password" class="input-medium">
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="confirm_password">パスワード再入力</label>
									<div class="controls">
										<input type="password" name="confirm_password" id="confirm_password" class="input-medium">
									</div>
								</div>
							</fieldset>
							<?php } ?>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="email">メールアドレス</label>
									<div class="controls">
										<?php $this->mUser->input("email"); ?>  
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="plan_type">ユーザープランタイプ</label>
									<div class="controls">
										<?php $this->mUser->select_code("plan_type", CODE_PLAN); ?>  
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="plan_start_date">プラン開始日付</label>
									<div class="controls">
										<?php $this->mUser->datebox("plan_start_date"); ?>  
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="plan_end_date">プラン終了日付</label>
									<div class="controls">
										<?php $this->mUser->datebox("plan_end_date"); ?>  
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</form>
			</section>
		</div>
	</div>
</div>
<?php } else { ?>
<script type="text/javascript">
$(function () {
	var $save_form = $('#save_form').validate($.extend({
		rules : {
			password: {
				required: true
			},
			confirm_password: {
				equalTo: $('#password')
			},
			sex: {
				required: true
			},
			email: {
				email: true,
				unique_email: true
			}
		},

		// Messages for form validation
		messages : {
			user_name : {
				required : '名前を入力してください。'
			},
			password : {
				required : 'パスワードを入力してください。'
			},
			confirm_password: {
				equalTo : 'パスワードを再度入力してください。'
			},
			email: {
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
					alertBox("保存完了", "ユーザー情報が成功に保存されました。", function() {
						goto_url("users");
					});
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