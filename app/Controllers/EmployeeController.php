<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Employee.php';

class EmployeeController extends Controller {

    public function showEmployees() : void {
        $employees = Employee::getActiveEmployees();
        $this->render('Users/employees', ['employees' => $employees]);
    }
}
