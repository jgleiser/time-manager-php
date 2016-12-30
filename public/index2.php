<?php

include_once '../src/config/database_data.php';

function __autoload($classname) {
    include '../src/classes/' . $classname . '.php';
}

// User create
$create = User::create("juan", "1234");
print_r($create);
echo "<br>\n";

// User login
$userdata = User::login("jose", "1234");
print_r($userdata);
echo "<br>\n";

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

}