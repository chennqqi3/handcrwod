<?php if (!$this->script_mode()) { ?>
<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h2>パッチ履歴</h2>
	</div>
</header>

<div class="container">
	<div class="row">
		<div class="span12">
			<section id="main">
				<table class="table table-striped">
					<tr>
						<th width="90px">バージョン</th>
						<th>説明</th>
						<th width="80px">パッチ日時</th>
					</tr>
				<?php 
					foreach($this->oPatched as $p) {
				?>
					<tr>
						<td><?php $p->detail("version"); ?></td>
						<td><?php $p->nl2br("description"); ?></td>
						<td><?php $p->date("create_time"); ?></td>
					</tr>
				<?php 
					}
				?>
				</table>
			</section>
		</div>
	</div>
</div> <!-- /container -->
<?php } else { ?>
<script type="text/javascript">
</script>
<?php } ?>