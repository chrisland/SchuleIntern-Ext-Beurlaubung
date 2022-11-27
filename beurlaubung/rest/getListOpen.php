<?php

class getListOpen extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        $status = [1];

        $tmp_data = extBeurlaubungModelAntrag::getByStatus($status);

        $user = DB::getSession()->getUser();

        if ( $user->isTeacher() ) {
            $teacherID = $user->getTeacherObject()->getID();
        }

        $ret = [];
        foreach ($tmp_data as $item) {
            $ret[] = $item->getCollection(true);
        }

        $freigabe = false;
        $freigabeSL = DB::getSettings()->getBoolean("extBeurlaubung-schulleitung-freigabe");
        if ($freigabeSL) {
            $schulleitung = schulinfo::getSchulleitungLehrerObjects();
            foreach ($schulleitung as $sl) {
                if ($sl->getUserID() == $user->getUserID()) {
                    $freigabe = true;
                }
            }
        }

        if ($freigabe !== true) {
            $freigabeKL = DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-freigabe");
            if ($freigabeKL && $teacherID ) {
                $arr = [];
                foreach ($ret as $item) {
                    $klasse = $item['user']['klasse'];
                    $leitungen = klasse::getKlassenleitungAll($klasse);
                    $ok = false;
                    foreach ($leitungen as $leitung) {
                        if ($leitung['lehrerID'] == $teacherID) {
                            $ok = true;
                        }
                    }
                    if ($ok == true) {
                        $arr[] = $item;
                    }
                }
                $ret = $arr;
                $freigabe = true;
            }
        }

        if ( $user->isAdmin() ) {
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