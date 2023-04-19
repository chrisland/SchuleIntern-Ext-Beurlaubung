<?php

class getListOpen extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        $status = [1];

        $tmp_data = extBeurlaubungModelAntrag::getByStatus($status);


        $ret = [];
        foreach ($tmp_data as $item) {
            $ret[] = $item->getCollection(true);
        }

        $user = DB::getSession()->getUser();

        $freigabe = extBeurlaubungModelAntrag::getFreigabeBy($ret, $user);

        if ( DB::getSession()->isAdminOrGroupAdmin($this->extension['adminGroupName']) === true ) {
            $freigabe = true;
        }

        if ($freigabe) {
            return $ret;
        }

        return ['access' => false];

    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'GET';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
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
    public function needsSystemAuth()
    {
        return false;
    }

}

?>