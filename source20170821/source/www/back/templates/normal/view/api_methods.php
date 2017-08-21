<?php if (!$this->script_mode()) { ?>
	<h3>API「<?php p($this->api_name); ?>」のメソッド一覧</h3>

	<div class="row">
		<div class="span12">
			<table class="table table-striped table-hover" width="100%">					
				<thead>
					<tr>
						<th class="td-no">#</th>
						<th>メソッド名</th>
						<th>メソッドURL</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$i = 0;
					foreach ($this->api_methods as $method) {
				?>
					<tr>
						<td><?php p($i + 1); ?></td>
						<td><?php p($method); ?></td>
						<td><a href="<?php p($this->apitest_url . $method); ?>"><?php p($this->api_url . $method); ?></a></th>
					</tr>
				<?php
						$i ++;
					}
				?>
				</tbody>
			</table>
			<!--/table -->
			<div class="navbar">
				<div class="navbar-inner">
					<div class="navbar-form pull-right">
						<a href="<?php p($this->apitest_url); ?>../" class="btn"><i class="fa fa-fw fa-times"></i> 戻る</a>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } else { ?>
<?php } ?>