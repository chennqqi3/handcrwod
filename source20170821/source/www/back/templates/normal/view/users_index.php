<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>ユーザー一覧 <span class="label label-info">検索 <?php p($this->counts); ?>名</span></h2>
	</div>
</header>
<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<form id="list_form" action="users/index" method="post">
					<?php $this->search->hidden("sort_field"); ?>
					<?php $this->search->hidden("sort_order"); ?>
					<div class="navbar">
						<div class="navbar-inner">
							<div class="navbar-form pull-left">
								<label><?php l("権限 :");?></label>
								<?php $this->search->select_utype("search_user_type",_l("-すべて-"), array("class" => "input-small")); ?>
								<?php $this->search->select_code("search_plan_type", CODE_PLAN, _l("-すべて-"), array("class" => "input-medium")); ?>
								<?php $this->search->input("search_string", array("class" => "input-large", "placeholder" => _l("名前を入力してください。"))); ?>

								<div class="btn-group">
									<button type="submit" class="btn"><i class="fa fa-fw fa-search"></i></button>
									<button type="button" class="btn" id="btnClearSearch"><i class="fa fa-fw fa-times"></i></button>
								</div>
							</div>
							<div class="navbar-form pull-right">
								<a href="users/insert" class="btn btn-success"><i class="fa fa-fw fa-plus"></i> 登録</a>
								<a href="users/edit" class="btn btn-edit"><i class="fa fa-edit"></i> <?php l("編集");?></a>
								<button type="button" class="btn btn-delete"><i class="fa fa-trash-o"></i> <?php l("削除");?></button>
								<button type="button" class="btn btn-active"><i class="fa fa-check"></i> <?php l("アクティブ");?></button>
							</div>
						</div>
					</div>
					<table class="table table-striped table-hover" width="100%">					
						<thead>
							<tr>
								<th class="td-no"><input type="checkbox" id="check-all" /> #</th>
								<th class="td-avartar"><?php l("写真");?></th>
								<th><?php $this->search->orderLabel('user_name', _l('名前')); ?></th>
								<th><?php $this->search->orderLabel('user_type', _l('権限')); ?></th>
								<th><?php $this->search->orderLabel('plan_type', _l('プラン')); ?></th>
								<th><?php $this->search->orderLabel('email', _l('メールアドレス')); ?></th>
								<th><?php $this->search->orderLabel('access_time', _l('最近アクセス日時')); ?></th>
								<th><?php $this->search->orderLabel('activate_flag', _l('アクティブ状態')); ?></th>
								<th class="td-no"><?php $this->search->orderLabel('user_id', _l('ID')); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
							$i = $this->pagebar->start_no();
							foreach ($this->users as $user) {
						?>
							<tr>
								<td><input type="checkbox" class="check-item" user_id="<?php p($user->user_id); ?>" activate_flag="<?php p($user->activate_flag); ?>"/> <?php p($i); ?></td>
								<td><img class="mini-avartar" src="<?php p(_avartar_url($user->user_id)); ?>"/></td>
								<td><a href="users/detail/<?php p($user->user_id);?>"><?php $user->detail("user_name"); ?></a></td>
								<td><?php $user->detail_utype("user_type"); ?></td>
								<td><span class="badge badge-success"><?php $user->detail_code("plan_type", CODE_PLAN); ?></span></td>
								<td><?php if ($user->facebook_id != null) { ?><i class="fa fa-facebook"></i> Facebook<?php } ?><?php $user->detail("email"); ?></td>
								<td><?php $user->datetime("access_time"); ?></td>
								<td><?php p($user->activate_flag == 1 ? "" : "未"); ?></td>
								<th><?php $user->detail('user_id'); ?></th>
							</tr>
						<?php
								$i ++;
							}
						?>
						</tbody>
					</table>
					<!--/table -->
					<?php _nodata_message($this->users); ?>
				  
					<?php $this->pagebar->display("users/index/"); ?>
				</form>
			</section>
		</div>
	</div>
</div>
<?php } else { ?>
<script type="text/javascript">
$(function () {
	$("#check-all").change(function() {
		checkall = $(this)[0].checked;
		$(".check-item").each(function() {
			$(this)[0].checked = checkall;
		});
	});

	$(".btn-edit").click(function() {
		var user_id = "";
		if ($(".check-item:checked").length == 1)
		{
			user_id = $(".check-item:checked").attr("user_id");
			goto_url($(this).attr("href") + "/" + user_id);
		}
		else {
			errorBox("警告", "編集するようなユーザーを一人だけ選択してください。");
		}
		
		return false;
	});

	$('.btn-delete').click(function() {
		if ($(".check-item:checked").length >= 1)
		{
			var params = "";
			$(".check-item:checked").each(function() {
				$this = $(this);
				if (params != "")
					params += "/";
				params += $this.attr('user_id');
			});
			confirmBox("ユーザー削除", "選択されたユーザーを本当に削除しましょうか？", function() {
				$.ajax({
					url :"users/delete_ajax/" + params,
					type : "post",
					dataType : 'json',
					success : function(ret) {
						if (ret.err_code == 0)
						{	
							alertBox("削除完了", "ユーザーが成功に削除されました。", function() {
								document.location.reload();
							});
						}
						else if (ret.err_msg != "")
						{
							errorBox("エラー発生", ret.err_msg);
						}
					},
					error : function() {
					},
					complete : function() {
					}
				});
			});
		}
		else {
			errorBox("警告", "削除するようなユーザーを選択してください。");
		}
	});

	$('.btn-active').click(function() {
		if ($(".check-item:checked").length >= 1)
		{
			var params = "";
			$(".check-item:checked").each(function() {
				$this = $(this);
				if (params != "")
					params += "/";
				if ($this.attr('activate_flag') == 0)
					params += $this.attr('user_id');
			});

			if (params == "")
				alertBox("アクティブ", "選択されたユーザーは既にアクティブされています。");

			confirmBox("アクティブ", "選択されたユーザーを本当にアクティブしましょうか？", function() {
				$.ajax({
					url :"users/activate_ajax/" + params,
					type : "post",
					dataType : 'json',
					success : function(ret) {
						if (ret.err_code == 0)
						{	
							alertBox("アクティブ完了", "ユーザーが成功にアクティブされました。", function() {
								document.location.reload();
							});
						}
						else if (ret.err_msg != "")
						{
							errorBox("エラー発生", ret.err_msg);
						}
					},
					error : function() {
					},
					complete : function() {
					}
				});
			});

		}
	});
});
</script>
<?php } ?>