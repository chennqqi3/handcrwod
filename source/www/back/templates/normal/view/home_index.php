<?php if (!$this->script_mode()) { ?>

<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>バックエンドホーム</h2>
	</div>
</header>
<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<div class="row-fluid">
					<?php if (_utype() == UTYPE_ADMIN) { ?>
					<div class="span6">
						<h3>最近登録されたユーザー</h3>
						<table class="table table-striped table-hover" width="100%">					
							<thead>
								<tr>
									<th>名前</th>
									<th>メールアドレス</th>
									<th>登録日時</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$i = 1;
								foreach ($this->users as $user) {
							?>
								<tr>
									<td><img class="mini-avartar" src="<?php p(_avartar_url($user->user_id)); ?>"/> <a href="users/detail/<?php p($user->user_id);?>"><?php $user->detail("user_name"); ?></a></td>
									<td><?php $user->detail("email"); ?></td>
									<td><?php $user->datetime("create_time"); ?></td>
								</tr>
							<?php
									$i ++;
								}
							?>
							</tbody>
						</table>
						<!--/table -->

						<?php _nodata_message($this->users); ?>
					</div>
					<div class="span6">
						<h3>最近チャットルーム</h3>
						<table class="table table-striped table-hover" width="100%">					
							<thead>
								<tr>
									<th>名前</th>
									<th>登録者</th>
									<th>登録日時</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$i = 1;
								foreach ($this->missions as $mission) {
							?>
								<tr>
									<td><a href="missions/detail/<?php p($mission->mission_id);?>"><?php $mission->detail("mission_name"); ?></a></td>
									<td><img class="mini-avartar" src="<?php p(_avartar_url($mission->client_id)); ?>"/> <a href="users/detail/<?php p($mission->client_id);?>"><?php $mission->detail("client_name"); ?></a></td>
									<td><?php $mission->datetime("create_time"); ?></td>
								</tr>
							<?php
									$i ++;
								}
							?>
							</tbody>
						</table>
						<!--/table -->

						<?php _nodata_message($this->missions); ?>
					</div>
					<?php } else { ?>
					<?php } ?>
				</div>
			</section>
		</div>
	</div>
</div>
<?php } else { ?>

<?php } ?>