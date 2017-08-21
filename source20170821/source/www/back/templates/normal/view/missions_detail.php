<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>チャットルーム詳細</h2>
	</div>
</header>

<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<form id="save_form" action="missions" class="form-horizontal" method="post" novalidate="novalidate">
					<div class="navbar">
						<div class="navbar-inner">
							<div class="navbar-form pull-right">
								<a href="missions" class="btn"><i class="fa fa-fw fa-times"></i> 戻る</a>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="user_type">チャットルーム名</label>
									<div class="controls text-detail">
										<?php $this->mMission->detail("mission_name"); ?>
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="user_name">管理者</label>
									<div class="controls text-detail">
										<a href="users/detail/<?php p($this->mMission->client_id) ?>"><?php $this->mMission->detail("client_name"); ?></a>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					<h4>タスク一覧</h4>
					<table class="table table-striped table-hover" width="100%">					
						<thead>
							<tr>
								<th class="td-no">#</th>
								<th>状態</th>
								<th>タスク名</th>
								<th>作成者</th>
								<th>作成日時</th>
								<th>担当者</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$i = 1;
							foreach ($this->tasks as $task) {
						?>
							<tr>
								<td><?php p($i); ?></td>
								<td><?php if ($task->complete_flag) { ?><i class="fa fa-check-circle-o"></i><?php } ?></td>
								<td><?php $task->detail("task_name"); ?></td>
								<td><a href="users/detail/<?php p($task->user_id); ?>"><img class="mini-avartar" src="<?php p(_avartar_url($task->user_id)); ?>"/> <?php $task->detail("creator_name"); ?></a></td>
								<td><?php $task->datetime("create_time"); ?></td>
								<td><a href="users/detail/<?php p($task->performer_id); ?>"><img class="mini-avartar" src="<?php p(_avartar_url($task->performer_id)); ?>"/> <?php $task->detail("performer_name"); ?></a></td>
							</tr>
						<?php
								$i ++;
							}
						?>
						</tbody>
					</table>
					<!--/table -->
					<h4>共有ユーザー一覧</h4>
					<table class="table table-striped table-hover" width="100%">					
						<thead>
							<tr>
								<th class="td-no">#</th>
								<th>名前</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$i = 1;
							foreach ($this->members as $member) {
						?>
							<tr>
								<td><?php p($i); ?></td>
								<td><a href="users/detail/<?php p($member->user_id); ?>"><img class="mini-avartar" src="<?php p(_avartar_url($member->user_id)); ?>"/> <?php $member->detail("user_name"); ?></a></td>
							</tr>
						<?php
								$i ++;
							}
						?>
						</tbody>
					</table>
					<!--/table -->
				</form>
			</section>
		</div>
	</div>
</div>
<?php } else { ?>
<?php } ?>