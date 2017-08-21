var urlField;
CKEDITOR.on( 'dialogDefinition', function( ev ) {
	// Take the dialog name and its definition from the event data.
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;

	// Check if the definition is from the dialog we're
	// interested on (the "Link" dialog).
	if ( dialogName == 'image') {
		var infoTab = dialogDefinition.getContents( 'info' );

		// Add a new tab to the "Link" dialog.
		dialogDefinition.addContents({
			id: 'uploadTab',
			label: 'Upload',
			accessKey: 'U',
			elements: [
				{
					id: 'upload_form',
					type: 'html',
					html: '<form id="cke_upload_image_form" action="articles/upload_image_ajax" method="post"><input type="file" name="cke_image_file" id="cke_image_file"><a href="javascript:onClickUploadImage()" title="아니" class="cke_dialog_ui_button cke_dialog_ui_button_cancel" role="button"><span class="cke_dialog_ui_button">Upload</span></a></form>'
				}
			]
		});

		// Provide the focus handler to start initial focus in "customField" field.
		dialogDefinition.onFocus = function() {
			urlField = this.getContentElement( 'info', 'txtUrl' );
		};
	}
});

function onClickUploadImage() {
	if ($('#cke_image_file').val() != "")
	{
		$('#cke_upload_image_form').ajaxForm({
			dataType : 'json',
			success: function(ret, statusText, xhr, form) {
				try {
					if (ret.err_code == 0)
					{	
						urlField.setValue(ret.image_path);
						urlField.select();
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

		$('#cke_upload_image_form').submit();
	}
}
