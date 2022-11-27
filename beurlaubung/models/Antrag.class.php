<?php

/**
 *
 */
class extBeurlaubungModelAntrag
{


    /**
     * @var data []
     */
    private $data = [];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->setData($data);
    }

    /**
     * @return boolean
     */
    public static function isVisible()
    {

        return true;
    }


    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Getter
     */
    public function getID()
    {
        return $this->data['id'];
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getCreatedTime()
    {
        return $this->data['createdTime'];
    }

    public function getCreatedUserID()
    {
        return $this->data['createdUserID'];
    }

    public function getUserID()
    {
        return $this->data['userID'];
    }

    public function getDatumStart()
    {
        return $this->data['datumStart'];
    }

    public function getDatumEnde()
    {
        return $this->data['datumEnde'];
    }

    public function getStunden()
    {
        return $this->data['stunden'];
    }

    public function getInfo()
    {
        return $this->data['info'];
    }
    public function getDoneInfo()
    {
        return $this->data['doneInfo'];
    }
    public function getDoneDate()
    {
        return $this->data['doneDate'];
    }
    public function getDoneUser()
    {
        return $this->data['doneUser'];
    }

    public function getDoneKL()
    {
        return $this->data['doneKL'];
    }

    public function getDoneKLDate()
    {
        return $this->data['doneKLDate'];
    }

    public function getDoneKLInfo()
    {
        return $this->data['doneKLInfo'];
    }

    public function getDoneSL()
    {
        return $this->data['doneSL'];
    }

    public function getDoneSLDate()
    {
        return $this->data['doneSLDate'];
    }

    public function getDoneSLInfo()
    {
        return $this->data['doneSLInfo'];
    }


    public function getCollection($full = false)
    {

        $dateStart = DateTime::createFromFormat('Y-m-d',$this->getDatumStart());
        $dateEnde = DateTime::createFromFormat('Y-m-d',$this->getDatumEnde());

        $doneDate = DateTime::createFromFormat('Y-m-d H:i:s',$this->getDoneDate());

        $collection = [
            "id" => $this->getID(),
            "status" => $this->getStatus(),
            "createdTime" => date('d.m.Y', $this->getCreatedTime()),
            "createdUserID" => $this->getCreatedUserID(),
            "userID" => $this->getUserID(),
            "datumStart" => $dateStart->format('d.m.Y' ),
            "datumEnde" => $dateEnde->format('d.m.Y' ),
            "stunden" => $this->getStunden(),
            "info" => $this->getInfo(),
            "doneInfo" => $this->getDoneInfo() ? $this->getDoneInfo() : '',
            "doneUser" => $this->getDoneuser(),
            "doneDate" => $doneDate ? $doneDate->format('d.m. H:i') : 0 ,
            "doneKL" => $this->getDoneKL(),
            "doneKLDate" => $this->getDoneKLDate(),
            "doneKLInfo" => $this->getDoneKLInfo(),
            "doneSL" => $this->getDoneSL(),
            "doneSLDate" => $this->getDoneSLDate(),
            "doneSLInfo" => $this->getDoneSLInfo(),
        ];

        if ($full) {
            if ( $this->getUserID() ) {
                $temp_user_1 = user::getUserByID($this->getUserID());
                if ($temp_user_1) {
                    $collection['user'] = $temp_user_1->getCollection(true);
                    $collection['username'] = $collection['user']['name'];
                }

            }
            if ( $this->getDoneuser() ) {
                $temp_user_2 = user::getUserByID($this->getDoneuser());
                if ($temp_user_2) {
                    $collection['doneUserUser'] = $temp_user_2->getCollection(true);
                }
            }
        }


        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getByStatus($status = [1])
    {
        if (!$status || !is_array($status)) {
            return false;
        }
        $where = '';
        foreach ($status as $s) {
            if ($where != '') { $where .= " OR "; }
            $where .= " `status` = " . (int)$s;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_beurlaubung_antrag WHERE " .$where." ORDER BY createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;

    }


    /**
     * @return Array[]
     */
    public static function getByUserID($userID = false)
    {
        if (!$userID) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_beurlaubung_antrag WHERE `createdUserID` = " . (int)$userID ." OR `userID` = " . (int)$userID );
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;

    }


    /**
     * @return Array[]
     */
    public static function setDone($id = false, $status = false, $info = false, $userID = false, $doneInfoIntern = '')
    {
        if (!$id || !$status || !$userID) {
            return false;
        }
        if ($doneInfoIntern == 'undefined') {
            $doneInfoIntern = '';
        }

        $sql = '';
        $freigabeSL = DB::getSettings()->getBoolean("extBeurlaubung-schulleitung-freigabe");
        if ($freigabeSL) {
            $schulleitung = schulinfo::getSchulleitungLehrerObjects();
            foreach ($schulleitung as $sl) {
                if ($sl->getUserID() == $userID) {
                    $sql = 'doneSL = 1, doneSLDate = "'.date('Y-m-d H:i', time()).'" , doneSLInfo = "'.DB::getDB()->escapeString($doneInfoIntern).'" ';
                }
            }
        }
        $freigabeKL = DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-freigabe");
        if ( $freigabeKL ) {
            $user = DB::getSession()->getUser();
            if ( $user->isTeacher() ) {
                $teacherID = $user->getTeacherObject()->getID();
            }
            if ($teacherID) {
                $klassen = klasseDB::getAll();
                foreach ($klassen as $klasse) {
                    $leitungen = klasse::getKlassenleitungAll($klasse->getKlassenname());
                    foreach ($leitungen as $leitung) {
                        if ($leitung['lehrerID'] == $teacherID) {
                            $sql = 'doneKL = 1, doneKLDate = "'.date('Y-m-d H:i', time()).'" , doneKLInfo = "'.DB::getDB()->escapeString($doneInfoIntern).'" ';
                        }
                    }

                }
            }
        }



        if (DB::getDB()->query("UPDATE ext_beurlaubung_antrag
                SET status=" . DB::getDB()->escapeString((int)$status) . ",
                doneInfo='" . DB::getDB()->escapeString($info) . "',
                doneUser=" . DB::getDB()->escapeString($userID) . ",
                doneDate = '".date('Y-m-d H:i', time())."',
                $sql
                WHERE id=".$id
        )) {
            return true;
        }
        return false;
    }


    /**
     * @return Array[]
     */
    public static function setAntrag($userID = false, $schueler = false, $date = false, $stunden = false, $info = '', $status = 1)
    {
        if (!$userID || !$schueler || !$date || !is_array($date) || !$date[0] || !$stunden) {
            return false;
        }

        if (!$date[1]) {
            $date[1] = $date[0];
        }
        if (DB::getDB()->query("INSERT INTO ext_beurlaubung_antrag
            (
                status,
                createdTime,
                createdUserID,
                userID,
                datumStart,
                datumEnde,
                stunden,
                info
            ) values(
            ".(int)$status.",
            " .  time() . ",
            " .  DB::getDB()->escapeString($userID) . ",
            " .  DB::getDB()->escapeString($schueler) . ",
            '" .  DB::getDB()->escapeString($date[0]) . "',
            '" . DB::getDB()->escapeString($date[1]) . "',
            '" . DB::getDB()->escapeString($stunden) . "',
            '" . DB::getDB()->escapeString($info) . "'
            )
                ")) {
            return true;
        }
        return false;

    }


}