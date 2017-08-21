<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>スキル管理 <span class="label label-info">検索 <?php p($this->counts); ?>件</span></h2>
	</div>
</header>
<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<form id="list_form" action="skills/index" method="post">
					<?php $this->search->hidden("sort_field"); ?>
					<?php $this->search->hidden("sort_order"); ?>
					<div class="navbar">
						<div class="navbar-inner">
							<div class="navbar-form pull-left">
								<?php $this->search->input("search_string", array("class" => "input-xxlarge", "placeholder" => _l("スキル名を入力してください。"))); ?>

								<div class="btn-group">
									<button type="submit" class="btn"><i class="fa fa-fw fa-search"></i></button>
									<button type="button" class="btn" id="btnClearSearch"><i class="fa fa-fw fa-times"></i></button>
								</div>
							</div>
						</div>
					</div>
					<table class="table table-striped table-hover" width="100%">					
						<thead>
							<tr>
								<th class="td-no">#</th>
								<th><?php $this->search->orderLabel('skill_name', _l('スキル名')); ?></th>
								<th class="td-datetime"><?php $this->search->orderLabel('create_time', _l('登録日時')); ?></th>
								<th class="td-no"><?php $this->search->orderLabel('skill_id', _l('ID')); ?></th>
								<th class="td-buttons"></th>
							</tr>
						</thead>
						<tbody>
						<?php
							$i = $this->pagebar->start_no();
							foreach ($this->skills as $skill) {
						?>
							<tr>
								<td><?php p($i); ?></td>
								<td><?php $skill->detail("skill_name"); ?></td>
								<td><?php $skill->datetime("create_time"); ?></td>
								<th><?php $skill->detail('skill_id'); ?></th>
								<td>
									<button type="button" class="btn btn-mini btn-edit" data-skill-id="<?php p($skill->skill_id); ?>" data-skill-name="<?php p($skill->skill_name); ?>"><i class="fa fa-fw fa-edit"></i></button>
									<button type="button" class="btn btn-mini btn-delete" data-skill-id="<?php p($skill->skill_id); ?>"><i class="fa fa-fw fa-trash-o"></i></button>
								</td>
							</tr>
						<?php
								$i ++;
							}
						?>
						</tbody>
					</table>
					<!--/table -->
					<?php _nodata_message($this->skills); ?>
				  
					<?php $this->pagebar->display("skills/index/"); ?>
				</form>

				<form id="save_form" action="skills/save_ajax" class="form-horizontal" method="post" novalidate="novalidate">				
					<h4 id="title_add">スキルの追加</h4>
					<h4 id="title_edit" style="display:none">スキルの編集</h4>
					<?php $this->mSkill->hidden("skill_id"); ?> 
					<div class="row">
						<div class="span8">
							<fieldset>
								<div class="control-group">
									<label class="control-label" for="skill_name">スキル名</label>
									<div class="controls">
										<?php $this->mSkill->input("skill_name", array("class" => "input-xxlarge")); ?> 
									</div>
								</div>
							</fieldset>
						</div>
						<div class="span4 text-right">
							<button id="btn_add" type="submit" class="btn btn-success"><i class="fa fa-fw fa-plus"></i> 登録</button>
							<button id="btn_save" type="submit" class="btn btn-success" style="display:none"><i class="fa fa-fw fa-save"></i> 保存</button>
							<button id="btn_cancel" type="button" class="btn"><i class="fa fa-fw fa-times"></i> 取消</button>
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
	$(".btn-edit").click(function() {
		var skill_id = $(this).data("skillId");
		var skill_name = $(this).data("skillName");

		$('#skill_id').val(skill_id);
		$('#skill_name').val(skill_name);

		$('#title_add').hide();
		$('#btn_add').hide();

		$('#title_edit').show();
		$('#btn_save').show();

		return false;
	});

	$('#btn_cancel').click(function() {
		$('#skill_id').val('');
		$('#skill_name').val('');

		$('#title_add').show();
		$('#btn_add').show();

		$('#title_edit').hide();
		$('#btn_save').hide();
	});

	$('.btn-delete').click(function() {
		var skill_id = $(this).data("skillId");
		confirmBox("スキル削除", "このスキルを本当に削除しましょうか？", function() {
			$.ajax({
				url :"skills/delete_ajax/" + skill_id,
				type : "post",
				dataType : 'json',
				success : function(ret) {
					if (ret.err_code == 0)
					{	
						alertBox("削除完了", "スキルが成功に削除されました。", function() {
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
	});

	var $save_form = $('#save_form').validate($.extend({
		rules : {
			skill_name: {
				required: true
			}
		},

		// Messages for form validation
		messages : {
			skill_name : {
				required : 'スキル名を入力してください。'
			}
		}
	}, getValidationRules()));

	$('#save_form').ajaxForm({
		dataType : 'json',
		success: function(ret, statusText, xhr, form) {
			try {
				if (ret.err_code == 0)
				{	
					alertBox("保存完了", "スキル情報が成功に保存されました。", function() {
						goto_url("skills");
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
</script>
<?php } ?>