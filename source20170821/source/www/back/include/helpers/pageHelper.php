<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class pageHelper {
		private $_props;

		public function __construct($counts, $page, $size){
			$this->counts = $counts;
			$this->page = $page;
			$this->size = $size;
			$this->pages= ceil($this->counts / $this->size);

			if ($this->page >= $this->pages && $this->page > 1)
				$this->page = $this->pages - 1;
		}

		public function __get($prop) {
			return $this->_props[$prop];
		}

		public function __set($prop, $val) {
			$this->_props[$prop] = $val;
		}

		public function start_no() {
			return $this->page * $this->size + 1;
		}

		public function display($base_url, $page_size = 10, $bpop = 'bpop') {
			?>
			<input type="hidden" name="page_no" id="pagebar_page" value="<?php p($this->page); ?>"/>
			<input type="hidden" name="page_size" id="pagebar_size" value="<?php p($this->size); ?>"/>
			<?php
			if ($this->pages <= 1)
				return;

			$curp = $this->page;
			$sp = floor($curp / $page_size) * $page_size;
			$ep = $sp + $page_size - 1;
			if ($ep >= $this->pages)
				$ep = $this->pages - 1;
			?>
			<div class="pagination text-center">
				<ul>
					<?php 
					if ($curp > 0) {
					?><li><a href="<?php p($base_url . ($curp - 1) . "/" . $this->size) ?>" class='<?php p($bpop);?>'><?php l("前へ"); ?></a></li><?php
					}
					if ($sp > 0) {
					?><li><a href="<?php p($base_url . ($sp - 1) . "/" . $this->size) ?>" class='<?php p($bpop);?>'>...</a></li><?php
					}
					for ($p = $sp; $p <= $ep; $p ++) 
					{
					?><li class="<?php p($p == $curp ? "active" : "" ) ?>">
					  <a href="<?php p($base_url . $p . "/" . $this->size) ?>" class='<?php p($bpop);?>'><?php p($p + 1) ?></a>
					</li><?php
					}
					if ($ep < $this->pages - 1) {
					?><li><a href="<?php p($base_url . ($ep + 1) . "/" . $this->size) ?>" class='<?php p($bpop);?>'>...</a></li><?php
					}
					if ($curp < $this->pages - 1) {
					?><li><a href="<?php p($base_url . ($curp + 1) . "/" . $this->size) ?>" class='<?php p($bpop);?>'><?php l("次へ"); ?></a></li><?php
					}
					?>
				</ul>
			</div>
			<?php

		}

		public function display_ajax($page_size = 10) {
			if ($this->pages <= 1)
				return array("counts" => $this->counts , "link" => null);

			$curp = $this->page;
			$sp = floor($curp / $page_size) * $page_size;
			$ep = $sp + $page_size - 1;
			if ($ep >= $this->pages)
				$ep = $this->pages - 1;

			$link = array();
			if ($curp > 0) {
				$link[] = array("page" => ($curp - 1), "label" => "Prev");
			}
			if ($sp > 0) {
				$link[] = array("page" => ($sp - 1), "label" => "...");
			}
			for ($p = $sp; $p <= $ep; $p ++) 
			{
				$link[] = array("page" => $p, "label" => $p + 1);
			}
			if ($ep < $this->pages - 1) {
				$link[] = array("page" => ($ep + 1), "label" => "...");
			}
			if ($curp < $this->pages - 1) {
				$link[] = array("page" => ($curp + 1), "label" => "Next");
			}

			return array("counts" => $this->counts , "link" => $link);
		}
	}

?>