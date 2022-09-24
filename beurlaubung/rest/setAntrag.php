<?php

class setAntrag extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $info = $input['info'];
        if ( DB::getSettings()->getBoolean("extBeurlaubung-form-info-required") && !$info ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Info'
            ];
        }

        $schueler = (int)$input['schueler'];
        if ( !$schueler ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Schueler'
            ];
        }

        $date = explode(',', $input['date']);
        if ( !$date || !$date[0] ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: date'
            ];
        }


        $stunden = $input['stunden'];
        if ( !$stunden ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Stunden'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        if ( extBeurlaubungModelAntrag::setAntrag($userID, $schueler, $date, $stunden, $info) ) {
            return [
                'success' => true
            ];
        }

        return [
            'error' => true,
            'msg' => 'Error'
        ];

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'POST';
	}


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth() {
        return true;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }
    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth() {
        return false;
    }

}	

?>