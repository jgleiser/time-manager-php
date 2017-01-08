<?php
namespace TM;

class Admin extends Manager {
    public function isAdmin() {
        return true;
    }
}
