<?php
namespace TM;

include_once '../src/classes/TM/Manager.php';

class Admin extends \TM\Manager {
    public function isAdmin() {
        return true;
    }
}
