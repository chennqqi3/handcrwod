<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	$bc_stack = null;

	class breadcrumbHelper {
		static public function push($title) {
			global $_REQUEST, $_SERVER;

			$url = strstr($_SERVER["REQUEST_URI"], "?" , true);
			if ($url == "")
				$url = $_SERVER["REQUEST_URI"];

			if (array_key_exists("bpop", $_REQUEST)) {
				$stack = breadcrumbHelper::stack();
				$c = count($stack);
				for ($i = $c - 1; $i > 0; $i --) {
					$item = $stack[$i];
					if ($item["title"] == $title) {
						array_pop($stack);
						array_push($stack, array("title" => $title, "url" => $url));
						break;
					}
					array_pop($stack);
				}
				breadcrumbHelper::stack($stack);
				return;
			}

			if (array_key_exists("badd", $_REQUEST)) {
				$stack = breadcrumbHelper::stack();
			}
			else if (array_key_exists("bign", $_REQUEST)) {
				return;
			}
			else {
				$stack = breadcrumbHelper::home_stack();
			}

			if ($url == breadcrumbHelper::last_url())
				return;

			array_push($stack, array("title" => $title, "url" => $url));
			breadcrumbHelper::stack($stack);
		}

		static public function params() {
			global $_REQUEST;

			if (array_key_exists("bpop", $_REQUEST)) {
				return "bpop";
			}
			else if (array_key_exists("badd", $_REQUEST)) {
				return "badd";
			}
			else if (array_key_exists("bign", $_REQUEST)) {
				return "bign";
			}
		}

		static public function prev_url($url) {
			$stack = breadcrumbHelper::stack();
			$c = count($stack);
			if ($c <= 1) {
				return $url;
			}
			else {
				$item = $stack[$c - 2];
				return $item["url"];
			}
		}

		static public function last_url() {
			$stack = breadcrumbHelper::stack();
			$c = count($stack);
			$item = $stack[$c - 1];
			return $item["url"];
		}

		static public function set_home() {
			breadcrumbHelper::stack(breadcrumbHelper::home_stack());
		}

		static public function home_stack() {
			return array(array("title" => "ホーム", "url" => "/home"));
		}

		static public function stack($stack = null) {
			global $bc_stack;
			if ($stack == null) {
				$stack = $bc_stack;
				if ($stack == null)
					$stack = _session("breadcrumb");
				if ($stack == null)
					$stack = breadcrumbHelper::home_stack();

				return $stack;
			}
			else {
				//_session("breadcrumb", $stack);
				$bc_stack = $stack;
			}
		}

		static public function render() {
			$stack = breadcrumbHelper::stack();
			p("<ul class='breadcrumb'>");

			for ($i = 0; $i < count($stack); $i ++) {
				$item = $stack[$i];
				if ($i == count($stack) - 1) {
					p("<li class='active'>" . ($i == 0 ? "<i class='glyphicon glyphicon-home'></i> " : "") . $item["title"] . " </li>");
				}
				else {
					p("<li>" . ($i == 0 ? "<i class='glyphicon glyphicon-home'></i> " : "") . " <a href='" . $item['url'] . "' class='bpop'>" . $item["title"] . "</a> </li>");
				}
			}

			p("</ul>");
		}

		static public function read_save() {
			global $_REQUEST;
			$items = $_REQUEST["items"];
			
			if (count($items) == 0) {
				$stack = breadcrumbHelper::home_stack();
			}
			else {
				$stack = array();
				for ($i = 0; $i < count($items); $i ++) {
					$item = $items[$i];
					$ai = preg_split("/;/", $item);
					array_push($stack, array("title" => $ai[0], "url" => $ai[1]));
				}
			}

			_session("breadcrumb", $stack);
		}

		static public function breadcrumb_form() {
			$stack = breadcrumbHelper::stack();
			p("<form id='breadcrumb_form' action='/home/breadcrumb_ajax' method='post' style='height:0px;margin:0px;'>");

			for ($i = 0; $i < count($stack); $i ++) {
				$item = $stack[$i];
				p("<input type='hidden' name='items[]' value='" . $item["title"] . ";" . $item["url"] . "'>");
			}

			p("<input type='hidden' id='bcurl'>");
			p("</form>");
		}
	};

?>