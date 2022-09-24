<?php

class extBeurlaubungDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-sun"></i> Beurlaubung';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }


    public function execute()
    {

        //$_request = $this->getRequest();
        //print_r($_request);

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1) {
            new errorPage('Kein Zugriff');
        }

        //print_r( $acl );

        //$user = DB::getSession()->getUser();


        if (DB::getSession()->isEltern()) {
            $mySchueler = [];
            $mySchueler_temp = DB::getSession()->getElternObject()->getMySchueler();
            foreach ($mySchueler_temp as $schueler) {
                $mySchueler[] = $schueler->getCollection(true, true);
            }
        }

        if (DB::getSession()->isTeacher()) {
            $isSchulleitung = DB::getSession()->getTeacherObject()->isSchulleitung();
        }

        $maxStunden = (int)DB::getSettings()->getValue("stundenplan-anzahlstunden");
        if (!$maxStunden) {
            $maxStunden = 6;
        }
        $stundenVormittag = 6;
        $stundenNachmittag = $maxStunden - $stundenVormittag;

        //$settings = $this->getSettings();


            //DB::getSettings()->getBoolean("beurlaubung-volljaehrige-schueler")

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/beurlaubung",
                "acl" => $acl['rights'],
                "settings" => $this->getSettings(),
                "maxStunden" => (int)$maxStunden,
                "stundenVormittag" => (int)$stundenVormittag,
                "stundenNachmittag" => (int)$stundenNachmittag,
                "mySchueler" => $mySchueler
            ]
        ]);


    }


}
