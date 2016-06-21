<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<?php if ($this->mission_id == null) { ?>
		<h2>チャットルーム登録</h2>
		<?php } else { ?>
		<h2>チャットルーム編集</h2>
		<?php } ?>
	</div>
</header>

<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<form id="save_form" action="missions/save_ajax/" class="form-horizontal" method="post" novalidate="novalidate">
					<?php $this->mMission->hidden("user_id"); ?>  
					<div class="navbar">
						<div class="navbar-inner">
							<div class="navbar-form pull-right">
								<button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-save"></i> 保存</button>
								<a href="missions" class="btn"><i class="fa fa-fw fa-times"></i> 取消</a>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="mission_name">チャットルーム名</label>
									<div class="controls">
										<?php $this->mMission->input("mission_name"); ?> 
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="client_name">管理者</label>
									<div class="controls">
										<?php $this->mMission->input_user("client_id", "client_name"); ?> 
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
			mission_name: {
				required: true,
			},
			client_name: {
				required: true
			}
		},

		// Messages for form validation
		messages : {
			mission_name : {
				required : 'チャットルーム名を入力してください。'
			},
			client_name : {
				required : '管理者を入力してください。'
			}
		}
	}, getValidationRules()));

	$('#save_form').ajaxForm({
		dataType : 'json',
		success: function(ret, statusText, xhr, form) {
			try {
				if (ret.err_code == 0)
				{	
					alertBox("保存完了", "チャットルーム情報が成功に保存されました。", function() {
						goto_url("missions");
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

function onSelectUser(user_id, user_name)
{
	$('#client_id').val(user_id);
	$('#client_name').val(user_name);
}
</script>
<?php } ?>