<?php if (!$this->script_mode()) { ?>
<form id="fileDropzone" action="common/upload_ajax" class="dropzone">	
</form>
<input type="hidden" name="files" id="files"/>
<div class="row-fluid">
	<div class="span12">
		<div class="navbar">
			<div class="navbar-inner">
				<div class="navbar-form pull-right">
					<button type="submit" class="btn btn-primary btn-ok"><i class="fa fa-fw fa-check"></i> 確認</button>
					<button type="button" class="btn btn-cancel"><i class="fa fa-fw fa-times"></i> 取消</button>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $this->addcss("js/dropzone/dropzone.css"); ?>
<?php $this->addjs("js/dropzone/dropzone.js"); ?>
<?php } else { ?>
<script type="text/javascript">
$(function () {
	$('.btn-ok').click(function() {
		parent.onUploadComplete($('#files').val(), '<?php p($this->upload_type); ?>');
		parent.$.fancybox.close();
	});
	
	$('.btn-cancel').click(function() {
		parent.$.fancybox.close();
	});

	
	Dropzone.options.fileDropzone = {
	  init: function() {
		this.on("success", function(file, responseText) {
			eval("var ret = " + responseText);
			files = $('#files').val();
			if (files != "") files += ";";
			$('#files').val(files + ret.path + ":" + ret.filename);

			/*
			photo_url = ret.photo_url;
			$("<a href='" + photo_url + "' target='_blank'>" + photo_url + "</a>").appendTo($(file.previewTemplate));
			*/
		});
		this.on("error", function(file, responseText) {
			eval("var ret = " + responseText);
			file.previewElement.querySelector("[data-dz-errormessage]").textContent = ret.err_msg;
		});
	  }
	};

});
</script>
<?php } ?>