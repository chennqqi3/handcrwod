<?php if (!$this->script_mode()) { ?>
	<h3>APIテストページ</h3>

	<div class="row">
		<div class="span12">
			<form id="save_form" action="<?php p($this->api_url); ?>" class="form-horizontal" method="post" novalidate="novalidate">
				<div class="row">
					<div class="span12">
						<fieldset>
							<div class="control-group">
								<label class="control-label" for="api_url">API URL</label>
								<div class="controls text-detail">
									<?php p($this->api_url); ?> 
								</div>
							</div>
						</fieldset>
						<hr/>
						<?php foreach($this->api_param_names as $param_name) { ?>
						<fieldset>
							<div class="control-group">
								<label class="control-label" for="<?php p($param_name); ?>"><?php p($param_name); ?></label>
								<div class="controls">
									<?php $this->api_params->input($param_name, array("class" => "input-xxlarge")); ?> 
								</div>
							</div>
						</fieldset>
						<?php } ?>
						<hr/>
						<fieldset>
							<div class="control-group">
								<label class="control-label" for="api_url">呼び出し結果</label>
								<div class="controls">
									<pre id="api_result">
									</pre>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="navbar">
					<div class="navbar-inner">
						<div class="navbar-form pull-right">
							<button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> 呼び出し</button>
							<a href="<?php p($this->apitest_url); ?>/../" class="btn"><i class="fa fa-fw fa-times"></i> 戻る</a>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php } else { ?>
	<script type="text/javascript">
	$(function () {
		$('#save_form').ajaxForm({
			success: function(ret, statusText, xhr, form) {
				try {
					$('#api_result').text(ret);
				}
				finally {
				}
			}
		});

	});
	</script>
<?php } ?>