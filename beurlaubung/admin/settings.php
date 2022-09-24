<?php

class extBeurlaubungAdminSettings extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-plug"></i> Beurlaubung - Admin Einstellungen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

		
		$this->render([
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
				PATH_COMPONENTS.'system/adminSettings/dist/main.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"settings" => $this->getSettings()
			]

		]);

	}


    public static function getSettingsDescription() {

        $userGroups = usergroup::getAllOwnGroups();

        $options = [];

        for($i = 0; $i < sizeof($userGroups); $i++) {
            $options[] = [
                'value' => md5($userGroups[$i]->getName()),
                'name' => $userGroups[$i]->getName()
            ];
        }

        $settings =  [
            [
                'name' => "extBeurlaubung-form-info-required",
                'typ' => 'BOOLEAN',
                'title' => "Neuer Antrag - Begründung als Pfichtfeld",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-volljaehrige-schueler",
                'typ' => 'BOOLEAN',
                'title' => "Beurlaubung durch volljährige Schüler aktivieren",
                'desc' => "Ist diese Einstellung aktiv, können sich volljährige Schüler selbst krank melden."
            ],
            [
                'name' => "extBeurlaubung-schueler",
                'typ' => 'BOOLEAN',
                'title' => "Beurlaubung durch Schüler aktivieren",
                'desc' => "Ist diese Einstellung aktiv, können sich Schüler selbst krank melden. (Auch die unter 18 Jahren!)"
            ],
            [
                'name' => "extBeurlaubung-lnw-sperre",
                'typ' => 'BOOLEAN',
                'title' => "An Tagen mit angekündigtem Leistungsnachweis keine Beurlaubung erlauben",
                'desc' => "Wenn diese Option aktiv ist, dann kann für Tage, an denen ein Leistungsnachwei angekündigt ist, keine Beurlaubung eingereicht werden."
            ],
            [
                'name' => "extBeurlaubung-termin-sperre",
                'typ' => 'BOOLEAN',
                'title' => "An Tagen mit Klassenterminen keine Beurlaubung erlauben",
                'desc' => "Wenn diese Option aktiv ist, dann kann für Tage, an denen ein Klassentermin angekündigt ist, keine Beurlaubung eingereicht werden."
            ],
            [
                'name' => "extBeurlaubung-klassenleitung-freigabe",
                'typ' => 'BOOLEAN',
                'title' => "Beurlaubungen müssen von der Klassenleitung freigegeben werden?",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-klassenleitung-nachricht",
                'typ' => 'BOOLEAN',
                'title' => "Klassenleitung bei neuen Anträgen per Nachricht informieren?",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-schulleitung-freigabe",
                'typ' => 'BOOLEAN',
                'title' => "Beurlaubungen müssen von der Schulleitung freigegeben werden?",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-schulleitung-nachricht",
                'typ' => 'BOOLEAN',
                'title' => "Schulleitung bei neuen Anträgen per Nachricht informieren?",
                'desc' => ""
            ],

            [
                'name' => "extBeurlaubung-ausdruck-erforderlich",
                'typ' => 'BOOLEAN',
                'title' => "Muss nach dem Beurlaubungsantrag ein schriftlicher Antrag ausgedruckt und eingereicht werden?",
                'desc' => ""
            ],

            [
                'name' => "extBeurlaubung-genehmigung-benachrichtigung-gruppen",
                'typ' => 'SELECT',
                'options' => $options,
                'multiple' => true,
                'title' => "Folgende Gruppen können optional bei der Genehmigung über eine Beurlaubung informiert werden.",
                'desc' => "Sind hier Gruppen ausgewählt, kann der Schulleitung bei der Eingabe einer Genehmigung option die Infos zur Beurlaubung an diese Gruppen automatisch senden. (z.B. zur Information der Nachmittagsbetreuung)"
            ],
        ];
        return $settings;

    }

    public function taskSave($postData) {

        $request = $this->getRequest();
        if ($request['page'] && $postData['settings']) {
            foreach($postData['settings'] as $item) {

                echo "INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" .DB::getDB()->escapeString($item['name']) . "',
				'" . DB::getDB()->escapeString(($item['value'])) . "'
				,'" . DB::getDB()->escapeString(($request['page'])) . "')
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item['value']) . "'";

                DB::getDB()->query("INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" .DB::getDB()->escapeString($item['name']) . "',
				'" . DB::getDB()->escapeString(($item['value'])) . "'
				,'" . DB::getDB()->escapeString(($request['page'])) . "')
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item['value']) . "'");
            }
            echo json_encode(['done' => 'true']);
        } else {
            echo json_encode(['error' => 'Fehler beim Speichern!']);
        }
    }



}
