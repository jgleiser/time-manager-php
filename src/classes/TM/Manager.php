<?php
namespace TM;

class Manager extends User {
    public function isManager() {
        return true;
    }
}
