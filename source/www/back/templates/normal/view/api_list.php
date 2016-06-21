<?php if (!$this->script_mode()) { ?>
	<h3>API一覧</h3>

	<div class="row">
		<div class="span12">
			<table class="table table-striped table-hover" width="100%">					
				<thead>
					<tr>
						<th class="td-no">#</th>
						<th>API名</th>
						<th>API URL</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach ($this->apis as $api) {
				?>
					<tr>
						<td><?php p($i + 1); ?></td>
						<td><?php p($api); ?></td>
						<td><a href="<?php p($this->apitest_url . $api); ?>"><?php p($this->api_url . $api); ?></a></th>
					</tr>
				<?php
						$i ++;
					}
				?>
				</tbody>
			</table>
			<!--/table -->
		</div>
	</div>
<?php } else { ?>
<?php } ?>