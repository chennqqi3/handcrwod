<?php if (!$this->script_mode()) { ?>
<center>
	<ul id="photo_tab" class="nav nav-tabs">
		<li class="active"><a href="#" pane="capture">撮影</a></li>
		<li><a href="#" pane="upload">ファイルアップロード</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="capture">
			<div id="webcam">	
				<div>
					<h1>FlashPlayer 9をインストールしてください!</h1>
					<p><a href="http://www.adobe.com/go/getflashplayer"><!--<img 
					src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" 
					alt="Get Adobe Flash player" />--></a></p>
				</div>
			</div>
			<div class="text-right">
				<button type="button" class="btn btn-primary" id="btn_capture" disabled><i class="fa fa-camera"></i> <?php l("撮影");?></button>
				<button type="button" class="btn" id="btn_cancel"><i class="fa fa-check"></i> <?php l("確認");?></button>
			</div>
		</div>
		<div class="tab-pane" id="upload">
			<form id="fileDropzone" action="common/upload_avartar_ajax" class="dropzone dropzone-avartar">	
			</form>
			<input type="hidden" name="avaratr" id="avaratr"/>
			<div class="text-right">
				<button type="submit" class="btn btn-primary" id="btn_ok_upload" disabled><i class="fa fa-fw fa-check"></i> 確認</button>
				<button type="button" class="btn" id="btn_cancel_upload"><i class="fa fa-fw fa-times"></i> 取消</button>
			</div>
		</div>
	</div>
<center>

<script src="js/flash/swfobject.js" language="javascript"></script>
<?php $this->addcss("js/dropzone/dropzone.css"); ?>
<?php $this->addjs("js/dropzone/dropzone.js"); ?>
<?php } else { ?>
<script type="text/javascript">
	var photo_path = "";
	var flashvars = {};

	var parameters = {};
	parameters.scale = "noscale";
	parameters.wmode = "window";
	parameters.allowFullScreen = "true";
	parameters.allowScriptAccess = "always";

	var attributes = {};

	swfobject.embedSWF("swf/booth.swf", "webcam", "540", "280", "9", 
			"expressInstall.swf", flashvars, parameters, attributes);

	function onBoothReady() {
		btn_capture.disabled = false;
	};

	function onCaptureComplete(path) {
		photo_path = path;
	}

	$(function() {
		$('#photo_tab a').click(function(e) {
			e.preventDefault();
			$('#photo_tab li').removeClass('active');
			$(this).parents('li').addClass('active');
			$('.tab-content .tab-pane').hide();
			var pane = $(this).attr('pane');
			$('.tab-content #' + pane).show();
		});

		$('#btn_capture').click( function() {
			var el = document.getElementById('webcam');
			el.capture();
		});

		$('#btn_cancel').click(function() {
			parent.onBoothComplete(photo_path);
			parent.$.fancybox.close();
		});

		$('#btn_ok_upload').click(function() {
			parent.onBoothComplete($('#avaratr').val());
			parent.$.fancybox.close();
		});
		
		$('#btn_cancel_upload').click(function() {
			parent.$.fancybox.close();
		});

		Dropzone.options.fileDropzone = {
		  init: function() {
			this.on("success", function(file, responseText) {
				eval("var ret = " + responseText);
				if (ret.err_code == 0)
				{
					$('#avaratr').val(ret.tmp_path);
					$('#btn_ok_upload').aenable();
				}
				else {
					$(file.previewElement).removeClass('dz-success');
					$(file.previewElement).addClass('dz-error');
					file.previewElement.querySelector("[data-dz-errormessage]").textContent = ret.err_msg;
				}
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