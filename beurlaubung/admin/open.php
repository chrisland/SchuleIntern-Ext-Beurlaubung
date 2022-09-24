<?php

class extBeurlaubungAdminOpen extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-plug"></i> Beurlaubung - Admin Open';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

		
		$this->render([
            "tmpl" => "list",
			"scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/open/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/open/dist/js/app.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
                "apiURL" => "rest.php/beurlaubung"
			]

		]);

	}


}
