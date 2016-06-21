<?php if (!$this->script_mode()) { ?>
<h2>写真アップロード</h2>
<p>JPEGやPNGイメージをアップロードしてください。</p>
<form id="form" action="common/upload_userphoto_ajax" class="form-horizontal" method="post" novalidate="novalidate">
	<fieldset>
		<div class="control-group">
			<label class="control-label" for="photo">写真ファイル</label>
			<div>
				<input type="file" name="photo" id="photo" class="form-control" />
			</div>
		</div>	
	</fieldset>
	<div class="form-actions" style="padding-top:100px;">
		<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> アップロード</button>
		<button type="button" class="btn" id="btn_cancel"><i class="fa fa-times"></i> 取消</button>
	</div>
<form>
<?php } else { ?>
<script type="text/javascript">
$(function () {

	var $form = $('#form').validate($.extend({
		rules : {
			photo: {
				required: true,
				imagefile: true
			}
		},

		// Messages for form validation
		messages : {
			photo: {
				required: '写真を選択してください。',
				imagefile: 'JPEGやPNGイメージではなければならないです。'
			}
		}
	}, getValidationRules()));

	$('#form').ajaxForm({
		dataType : 'json',
		success: function(ret, statusText, xhr, form) {
			try {
				if (ret.err_code == 0)
				{			
					parent.onBoothComplete(ret.tmp_path);
					parent.$.fancybox.close();
				}
				else if (ret.err_msg != "")
				{
					
				}
			}
			finally {
			}
		}
	});

	
	$('#btn_cancel').click(function() {
		parent.$.fancybox.close();
	});

});
</script>
<?php } ?>