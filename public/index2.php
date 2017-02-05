<?php

include_once '../src/config/database_data.php';

function __autoload($classname) {
    $classname = str_replace("\\", "/", $classname);
    include_once '../src/classes/' . $classname . '.php';
}

use TM\User as User;
use TM\Manager as Manager;
use TM\Admin as Admin;
use TM\TimeNote as TimeNote;


// User create
$create = User::create("juan", "1234");
print_r($create);
echo "<br>\n";

// User login
/*
$userdata = User::login("jose", "1234");
print_r($userdata);
echo "<br>\n";
/*
// url vars
echo "<p>Url Vars</p>";
var_dump($_GET);
echo "<br><br>\n";
//var_dump($_SERVER['REQUEST_URI']);
foreach ($_SERVER as $k => $v) {
    echo "['" . $k . "'] : " . $v;
    echo "<br>\n";
}
echo "<br><br>\n";
*/
$router = new Router();
echo $router;
echo "<br><br>\n";

$router->route(['hola', 'mundo']);


/*
if (!isset($userdata['error'])) {
    // Create user, manager or admin acording to role
    if ($userdata['role'] === 'ADMIN') $currentUser = new Admin($userdata);
    else if ($userdata['role'] === 'MANAGER') $currentUser = new Manager($userdata);
    else $currentUser = new User($userdata);

    // Check user role
    $username = $userdata['username'];
    if ($currentUser->isAdmin()) echo "<p>$username is Admin</p>";
    else if ($currentUser->isManager()) echo "<p>$username is Manager</p>";
    else echo "<p>$username is User</p>";
    
    // Update working hours
    $newStart = "09:00:00";
    $newEnd = "16:00:00";
    if ($currentUser->getWorkHoursStart() !== $newStart || $currentUser->getWorkHoursEnd() !== $newEnd) {
        $upd = $currentUser->setWorkHoursStart($newStart)->setWorkHoursEnd($newEnd)->update();
    }
    else $upd = 0;
    echo "upd work hours: ";
    print_r($upd);
    echo "<br>\n";

    // Update password
    $newPwd = "1234";
    $oldPwd = "1234";
    $upd = $currentUser->update($newPwd, $oldPwd);
    echo "upd password: ";
    print_r($upd);
    echo "<br>\n";
    
    // Delete user
    $deleted = $currentUser->delete("12345");
    if (!isset($deleted['error'])) {
        echo "<p>Uesr deleted</p>";
    } else {
        echo "<p>User not deleted: ";
        print_r($deleted);
        echo "</p>";
    }
    
    // insert new note
    /*
    $note = new TimeNote($currentUser);
    $note->setStartDt("2016-12-30 10:00:00")->setWorkHours("2")->setNote("Nota de prueba por create");
    $create = $note->create();
    print_r($create);
    
    /*
    // get user notes
    $timeNotes = TimeNote::getTimeNotes($currentUser);
    foreach ($timeNotes as $timeNote) {
        print_r($timeNote);
        echo "<br>\n";
    }
    echo "<br>\n";
    
    // fetch a note
    $note = new TimeNote($currentUser);
    $note->fetch(24);
    print_r($note);
    echo "<br><br>\n";
    
    // update a note
    $note->setWorkHours(3);
    $note->setStartDate("2016-12-30 09:00");
    $upd = $note->update();
    print_r($upd);
    echo "<br><br>\n";
    $note->fetch(24);
    print_r($note);
    echo "<br><br>\n";
    
    // get summary of notes between dates
    $summary = TimeNote::getNotesSumary($currentUser, "2016-12-12", "2016-12-13");
    foreach ($summary as $s) {
        print_r($s);
        echo "<br>\n";
    }
    echo "<br>\n";
    
    // get work schedule for a given day
    $schedule = TimeNote::getWorkScheduleFromDate($currentUser, "2016-12-12");
    foreach ($schedule as $s) {
        print_r($s);
        echo "<br>\n";
    }
    echo "<br>\n";
    
}
/**/

/* Date Time test
$start_dt = "2016-12-30 10:00:00";
$work_hours = "2";
$end_dt = new DateTime($start_dt);
$end_dt->add(new DateInterval('PT'.$work_hours.'H'));
echo $end_dt->format('Y-m-d H:i:s');

/* Api key expiration test */
$api_key_expire = "2017-01-08 03:39:59";
$expire_dt = new DateTime($api_key_expire);
//$expire_dt->setTimezone(new DateTimeZone('America/Santiago'));
$now = new DateTime();
//$now->setTimezone(new DateTimeZone('America/Santiago'));
echo $expire_dt->format('Y-m-d H:i:s') . "<br>";
echo $now->format('Y-m-d H:i:s') . "<br>";
var_dump($expire_dt > $now);
/**/