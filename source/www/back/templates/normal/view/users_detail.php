<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>ユーザー詳細</h2>
	</div>
</header>

<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<form id="save_form" action="users" class="form-horizontal" method="post" novalidate="novalidate">
					<div class="navbar">
						<div class="navbar-inner">
							<div class="navbar-form pull-right">
							<?php if (_utype() == UTYPE_ADMIN) { ?>
								<a href="users/edit/<?php p($this->mUser->user_id); ?>" class="btn btn-edit"><i class="fa fa-edit"></i> 編集</a>
							<?php } ?>
								<a href="users" class="btn"><i class="fa fa-fw fa-times"></i> 戻る</a>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="span2">
							<fieldset>
								<div class="control-group">
									<div>
										<img class="avartar" id="user_photo" src="<?php p(_avartar_url($this->mUser->user_id)); ?>"/>
									</div>
								</div>	
							</fieldset>
						</div>
						<div class="span10">
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="user_type">権限</label>
									<div class="controls text-detail">
										<?php $this->mUser->detail_utype("user_type"); ?>  
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="user_name">名前</label>
									<div class="controls text-detail">
										<?php $this->mUser->detail("user_name"); ?> 
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="email">メールアドレス</label>
									<div class="controls text-detail">
										<?php $this->mUser->detail("email"); ?>  
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="plan_type">ユーザープランタイプ</label>
									<div class="controls text-detail">
										<span class="badge badge-success"><?php $this->mUser->detail_code("plan_type", CODE_PLAN); ?></span>
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="plan_start_date">プラン開始日付</label>
									<div class="controls text-detail">
										<?php $this->mUser->date("plan_start_date"); ?>  
									</div>
								</div>
							</fieldset>
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="plan_end_date">プラン終了日付</label>
									<div class="controls text-detail">
										<?php $this->mUser->date("plan_end_date"); ?>  
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
<?php } ?>