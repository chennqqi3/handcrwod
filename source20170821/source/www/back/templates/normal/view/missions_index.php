<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>チャットルーム一覧 <span class="label label-info">検索 <?php p($this->counts); ?>件</span></h2>
	</div>
</header>
<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<form id="list_form" action="missions/index" method="post">
					<?php $this->search->hidden("sort_field"); ?>
					<?php $this->search->hidden("sort_order"); ?>
					<div class="navbar">
						<div class="navbar-inner">
							<div class="navbar-form pull-left">
								<?php $this->search->input("search_string", array("class" => "input-xxlarge", "placeholder" => _l("チャットルーム名やエージェント名を入力してください。"))); ?>

								<div class="btn-group">
									<button type="submit" class="btn"><i class="fa fa-fw fa-search"></i></button>
									<button type="button" class="btn" id="btnClearSearch"><i class="fa fa-fw fa-times"></i></button>
								</div>
							</div>
							<div class="navbar-form pull-right">
								<a href="missions/insert" class="btn btn-success"><i class="fa fa-fw fa-plus"></i> 登録</a>
								<button type="button" class="btn btn-delete"><i class="fa fa-trash-o"></i> <?php l("削除");?></button>
							</div>
						</div>
					</div>
					<table class="table table-striped table-hover" width="100%">					
						<thead>
							<tr>
								<th class="td-no"><input type="checkbox" id="check-all" /> #</th>
								<th style="width:30%"><?php $this->search->orderLabel('mission_name', _l('チャットルーム名')); ?></th>
								<th style="width:20%"><?php $this->search->orderLabel('client_name', _l('作成者')); ?></th>
								<th style="width:30%">最近タスク</th>
								<th>状態</th>
								<th class="td-no"><?php $this->search->orderLabel('mission_id', _l('ID')); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
							$i = $this->pagebar->start_no();
							foreach ($this->missions as $mission) {
						?>
							<tr>
								<td><input type="checkbox" class="check-item" mission_id="<?php p($mission->mission_id); ?>"/> <?php p($i); ?></td>
								<td><a href="missions/detail/<?php p($mission->mission_id); ?>"><?php $mission->detail("mission_name"); ?></a></td>
								<td><a href="users/detail/<?php p($mission->client_id); ?>"><img class="mini-avartar" src="<?php p(_avartar_url($mission->client_id)); ?>"/> <?php $mission->detail("client_name"); ?></a></td>
								<td><?php $mission->detail("last_task_name"); ?></td>
								<td><?php if ($mission->complete_flag == 1){?><span class="label label-success">完了済み</span><p><?php $mission->datetime("complete_time"); ?></p><?php }else{ ?><span class="label label-primary">進行中</span><?php } ?></td>
								<th><?php $mission->detail('mission_id'); ?></th>
							</tr>
						<?php
								$i ++;
							}
						?>
						</tbody>
					</table>
					<!--/table -->
					<?php _nodata_message($this->missions); ?>
				  
					<?php $this->pagebar->display("missions/index/"); ?>
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
			errorBox("警告", "編集するようなチャットルームを一つだけ選択してください。");
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
				params += $this.attr('mission_id');
			});
			confirmBox("チャットルーム削除", "選択されたチャットルームを本当に削除しましょうか？", function() {
				$.ajax({
					url :"missions/delete_ajax/" + params,
					type : "post",
					dataType : 'json',
					success : function(ret) {
						if (ret.err_code == 0)
						{	
							alertBox("削除完了", "チャットルームが成功に削除されました。", function() {
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
			errorBox("警告", "削除するようなチャットルームを選択してください。");
		}
	});
});
</script>
<?php } ?>