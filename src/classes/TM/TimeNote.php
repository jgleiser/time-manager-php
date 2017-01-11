<?php
namespace TM;

use \PDO;
use \DateTime;
use \DateInterval;

class TimeNote {
    private $id;
    private $userid;
    private $startDate;
    private $endDate;
    private $workHours;
    private $note;
    
    public function __construct(User $owner) {
        $this->userid = $owner->getId();
        return $this;
    }
    
    /* public function setId($id) {
        $this->id = $id;
        return $this;
    } */
    
    public function getId() {
        return $this->id;
    }
    
     public function setUserId($userid) {
        $this->userid = $userid;
        return $this;
    }
    
    public function getUserId() {
        return $this->userid;
    }
    
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
        return $this;
    }
    
    public function getStartDate() {
        return $this->startDate;
    }
    
    /* public function setEndDate($endDate) {
        $this->endDate = $endDate;
        return $this;
    } */
    
    public function getEndDate() {
        return $this->endDate;
    }
    
    public function setWorkHours($workHours) {
        $this->workHours = $workHours;
        return $this;
    }
    
    public function getWorkHours() {
        return $this->workHours;
    }
    
    public function setNote($note) {
        $this->note = $note;
        return $this;
    }
    
    public function getNote() {
        return $this->note;
    }
    
    private function calcendDate() {
        $endDate = new DateTime($this->startDate);
        $endDate->add(new DateInterval('PT'.$this->workHours.'H'));
        return $endDate->format('Y-m-d H:i:s');
    }
    
    // get all time notes from a user
    public static function getTimeNotes(User $owner) {
        $query = "SELECT DATE_FORMAT(START_DT, '%Y-%m-%d') AS START_DT, ";
        $query.= "DATE_FORMAT(START_DT, '%H:%i') AS START_TIME, ";
        $query.= "TIMESTAMPDIFF(MINUTE,START_DT,END_DT) AS MINUTES, ";
        $query.= "NOTE, ROW_ID ";
        $query.= "FROM TIME_LOG WHERE USER_ID = :userid ";
        $query.= "ORDER BY START_DT";
        $query_data = array(
            "userid" => $owner->getId()
        );
        
        try {
            $conn = new PDO("mysql:host=".HOST.";dbname=".DATABASE, DBUSER, DBPASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "Connection failed: " . $e->getMessage()]
            );
        }
        
        try {
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $result = $stmt->execute($query_data);
            }
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "PDO Error: " . $e->getMessage()]
            );
        }
        
        if ($result) {
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $timeNotes = $stmt->fetchAll();
            return $timeNotes;
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
    }
    
    // get notes summary between two dates
    public static function getNotesSumary(User $owner, $startDate, $endDate) {
        $query = "SELECT DATE_FORMAT(START_DT, '%Y-%m-%d') AS date, ";
        $query.= "TIMESTAMPDIFF(MINUTE,START_DT,END_DT) AS minutes, note ";
        $query.= "FROM TIME_LOG WHERE USER_ID = :userid ";
        $query.= "AND STR_TO_DATE(DATE_FORMAT(START_DT, '%Y-%m-%d'), '%Y-%m-%d') ";
        $query.= "BETWEEN STR_TO_DATE(:start_dt, '%Y-%m-%d') ";
        $query.= "AND STR_TO_DATE(:end_dt, '%Y-%m-%d') ";
        $query.= "ORDER BY START_DT";
        $query_data = array(
            "userid" => $owner->getId(),
            "start_dt" => $startDate,
            "end_dt" => $endDate
        );
        
        try {
            $conn = new PDO("mysql:host=".HOST.";dbname=".DATABASE, DBUSER, DBPASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "Connection failed: " . $e->getMessage()]
            );
        }
        
        try {
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $result = $stmt->execute($query_data);
            }
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "PDO Error: " . $e->getMessage()]
            );
        }
        
        if ($result) {
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $summary = $stmt->fetchAll();
            return $summary;
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
    }
    
    // get work schedule from a given date for a user
    public static function getWorkScheduleFromDate(User $owner, $date) {
        $query = "SELECT DATE_FORMAT(START_DT, '%Y-%m-%d %H:%i') AS START_DT, ";
        $query.= "DATE_FORMAT(END_DT, '%Y-%m-%d %H:%i') AS END_DT, ";
        $query.= "NOTE FROM TIME_LOG WHERE USER_ID = :userid ";
        $query.= "AND (DATE_FORMAT(START_DT, '%Y-%m-%d') = :date ";
        $query.= "OR DATE_FORMAT(END_DT, '%Y-%m-%d') = :date) ";
        $query.= "ORDER BY START_DT";
        $query_data = array(
            "userid" => $owner->getId(),
            "date" => $date
        );
        
        try {
            $conn = new PDO("mysql:host=".HOST.";dbname=".DATABASE, DBUSER, DBPASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "Connection failed: " . $e->getMessage()]
            );
        }
        
        try {
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $result = $stmt->execute($query_data);
            }
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "PDO Error: " . $e->getMessage()]
            );
        }
        
        if ($result) {
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $schedule = $stmt->fetchAll();
            return $schedule;
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
    }
    
    // creates a new note
    public function create() {
        // check that startDate, workHours and note are set
        if (!isset($this->startDate) || !isset($this->workHours) || !isset($this->note)) {
            return array(
                'error' => ['msg' => "Need to set startDate, workHours and note to create a new time note"]
            );
        }
        
        $this->endDate = $this->calcendDate();
        
        $query = "INSERT INTO TIME_LOG (USER_ID, START_DT, END_DT, NOTE) ";
        $query.= "VALUES (:user_id, :start_dt, :end_dt, :note)";
        $query_data = array(
            "user_id" => $this->userid,
            "start_dt" => $this->startDate,
            "end_dt" => $this->endDate,
            "note" => $this->note
        );
        
        try {
            $conn = new PDO("mysql:host=".HOST.";dbname=".DATABASE, DBUSER, DBPASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "Connection failed: " . $e->getMessage()]
            );
        }
        
        try {
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $result = $stmt->execute($query_data);
            }
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "PDO Error: " . $e->getMessage()]
            );
        }
        
        if ($result) {
            $this->id = $conn->lastInsertId();
            return true;
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
        
        return array(
            'error' => ['msg' => "Unexpected error while creating a new user"]
        );
    }
    
    // update a given time note
    public function update() {
        // check that id, startDate, workHours and note are set
        if (!isset($this->id) || !isset($this->startDate) || !isset($this->workHours) || !isset($this->note)) {
            return array(
                'error' => ['msg' => "Need to set id, startDate, workHours and note to update a time note"]
            );
        }
        
        $this->endDate = $this->calcendDate();
        
        $query = "UPDATE TIME_LOG SET ";
        $query.= "START_DT = :start_dt, ";
        $query.= "END_DT = :end_dt, ";
        $query.= "NOTE = :note ";
        $query.= "WHERE ROW_ID = :noteid AND USER_ID = :userid";
        $query_data = array(
            "start_dt" => $this->startDate,
            "end_dt" => $this->endDate,
            "note" => $this->note,
            "noteid" => $this->id,
            "userid" => $this->userid
        );
        
        try {
            $conn = new PDO("mysql:host=".HOST.";dbname=".DATABASE, DBUSER, DBPASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "Connection failed: " . $e->getMessage()]
            );
        }
        
        try {
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $result = $stmt->execute($query_data);
            }
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "PDO Error: " . $e->getMessage()]
            );
        }
        
        if ($result) {
            $rowCount = $stmt->rowCount();
            if (1 === $rowCount) {
                return true;
            } else if (0 === $rowCount) {
                return array(
                    'error' => ['msg' => "Time note not updated or data didn't change"]
                );
            } else {
                return array(
                    'error' => ['msg' => "Unexpected error, more than 1 time note updated"]
                );
            }
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
    }
    
    // fetch data from a given note
    public function fetch($noteid) {
        $query = "SELECT DATE_FORMAT(START_DT, '%Y-%m-%d %H:%i') AS START_DT, ";
        $query.= "DATE_FORMAT(END_DT, '%Y-%m-%d %H:%i') AS END_DT, ";
        //$query = "SELECT DATE_FORMAT(START_DT, '%Y-%m-%d') AS START_DT, ";
        //$query.= "DATE_FORMAT(START_DT, '%H:%i') AS START_TIME, ";
        $query.= "TIMESTAMPDIFF(MINUTE,START_DT,END_DT) AS MINUTES, ";
        $query.= "NOTE, ROW_ID ";
        $query.= "FROM TIME_LOG ";
        $query.= "WHERE ROW_ID = :noteid AND USER_ID = :userid";
        $query_data = array(
            "noteid" => $noteid,
            "userid" => $this->userid
        );
        
        try {
            $conn = new PDO("mysql:host=".HOST.";dbname=".DATABASE, DBUSER, DBPASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "Connection failed: " . $e->getMessage()]
            );
        }
        
        try {
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $result = $stmt->execute($query_data);
            }
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "PDO Error: " . $e->getMessage()]
            );
        }
        
        if ($result) {
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $timeNote = $stmt->fetch();
            $this->id = $timeNote['ROW_ID'];
            $this->startDate = $timeNote['START_DT'];
            $this->endDate = $timeNote['END_DT'];
            $this->workHours = (int)$timeNote['MINUTES'] / 60;
            $this->note = $timeNote['NOTE'];
            return $this;
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
    }
}