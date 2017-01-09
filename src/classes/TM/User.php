<?php
namespace TM;

use \PDO;
use \DateTime;
use \DateTimeZone;
use \DateInterval;

class User {
    protected $id;
    protected $username;
    protected $workHoursStart;
    protected $workHoursEnd;
    protected $apiKey;
    protected $apiKeyExpiration;
    
    // to be used with User::login returned data
    public function __construct($userdata) {
        $this->id = $userdata['id'];
        $this->username = $userdata['username'];
        $this->workHoursStart = $userdata['workHoursStart'];
        $this->workHoursEnd = $userdata['workHoursEnd'];
        $this->apiKey = $userdata['apiKey'];
        $this->apiKeyExpiration = $userdata['apiKeyExpiration'];
        return $this;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function setWorkHoursStart($time) {
        $this->workHoursStart = $time;
        return $this;
    }
    
    public function getWorkHoursStart() {
        return $this->workHoursStart;
    }
    
    public function setWorkHoursEnd($time) {
        $this->workHoursEnd = $time;
        return $this;
    }
    
    public function getWorkHoursEnd() {
        return $this->workHoursEnd;
    }
    
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
        return $this;
    }
    
    public function getApiKey() {
        return $this->apiKey;
    }
    
    public function setApiKeyExpiration($apiKeyExpiration) {
        $this->apiKeyExpiration = $apiKeyExpiration;
        return $this;
    }
    
    public function getApiKeyExpiration() {
        return $this->apiKeyExpiration;
    }
    
    // Static function to login a user with username and password
    public static function login($username, $password) {
        // check if user exists and password is ok
        $query = "SELECT ROW_ID, USERNAME, PASSWORD, ROLE, API_KEY, API_KEY_EXPIRATION, WORK_START_TIME, WORK_END_TIME ";
        $query.= "FROM USERS WHERE USERNAME = :username";
        $query_data = array(
            'username' => $username
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
            $user = $stmt->fetch();
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
        
        if (empty($user)) {
            return array(
                'error' => [
                    'msg' => "User not found",
                    'code' => 404
                ]
            );
        }
        else if (!password_verify($password, $user['PASSWORD'])) {
            return array(
                'error' => [
                    'msg' => "Password is incorrect for user $username",
                    'code' => 403
                ]
            );
        }
        
        // create api key and update when expires
        $api_key = Api::genApiKey($user['ROW_ID']);
        $api_key_expiration = new DateTime();
        //$api_key_expiration->setTimezone(new DateTimeZone('America/Santiago'));
        $api_key_expiration->add(new DateInterval('P1D'));
        $expDate = $api_key_expiration->format('Y-m-d H:i:s');
        
        $query = "UPDATE USERS SET API_KEY = :api_key, ";
        $query.= "API_KEY_EXPIRATION = STR_TO_DATE('".$expDate."', '%Y-%m-%d %H:%i:%s') ";
        $query.= "WHERE ROW_ID = :userid";
        $query_data = array(
            'api_key' => $api_key,
            'userid' => $user['ROW_ID']
        );
        
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
            $row_count = $stmt->rowCount();
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
        
        return array(
            'id' => $user['ROW_ID'],
            'username' => $user['USERNAME'],
            'role' => $user['ROLE'],
            'workHoursStart' => $user['WORK_START_TIME'],
            'workHoursEnd' => $user['WORK_END_TIME'],
            'apiKey' => $api_key,
            'apiKeyExpiration' => $expDate
        );
    }
    
    // Static function to login user with userid and api key
    public static function loginApi($userid, $apiKey) {
        $query = "SELECT ROW_ID, USERNAME, PASSWORD, ROLE, API_KEY, API_KEY_EXPIRATION, WORK_START_TIME, WORK_END_TIME ";
        $query.= "FROM USERS WHERE ROW_ID = :userid";
        $query_data = array(
            'userid' => $userid
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
            $user = $stmt->fetch();
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
        
        if (empty($user)) {
            return array(
                'error' => [
                    'msg' => "User not found",
                    'code' => 404
                ]
            );
        }
        else if ($apiKey !== $user['API_KEY']) {
            return array(
                'error' => [
                    'msg' => "Api key is incorrect",
                    'code' => 403
                ]
            );
        }
        else if (new DateTime($user['API_KEY_EXPIRATION']) < new DateTime()) {
            return array(
                'error' => [
                    'msg' => "Api key is expired, please login again",
                    'code' => 403
                ]
            );
        }
        
        return array(
            'id' => $user['ROW_ID'],
            'username' => $user['USERNAME'],
            'role' => $user['ROLE'],
            'workHoursStart' => $user['WORK_START_TIME'],
            'workHoursEnd' => $user['WORK_END_TIME'],
            'apiKey' => $user['API_KEY'],
            'apiKeyExpiration' => $user['API_KEY_EXPIRATION']
        );
    }
    
    // Check if a given username exists
    private static function usernameExists($username) {
        $query = "SELECT ROW_ID FROM USERS WHERE USERNAME = ? LIMIT 1";
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
                $result = $stmt->execute(array($username));
            }
        } catch(PDOException $e) {
            return array(
                'error' => ['msg' => "PDO Error: " . $e->getMessage()]
            );
        }
        
        if ($result) {
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $user = $stmt->fetch();
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
        
        if (!empty($user) && (int)$user['ROW_ID'] > 0)
            return true;
        return false;
    }
    
    // static function to create a new user
    public static function create($username, $password) {
        // check if user exists
        if (self::usernameExists($username)) {
            return array(
                'error' => [
                    'msg' => "Username $username already exists",
                    'code' => 200
                ]
            );
        }
        
        $query = "INSERT INTO USERS (USERNAME, PASSWORD, ROLE) ";
        $query .= "VALUES (:username, :password, :role)";
        $query_data = array(
            "username" => $username,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "role" => "USER"
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
            $userid = $conn->lastInsertId();
            return $userid;
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
    
    public function isManager() {
        return false;
    }
    
    public function isAdmin() {
        return false;
    }
    
    protected function checkPassword($pwd) {
        $query = "SELECT PASSWORD FROM USERS WHERE ROW_ID = :userid";
        $query_data = array(
            "userid" => $this->id
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
            $password = $stmt->fetch();
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
        
        if (!empty($password))
            return password_verify($pwd, $password['PASSWORD']);
        return false;
    }
    
    public function show() {
        return array(
            'id' => $this->id,
            'username' => $this->username,
            'workHoursStart' => $this->workHoursStart,
            'workHoursEnd' => $this->workHoursEnd
        );
    }
    
    // Updates user data in DB with current data
    public function update($newPwd = "", $oldPwd = "") {
        $pwd = $newPwd != "" ? password_hash($newPwd, PASSWORD_DEFAULT) : NULL;
        $checkPwd = $pwd && $oldPwd != "" ? $this->checkPassword($oldPwd) : NULL;
        
        $query = "UPDATE USERS SET";
        $query.= " WORK_START_TIME = :work_start_time,";
        $query.= " WORK_END_TIME = :work_end_time";
        if ($pwd && $checkPwd) {
            $query.= ", PASSWORD = :password";
        }
        $query.= " WHERE ROW_ID = :userid";
        
        $query_data = array(
            "work_start_time" => $this->workHoursStart,
            "work_end_time" => $this->workHoursEnd,
            "userid" => $this->id
        );
        
        if ($pwd && $checkPwd) {
            $query_data["password"] = $pwd;
        } else if ($pwd && !$checkPwd) {
            return array(
                'error' => ['msg' => "Current password didn't match"]
            );
        }
        
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
                    'error' => [
                        'msg' => "User not updated or data didn't change",
                        'code' => 200
                    ]
                );
            } else {
                return array(
                    'error' => ['msg' => "Unexpected error, more than 1 user updated"]
                );
            }
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
    }
    
    // delete user, password check is required
    public function delete($usrPwd) {
        if (!isset($usrPwd)) {
            return array(
                'error' => ['msg' => "Password needed to delete user " . $this->username]
            );
        }
        
        $checkPwd = $this->checkPassword($usrPwd);
        if (!$checkPwd) {
            return array(
                'error' => ['msg' => "Given password is incorrect, can't delete user " . $this->username]
            );
        }
        
        $query = "DELETE FROM USERS WHERE ROW_ID = :row_id";
        $query_data = array(
            "row_id" => $this->id
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
                $this->id = null;
                $this->username = null;
                $this->workHoursStart = null;
                $this->workHoursEnd = null;
                $this->apiKey = null;
                $this->apiKeyExpiration = null;
                return true;
            } else if (0 === $rowCount) {
                return array(
                    'error' => ['msg' => "User " . $this->username . " was not deleted"]
                );
            } else {
                return array(
                    'error' => ['msg' => "Unexpected error, more than 1 user deleted"]
                );
            }
        } else {
            $error = $stmt->errorInfo();
            return array(
                'error' => ['msg' => "Query failed with message: " . $error[2]]
            );
        }
    }
}

