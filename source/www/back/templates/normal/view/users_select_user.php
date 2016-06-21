<?php if (!$this->script_mode()) { ?>
<div>
	<div class="banner">
		<h2>ユーザー一覧 <span class="label label-info">検索 <?php p($this->counts); ?>名</span></h2>
	</div>

	<div class="row-fluid">
		<div class="span12">		
			<form id="list_form" action="users/select_user" method="post">
				<?php $this->search->hidden("sort_field"); ?>
				<?php $this->search->hidden("sort_order"); ?>
				<div class="navbar">
					<div class="navbar-inner">
						<div class="navbar-form pull-left">
							<?php $this->search->input("search_string", array("class" => "input-large", "placeholder" => _l("名前を入力してください。"))); ?>
				
							<div class="btn-group">
								<button type="submit" class="btn"><i class="fa fa-fw fa-search"></i></button>
								<button type="button" class="btn" id="btnClearSearch"><i class="fa fa-fw fa-times"></i></button>
							</div>
						</div>
					</div>
				</div>
				<table id="lists" class="table table-striped table-hover" width="100%">
					<thead>
						<tr>
							<th class="td-no">#</th>
							<th class="td-avartar"><?php l("写真");?></th>
							<th><?php $this->search->orderLabel('user_type', _l('権限')); ?></th>
							<th><?php $this->search->orderLabel('user_name', _l('名前')); ?></th>
							<th><?php $this->search->orderLabel('email', _l('メールアドレス')); ?></th>
							<th class="td-no"><?php $this->search->orderLabel('user_id', _l('ID')); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php
						_nodata_message($this->users);

						$i = $this->pagebar->start_no();
						foreach ($this->users as $user) {
					?>
						<tr user_id="<?php p($user->user_id); ?>" user_name="<?php p($user->user_name); ?>">
							<td style="cursor:pointer;"><?php p($i); ?></td>
							<td style="cursor:pointer;"><img class="mini-avartar" src="<?php p(_avartar_url($user->user_id)); ?>"/></td>
							<td style="cursor:pointer;"><?php $user->detail_utype("user_type"); ?></td>
							<td style="cursor:pointer;"><?php $user->detail("user_name"); ?></td>
							<td style="cursor:pointer;"><?php $user->detail("email"); ?></td>
							<th style="cursor:pointer;"><?php $user->detail('user_id'); ?></th>
						</tr>
					<?php
							$i ++;
						}
					?>
					</tbody>
				</table>
				<!--/table -->
			  
				<?php $this->pagebar->display("users/select_user/"); ?>
			</form>
		</div>
	</div>
</div>
<?php } else { ?>
<script type="text/javascript">
$(function () {
	$('tbody tr').click(function() {
		user_id = $(this).attr('user_id');
		user_name = $(this).attr('user_name');
		
		parent.onSelectUser(user_id, user_name);
		parent.$.fancybox.close();
	});
});
</script>
<?php } ?>