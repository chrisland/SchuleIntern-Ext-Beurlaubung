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

        $collection = [
            "id" => $this->getID(),
            "status" => $this->getStatus(),
            "createdTime" => date('d.m.Y', $this->getCreatedTime()),
            "createdUserID" => $this->getCreatedUserID(),
            "userID" => $this->getUserID(),
            "datumStart" => $dateStart->format('d.m.' ),
            "datumEnde" => $dateEnde->format('d.m.' ),
            "stunden" => $this->getStunden(),
            "info" => $this->getInfo(),
            "doneKL" => $this->getDoneKL(),
            "doneKLDate" => $this->getDoneKLDate(),
            "doneKLInfo" => $this->getDoneKLInfo(),
            "doneSL" => $this->getDoneSL(),
            "doneSLDate" => $this->getDoneSLDate(),
            "doneSLInfo" => $this->getDoneSLInfo(),
        ];

        if ($full) {
            if ( $this->getUserID() ) {
                $temp_user = user::getUserByID($this->getUserID());
                $collection['user'] = $temp_user->getCollection(true);
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
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_beurlaubung_antrag WHERE `createdUserID` = " . (int)$userID);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;

    }


    /**
     * @return Array[]
     */
    public static function setDone($id = false, $status = false, $info = false, $userID = false)
    {
        if (!$id || !$status || !$userID) {
            return false;
        }

        if (DB::getDB()->query("UPDATE ext_beurlaubung_antrag
                SET status=" . DB::getDB()->escapeString((int)$status) . ",
                doneInfo='" . DB::getDB()->escapeString($info) . "',
                doneUser=" . DB::getDB()->escapeString($userID) . ",
                doneDate = '".date('Y-m-d H:i', time())."'
                WHERE id=".$id
        )) {
            return true;
        }
        return false;
    }


    /**
     * @return Array[]
     */
    public static function setAntrag($userID = false, $schueler = false, $date = false, $stunden = false, $info = '')
    {
        if (!$userID || !$schueler || !$date || !is_array($date) || !$date[0] || !$stunden || !$info) {
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
            1,
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