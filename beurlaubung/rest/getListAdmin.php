<?php

class getListAdmin extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {



        $status_str= (string)$request[2];
        if ($status_str == 'open') {
            $status = [1];
        } else if ($status_str == 'list') {
            $status = [2,3];
        }


        //$acl = $this->getAcl();
        if ((int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        $tmp_data = extBeurlaubungModelAntrag::getByStatus($status);

        $ret = [];
        foreach ($tmp_data as $item) {
            $ret[] = $item->getCollection(true);
        }

        return $ret;

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'GET';
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