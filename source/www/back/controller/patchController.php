<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class PatchController extends controller {
		public function __construct(){
			$this->_page_id = "patch";
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_NONE);
		}

		public function index() {
			$patch = new patch;
			$this->must_patches = $patch->patch_info();
			return "none/patch_index";
		}

		public function patch_ajax() {
			$this->start();

			$patch = new patch;
			$err = $patch->patch();

			$this->finish(null, $err);
		}
	}
?>