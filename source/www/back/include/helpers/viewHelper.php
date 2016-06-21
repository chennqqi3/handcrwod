<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class viewHelper {		
		private $_model;

		function __construct($model) {
			$this->_model = $model;
		}

		static public function toAttrs($attr, $other_class=null) {
			if ($attr == null)
				$attr = array();

			if (!isset($attr["class"]) || $attr["class"] == null) 
				$attr["class"] = "";
			$attr["class"] .= " ";
			if ($other_class != null)
				$attr["class"] .= " " . $other_class;

			foreach($attr as $key => $value)
			{
				print $key . "=\"" . $value . "\" ";
			}
		}

		public function input($prop, $attr=null) {
			?><input type="text" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($this->_model->$prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
		}

		public function textarea($prop, $rows, $attr=null) {
			?><textarea id="<?php p($prop); ?>" name="<?php p($prop); ?>" rows="<?php p($rows); ?>" <?php viewHelper::toAttrs($attr)?>><?php p($this->_model->$prop); ?></textarea><?php
		}

		public function password($prop, $attr=null) {
			?><input type="password" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($this->_model->$prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
		}

		public function file($prop, $attr=null) {
			?><input type="file" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($this->_model->$prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
		}

		public function hidden($prop, $attr=null) {
			?><input type="hidden" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($this->_model->$prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
		}

		public function orderLabel($field, $label) {
			$ii = "";
			if ($this->_model->sort_field == $field)
			{
				$ii = " <i class='fa fa-chevron-" . ($this->_model->sort_order == "ASC" ? "up" : "down") ."'></i>";
			}
			?><a href="javascript:;" data-sort="<?php p($field); ?>"><?php p($label); ?><?php p($ii);?></a><?php
		}

		public function select_code($prop, $code, $default=null, $attr=null) {
			global $g_codes;
			$codes = $g_codes[$code];

			?><select id="<?php p($prop); ?>" name="<?php p($prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
			if ($default != null && $default != "")
			{
				?><option value="" <?php p($this->_model->$prop == null ? "selected" : "") ?>><?php p($default) ?></option><?php
			}
			foreach($codes as $key => $label) {
				$key = $key . "";
				?><option value="<?php p($key); ?>" <?php p($this->_model->$prop == $key ? "selected" : "") ?>><?php p($label) ?></option><?php
			}
			?></select><?php
		}

		public function select_dayofweek($prop, $default=null, $attr=null) {
			$this->select_code($prop, CODE_DAYOFWEEK, $default, $attr);
		}

		public function select_dayofmonth($prop, $default=null, $attr=null) {
			global $g_codes;
			$codes = $g_codes[$code];

			?><select id="<?php p($prop); ?>" name="<?php p($prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
			if ($default != null && $default != "")
			{
				?><option value="" <?php p($this->_model->$prop == null ? "selected" : "") ?>><?php p($default) ?></option><?php
			}
			for ($m = 1; $m <= 31; $m ++) {
				$m = $m . "";
				?><option value="<?php p($m); ?>" <?php p($this->_model->$prop == $m ? "selected" : "") ?>><?php p($m) ?></option><?php
			}
			?></select><label><?php l("日"); ?></label><?php
		}

		public function select_year($prop, $min, $max, $default=null, $attr=null) {
			global $g_codes;
			$codes = $g_codes[$code];

			?><select id="<?php p($prop); ?>" name="<?php p($prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
			if ($default != null && $default != "")
			{
				?><option value="" <?php p($this->_model->$prop == null ? "selected" : "") ?>><?php p($default) ?></option><?php
			}
			for ($y = $min; $y <= $max; $y ++) {
				$y = $y . "";
				?><option value="<?php p($y); ?>" <?php p($this->_model->$prop == $y ? "selected" : "") ?>><?php p($y) ?></option><?php
			}
			?></select><?php
		}

		public function select_month($prop, $default=null, $attr=null) {
			global $g_codes;
			$codes = $g_codes[$code];

			?><select id="<?php p($prop); ?>" name="<?php p($prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
			if ($default != null && $default != "")
			{
				?><option value="" <?php p($this->_model->$prop == null ? "selected" : "") ?>><?php p($default) ?></option><?php
			}
			for ($m = 1; $m <= 12; $m ++) {
				$m = $m . "";
				?><option value="<?php p($m); ?>" <?php p($this->_model->$prop == $m ? "selected" : "") ?>><?php p($m) ?></option><?php
			}
			?></select><?php
		}

		public function select_model($prop, $model, $val_field, $text_field, $default=null, $sqloption=null, $attr=null) {
			?><select id="<?php p($prop); ?>" name="<?php p($prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
			if ($default != null && $default != "")
			{
				?><option value="" <?php p($this->_model->$prop == null ? "selected" : "") ?>><?php p($default) ?></option><?php
			}	
			$where = "";
			if ($sqloption != null) {
				if ($sqloption["order"] != null)
					$order = $sqloption["order"];
				else
					$order = "create_time ASC";
				if ($sqloption["where"] != null)
					$where = $sqloption["where"];
			}
			$err = $model->select($where, array("order" => $order));
			while ($err == ERR_OK)
			{
				$key = $key . "";
				?><option value="<?php p($model->$val_field); ?>" <?php p($this->_model->$prop === $model->$val_field ? "selected" : "") ?>><?php p($model->$text_field) ?></option><?php
				$err = $model->fetch();
			}
			?></select><?php
		}

		public function select_times($prop, $prop_start, $prop_end, $attr=null) {
			if ($this->_model->$prop_start == "")
				$start = "";
			else {
				$start = _time(strtotime($this->_model->$prop_start));
				$times = $start;
			}
			if ($this->_model->$prop_end == "")
				$end = "";
			else {
				$end = _time(strtotime($this->_model->$prop_end));
				$times .= " ~ " . $end;
			}
			?>
			<input type="text" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($times); ?>" <?php viewHelper::toAttrs($attr); ?> readonly>
			<input type="hidden" id="<?php p($prop_start); ?>" name="<?php p($prop_start); ?>" value="<?php p($start); ?>">
			<input type="hidden" id="<?php p($prop_end); ?>" name="<?php p($prop_end); ?>" value="<?php p($end); ?>"> 

			<?php
		}

		public function select_utype($prop, $default=null, $attr=null) {
			global $g_codes;
			$codes = $g_codes[CODE_UTYPE];
			$utype = _utype();

			?><select id="<?php p($prop); ?>" name="<?php p($prop); ?>" <?php viewHelper::toAttrs($attr, "input-small")?>><?php
			if ($default != null && $default != "")
			{
				?><option value="" <?php p($this->_model->$prop == null ? "selected" : "") ?>><?php p($default) ?></option><?php
			}
			foreach($codes as $key => $label) {
				if ($utype == UTYPE_ADMIN || $key > $utype) {
					$key = $key . "";
					?><option value="<?php p($key); ?>" <?php p($this->_model->$prop == $key ? "selected" : "") ?>><?php p($label) ?></option><?php
				}
			}
			?></select><?php
		}

		public function select_user($prop, $default=null, $attr=null) {
			?><select id="<?php p($prop); ?>" name="<?php p($prop); ?>" <?php viewHelper::toAttrs($attr, "input-small")?>><?php
			if ($default != null && $default != "")
			{
				?><option value="" <?php p($this->_model->$prop == null ? "selected" : "") ?>><?php p($default) ?></option><?php
			}	
			$user = new user;
			$where = "";
			$order = "user_name ASC";
			$err = $user->select($where, array("order" => $order));
			while ($err == ERR_OK)
			{
				$key = $key . "";
				?><option value="<?php p($user->user_id); ?>" <?php p($this->_model->$prop === $user->user_id ? "selected" : "") ?>><?php p($user->user_name) ?></option><?php
				$err = $user->fetch();
			}
			?></select><?php
		}

		public function input_user($prop_id, $prop_name, $readonly=false) {
			$this->hidden($prop_id);
			$this->input($prop_name, array("class" => "input", "readonly" => "readonly"));
			if (!$readonly) {
				?>&nbsp;<a href="users/select_user" class="btn select-user fancybox" fancy-width="900" fancy-height="600"><div>…</div></a><?php
			}
		}

		public function radio($prop, $code, $attr=null) {
			global $g_codes;
			$codes = $g_codes[$code];

			foreach($codes as $key => $label) {
				$id = $prop . "_" . $key;
				?><label class="radio inline" for="<?php p($id); ?>"><input type="radio" id="<?php p($id); ?>" name="<?php p($prop); ?>" value="<?php p($key); ?>" <?php p($this->_model->$prop == $key ? "checked=true" : "") ?> <?php viewHelper::toAttrs($attr)?>><?php p($label) ?></label><?php
			}
		}

		public function radio_single($prop, $label, $key, $attr=null) {
			$id = $prop . "_" . $key;
			?><label class="radio" for="<?php p($id); ?>"> <input type="radio" id="<?php p($id); ?>" name="<?php p($prop); ?>" value="<?php p($key); ?>" <?php p($this->_model->$prop == $key ? "checked=true" : "") ?> <?php viewHelper::toAttrs($attr)?>><?php p($label) ?></label><?php
		}

		public function radio_bookmark_level($prop, $attr=null) {
			for ($key = 0; $key < 5; $key ++) {
				$id = $prop . "_" . $key;
				?><label class="radio inline" for="<?php p($id); ?>"><input type="radio" id="<?php p($id); ?>" name="<?php p($prop); ?>" value="<?php p($key); ?>" <?php p($this->_model->$prop == $key ? "checked=true" : "") ?> <?php viewHelper::toAttrs($attr)?>><i class="fa fa-bookmark bookmark-level<?php p($key); ?>"></i></label><?php
			}
		}

		public function checkbox($prop, $code, $attr=null) {
			global $g_codes;
			$codes = $g_codes[$code];

			$id = $prop . "_null";
			?><input type="checkbox" class="checkbox"  id="<?php p($id); ?>" name="<?php p($prop); ?>[]" value="0" checked style="display:none;"><?php

			foreach($codes as $key => $label) {
				$id = $prop . "_" . $key;
				?><label class="checkbox inline" for="<?php p($id); ?>"><input type="checkbox" class="checkbox"  id="<?php p($id); ?>" name="<?php p($prop); ?>[]" value="<?php p($key); ?>" <?php p($this->_model->$prop & $key ? "checked=true" : "") ?> <?php viewHelper::toAttrs($attr)?>><?php p($label) ?></label><?php
			}
		}

		public function checkbox_single($prop, $label, $attr=null) {
			?><label class="checkbox inline" for="<?php p($prop); ?>"> <input type="checkbox" class="checkbox"  id="<?php p($prop); ?>" name="<?php p($prop); ?>[]" value="1" <?php p($this->_model->$prop == 1 ? "checked=true" : "") ?> <?php viewHelper::toAttrs($attr)?>><?php p($label) ?></label><input type="checkbox" id="<?php p($prop); ?>_0" name="<?php p($prop); ?>[]" value="0" checked style="display:none"><?php
		}

		public function checkbox_join($prop, $code, $attr=null) {
			global $g_codes;
			$codes = $g_codes[$code];
			$vals = preg_split("/,/", $this->_model->$prop);

			foreach($codes as $key => $label) {
				$id = $prop . "_" . $key;
				$checked = "";
				foreach($vals as $val) {
					if ($key == $val) {
						$checked = "checked=true";
						break;
					}
				}
				?><label class="checkbox inline" for="<?php p($id); ?>"><input type="checkbox" class="checkbox"  id="<?php p($id); ?>" name="<?php p($prop); ?>[]" value="<?php p($key); ?>" <?php p($checked) ?> <?php viewHelper::toAttrs($attr)?>><?php p($label) ?></label><?php
			}
		}

		public function detail($prop, $format="%s") {
			$s = sprintf($format, $this->_model->$prop);
			if($s == "")
				$s = "&nbsp;";
			p($s);
		}

		public function installed($prop) {
			if ($this->_model->$prop)
				p("インストール済み");
			else
				p("未インストール");

			$this->input($prop, array("class" => "input-null"));
		}

		public function nl2br($prop, $format="%s") {
			$s = sprintf($format, $this->_model->$prop);
			if($s == "")
				$s = "&nbsp;";
			p(nl2br($s));
		}

		public function detail_html($prop) {
			p($this->_model->$prop);
		}

		public function number($prop) {
			$s = number_format($this->_model->$prop);
			if($s == "")
				$s = "&nbsp;";
			p($s);
		}

		public function currency($prop) {
			$s = _currency($this->_model->$prop);
			if($s == "")
				$s = "&nbsp;";
			p($s);
		}

		public function paragraph($prop) {
			if ($this->_model->$prop == "")
				p("&nbsp;");
			else
				p(_str2paragraph($this->_model->$prop));
		}

		public function summary($prop) {
			if ($this->_model->$prop == "")
				p("&nbsp;");
			else
				p(_str2firstparagraph($this->_model->$prop));
		}

		public function article_date($prop) {
			if ($this->_model->$prop == null)
				$s = "&nbsp;";
			else {
				$dt = strtotime($this->_model->$prop);
				if($dt == "") {
					$s = "&nbsp;";
				}
				else {
					$y = _date($dt, "Y"); $m = _date($dt, "m"); $d = _date($dt, "d");
					$s = "<div class='year'>" . $y  . "</div><span class='month'>" . $m . "</span>/<span class='day'>" . $d .  "</span>";
				}
			}
			p($s);
		}

		public function dateinput($prop, $attr=null) {
			if ($this->_model->$prop == null)
				$s = "";
			else 
				$s = _date(strtotime($this->_model->$prop));
			?><input type="text" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($s); ?>" <?php viewHelper::toAttrs($attr, "input-small")?>><?php
		}

		public function datebox($prop, $attr=null) {
			if ($this->_model->$prop == null)
				$s = "";
			else 
				$s = _date(strtotime($this->_model->$prop));
			?><input type="text" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($s); ?>" <?php viewHelper::toAttrs($attr, "datepicker input-small")?> data-dateformat="yy-mm-dd"><?php
			?><label class="fa fa-calendar mark-calendar"></label><?php
		}

		public function timebox($prop, $attr=null) {
			if ($this->_model->$prop == null)
				$s = "";
			else 
				$s = _time(strtotime($this->_model->$prop));
			?><input type="text" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($s); ?>" <?php viewHelper::toAttrs($attr, "input-xmini")?> data-mask="99:99" data-mask-placeholder= "-"><?php
			?><label class="fa fa-clock mark-calendar"></label><?php
		}

		public function date($prop) {
			if ($this->_model->$prop == null)
				$s = "&nbsp;";
			else {
				$s = _date(strtotime($this->_model->$prop));
				if($s == "")
					$s = "&nbsp;";
			}
			p($s);
		}

		public function datetime($prop) {
			if ($this->_model->$prop == null)
				$s = "&nbsp;";
			else {
				$s = _datetime(strtotime($this->_model->$prop));
				if($s == "")
					$s = "&nbsp;";
			}
			p($s);
		}

		public function time($prop) {
			if ($this->_model->$prop == null)
				$s = "&nbsp;";
			else {
				$s = _time(strtotime($this->_model->$prop));
				if($s == "")
					$s = "&nbsp;";
			}
			p($s);
		}

		public function times($prop1, $prop2) {
			if ($this->_model->$prop1 == null || $this->_model->$prop2 == null)
				$s = "&nbsp;";
			else {
				$s = strtotime($this->_model->$prop2) - strtotime($this->_model->$prop1);
				if ($s <= 0) 
					$s = "";
				$s = sprintf("%02d:%02d", $s / 3600, ($s % 3600) / 60);
				if($s == "")
					$s = "&nbsp;";
			}
			p($s);
		}

		public function detail_code($prop, $code) {
			global $g_codes;
			$codes = $g_codes[$code];
			$s = $codes[$this->_model->$prop];
			if($s == "")
				$s = "&nbsp;";
			p($s);
		}

		public function detail_code_multi($prop, $code, $active_code = null) {
			global $g_codes;
			$codes = $g_codes[$code];

			$s = "";
			foreach($codes as $key => $label) {
				if ($this->_model->$prop & $key) 
				{
					if ($s != "") $s.=", ";
					if ($key == $active_code) {
						$s .= "<span class='label label-important'>" . $label . "</span>";
					}
					else {
						$s .= $label;
					}
				}
			}
			p($s);
		}

		public function detail_code_multi_join($prop, $code, $active_code = null) {
			global $g_codes;
			$codes = $g_codes[$code];

			$s = "";
			$vals = preg_split("/,/", $this->_model->$prop);
			foreach($codes as $key => $label) {
				foreach($vals as $val) 
				{
					if ($val == $key) {
						if ($s != "") $s.=", ";
						if ($key == $active_code) {
							$s .= "<span class='label label-important'>" . $label . "</span>";
						}
						else {
							$s .= $label;
						}
					}
				}
			}
			p($s);
		}

		public function code($prop, $code) {
			global $g_codes;
			$codes = $g_codes[$code];
			p($codes[$this->_model->$prop]);
		}

		public function autobox($prop, $attr=null) {
			if ($attr["class"] == null)
				$attr["class"] = "input-medium";
			$attr["class"] .= " auto-complete";

			?><input type="text" id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($this->_model->$prop); ?>" <?php viewHelper::toAttrs($attr)?>><?php
		}

		public function tags($prop) {
			$tags = $this->_model->$prop;

			foreach($tags as $tag)
			{
				?><a href="articles/tag/<?php p($tag);?>" class="label label-info"><i class="fa fa-tag"></i> <?php p($tag);?></a> <?php
			}
		}

		public function sitetags($prop) {
			$tags = $this->_model->$prop;

			foreach($tags as $tag)
			{
				?><a href="sites/tag/<?php p($tag);?>" class="label label-info"><i class="fa fa-tag"></i> <?php p($tag);?></a> <?php
			}
		}

		public function attaches($prop) {
			$attaches = $this->_model->$prop;

			if ($attaches != "") {			
				$attaches = preg_split("/;/", $attaches);
				foreach($attaches as $attach)
				{
					$pf = preg_split("/:/", $attach);
					$path = $pf[0]; $file_name= $pf[1];
					?><a href="common/down_attach/<?php p($path);?>/<?php p($file_name);?>" class="label label-success"><i class="fa fa-download"></i> <?php p($file_name);?></a> <?php
				}
			}
		}

		public function rating($prop) {
			$article_id = $this->_model->article_id;
			for($r = 5; $r > 0; $r --)
			{
				$rating_name = "rating_" . $article_id;
				$rating_id = $rating_name . "_" . $r;
				?><input type="radio" name="<?php p($rating_name);?>" id="<?php p($rating_id); ?>" value="<?php p($r); ?>" article_id="<?php p($article_id); ?>" <?php if($this->_model->rating == $r) p("checked"); ?>><?php
				?><label for="<?php p($rating_id); ?>"><i class="fa fa-star"></i></label><?php
			}
		}

		public function forum_rating($prop) {
			$forum_id = $this->_model->forum_id;
			for($r = 5; $r > 0; $r --)
			{
				$rating_name = "rating_" . $forum_id;
				$rating_id = $rating_name . "_" . $r;
				?><input type="radio" name="<?php p($rating_name);?>" id="<?php p($rating_id); ?>" value="<?php p($r); ?>" forum_id="<?php p($forum_id); ?>" <?php if($this->_model->rating == $r) p("checked"); ?>><?php
				?><label for="<?php p($rating_id); ?>"><i class="fa fa-star"></i></label><?php
			}
		}

		public function qa_rating($prop) {
			$qa_id = $this->_model->qa_id;
			for($r = 5; $r > 0; $r --)
			{
				$rating_name = "rating_" . $qa_id;
				$rating_id = $rating_name . "_" . $r;
				?><input type="radio" name="<?php p($rating_name);?>" id="<?php p($rating_id); ?>" value="<?php p($r); ?>" qa_id="<?php p($qa_id); ?>" <?php if($this->_model->rating == $r) p("checked"); ?>><?php
				?><label for="<?php p($rating_id); ?>"><i class="fa fa-star"></i></label><?php
			}
		}

		public function siterating($prop) {
			$site_id = $this->_model->site_id;
			for($r = 5; $r > 0; $r --)
			{
				$rating_name = "rating_" . $site_id;
				$rating_id = $rating_name . "_" . $r;
				?><input type="radio" name="<?php p($rating_name);?>" id="<?php p($rating_id); ?>" value="<?php p($r); ?>" site_id="<?php p($site_id); ?>" <?php if($this->_model->rating == $r) p("checked"); ?>><?php
				?><label for="<?php p($rating_id); ?>"><i class="fa fa-star"></i></label><?php
			}
		}

		public function input_tags($prop, $attr=null) {
			if (is_array($this->_model->$prop))
				$tags = join(',', $this->_model->$prop);
			?><input type="text" <?php viewHelper::toAttrs($attr, "tagsinput")?> id="<?php p($prop); ?>" name="<?php p($prop); ?>" value="<?php p($tags); ?>" data-role="tagsinput"><?php
		}

		public function photo_url($prop) {
			p(_photo_url($this->_model->$prop, $this->_model->ext));
		}

		public function thumb_url($prop) {
			p(_photo_url($this->_model->$prop, "png"));
		}

		public function detail_utype($prop) {
			$utype = $this->_model->$prop;
			switch($utype) {
				case UTYPE_ADMIN:
					$label = "label-important";
					break;
				case UTYPE_USER:
					$label = "label-warning";
					break;
			}
			?><span class="label <?php p($label); ?>"><?php $this->detail_code($prop, CODE_UTYPE);?></span><?php
		}

		public function detail_atcstate($prop) {
			$utype = $this->_model->$prop;
			switch($utype) {
				case ATCSTATE_DRAFT:
					$label = "label-important";
					break;
				case ATCSTATE_CONTRIBUTED:
					$label = "label-warning";
					break;
				case ATCSTATE_PUBLISHED:
					$label = "label-success";
					break;
				case ATCSTATE_REJECTED:
					$label = "label-info";
					break;
			}
			?><span class="label <?php p($label); ?>"><?php $this->detail_code($prop, CODE_ATCSTATE);?></span><?php
		}

		public function detail_frmstate($prop) {
			$utype = $this->_model->$prop;
			switch($utype) {
				case FRMSTATE_DRAFT:
					$label = "label-important";
					break;
				case FRMSTATE_CONTRIBUTED:
					$label = "label-warning";
					break;
				case FRMSTATE_PUBLISHED:
					$label = "label-success";
					break;
				case FRMSTATE_REJECTED:
					$label = "label-info";
					break;
				case FRMSTATE_FINISHED:
					$label = "label-inverse";
					break;
			}
			?><span class="label <?php p($label); ?>"><?php $this->detail_code($prop, CODE_FRMSTATE);?></span><?php
		}

		public function detail_qastate($prop) {
			$utype = $this->_model->$prop;
			switch($utype) {
				case QASTATE_DRAFT:
					$label = "label-important";
					break;
				case QASTATE_CONTRIBUTED:
					$label = "label-warning";
					break;
				case QASTATE_PUBLISHED:
					$label = "label-success";
					break;
				case QASTATE_REJECTED:
					$label = "label-info";
					break;
				case QASTATE_SOLVED:
					$label = "label-inverse";
					break;
			}
			?><span class="label <?php p($label); ?>"><?php $this->detail_code($prop, CODE_QASTATE);?></span><?php
		}

		public function detail_sex($prop) {
			$sex = $this->_model->$prop;
			switch($sex) {
				case SEX_MAN:
					$label = "label-important";
					$icon = "fa fa-male";
					break;
				case SEX_WOMAN:
					$label = "label-warning";
					$icon = "fa fa-female";
					break;
			}
			?><i class="<?php p($icon); ?>"></i> <?php $this->detail_code($prop, CODE_SEX);?><?php
		}

		public function detail_atccat($prop) {
			p(str_repeat(" - ", $this->_model->depth - 1));
			$this->detail($prop);
		}

		public function detail_frmcat($prop) {
			p(str_repeat(" - ", $this->_model->depth - 1));
			$this->detail($prop);
		}

		public function editor($prop, $attr=null) {
			if ($editor_type == null)
				$editor_type = _editor_type();
			?><textarea id="<?php p($prop); ?>" name="<?php p($prop); ?>" <?php viewHelper::toAttrs($attr, $editor_type == EDITORTYPE_INLINE ? "cke_textarea" : "editor_textarea"); ?>  rows="<?php p($editor_type == EDITORTYPE_INLINE ? "1" : "15"); ?>"><?php
			p(htmlentities($this->_model->$prop, ENT_QUOTES));
			?></textarea><?php
		}

		public function editor_js($prop, $height = 70, $editor_type = null) {
			if ($editor_type == null)
				$editor_type = _editor_type();
			if ($editor_type == EDITORTYPE_INLINE) { ?>
			CKEDITOR.inline(document.getElementById('<?php p($prop); ?>'), { 
				height: <?php p($height); ?>,
				font_names: 'MS Gothic',
				toolbar: [
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
					{ name: 'styles', items: [ 'Styles', 'Format' ] },
					{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
					{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
					{ name: 'editing', groups: [ 'find' ], items: [ 'Find', 'Replace' ] },
					{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
					{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
					{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] }
				]
			});
			<?php
			}
		}
	}

?>