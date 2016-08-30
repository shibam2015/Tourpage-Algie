<?php

/*
 * Copyright (C) 2016 amit
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Multiple\Vendor\Controllers;

/**
 * Controller for Vendors Employee Management
 * @author amit
 */
class EmployeesController extends VendorController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index action for Employees Controller
     */
    public function indexAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        if ($this->request->isPost()) {
            $employeeIds = $this->request->getPost('emp');
            $employeeAction = $this->request->getPost('employee_action');
            if (count($employeeIds) > 0) {
                foreach ($employeeIds as $employeeId) {
                    $employee = \Tourpage\Models\Vendors::findFirst($employeeId);
                    if ($employee && $employee->count() > 0) {
                        switch ($employeeAction) {
                            case 'active':
                                $employee->status = \Tourpage\Models\Vendors::ACTIVE_STATUS_CODE;
                                $employee->save();
                                break;
                            case 'inactive':
                                $employee->status = \Tourpage\Models\Vendors::INACTIVE_STATUS_CODE;
                                $employee->save();
                                break;
                            case 'remove':
                                $employee->removeData();
                                break;
                        }
                    }
                }
                $this->flash->success('Selected action has been applied on to employees.');
            }
            $this->response->redirect($this->router->getRewriteUri());
        }
        $employees = \Tourpage\Models\Vendors::query();
        $employees->where('parentId = :parent_id:');
        $employees->bind(array(
            'parent_id' => $this->vendors->getId()
        ));
        $employees->order("vendorId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $employees->execute(),
            "page" => $page,
        ));
        $this->tag->setTitle('Employees');
        $this->view->pager = $pager;
    }

    /**
     * Action for View Employee Details and Report
     * @param type $employeeId
     * @return boolean
     */
    public function viewAction($employeeId = 0) {
        if (!preg_match_all('/[0-9]+/', $employeeId, $matches)) {
            return false;
        }
        $employee = \Tourpage\Models\Vendors::findFirst($employeeId);
        if (!$employee) {
            return FALSE;
        }
        $this->tag->setTitle('View Employees Detail Report');
        $this->view->employee = $employee;
    }

    /**
     * Action for add new employee
     */
    public function addAction() {
        $employeeForm = new \Multiple\Vendor\Forms\EmployeeForm();
        $employeeAcl = new \Tourpage\Models\VendorsAcl();
        if ($this->request->isPost()) {
            if ($employeeForm->isValid($this->request->getPost())) {																		
                $employee = new \Tourpage\Models\Vendors();
                $employee->parentId = $this->vendors->getId();
				$employee->businessName = $this->vendors->getVendorData()->businessName;
				$employee->jobTitle=$this->vendors->getVendorData()->jobTitle;
				$employee->vendorCategory = $this->vendors->getVendorData()->vendorCategory;
				$employee->isTripAdv = $this->vendors->getVendorData()->isTripAdv;
				//$employee->jobTitle = $this->request->getPost('job_title');
				$employee->firstName = $this->request->getPost('first_name', array('string', 'striptags'));
                $employee->lastName = $this->request->getPost('last_name', array('string', 'striptags'));
                $employee->emailAddress = $this->request->getPost('email_address');
                $employee->phone = $this->request->getPost('phone');
                $employee->countryId = $this->request->getPost('country');
                $employee->stateId = $this->request->getPost('state');
                $employee->city = $this->request->getPost('city', array('string', 'striptags'));
                $employee->passWord = \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('password'));
                $employee->status = $this->request->getPost('emp_status');
                $employee->createdOn = \Tourpage\Helpers\Utils::currentDate();
                if ($employee->save()) {
                    $acl = $this->request->getPost('acl');
                    $vendorsAcl = \Tourpage\Models\VendorsAcl::findFirst(array(
                                'conditions' => 'vendorId = :vendor_id:',
                                'bind' => array('vendor_id' => $employee->vendorId)
                    ));
                    if (!$vendorsAcl) {
                        $vendorsAcl = new \Tourpage\Models\VendorsAcl();
                    }
                    $vendorsAcl->vendorId = $employee->vendorId;
                    $vendorsAcl->aclData = serialize($acl);
                    $vendorsAcl->save();
                    $this->flash->success('Employee added successfully');
                    $this->response->redirect('/vendor/employees');
                } else {
                    foreach ($employee->getMessages() as $messages) {
                        $this->flash->error((string) $messages);
                    }
                }
            }
        }
        $this->tag->setTitle('New Employee');
        $this->view->employeeAcl = $employeeAcl;
        $this->view->formType = 'new';
        $this->view->form = $employeeForm;
        $this->view->pick('employees/form');
    }

    /**
     * Action for modify employee
     * @param int $employeeId
     * @return boolean
     */
    public function editAction($employeeId = 0) {
        $employee = \Tourpage\Models\Vendors::findFirst($employeeId);
        if (!$employee) {
            return FALSE;
        }
        $employeeForm = new \Multiple\Vendor\Forms\EmployeeForm($employee, array('edit' => TRUE));
        $employeeAcl = $employee->acl;
        if (!$employeeAcl) {
            $employeeAcl = new \Tourpage\Models\VendorsAcl();
        }
        if ($this->request->isPost()) {
            if ($employeeForm->isValid($this->request->getPost())) {
                $passWord = $this->request->getPost('password', array('string', 'striptags'));
                $employee->firstName = $this->request->getPost('first_name', array('string', 'striptags'));
                $employee->lastName = $this->request->getPost('last_name', array('string', 'striptags'));
                //$employee->emailAddress = $this->request->getPost('email_address');
                $employee->phone = $this->request->getPost('phone');
                $employee->countryId = $this->request->getPost('country');
                $employee->stateId = $this->request->getPost('state');
                $employee->city = $this->request->getPost('city', array('string', 'striptags'));
                $employee->status = $this->request->getPost('emp_status');
                if (!empty($passWord)) {
                    $employee->passWord = \Tourpage\Helpers\Utils::encryptPassword($passWord);
                }
                if ($employee->save()) {
                    $acl = $this->request->getPost('acl');
                    $vendorsAcl = \Tourpage\Models\VendorsAcl::findFirst(array(
                                'conditions' => 'vendorId = :vendor_id:',
                                'bind' => array('vendor_id' => $employee->vendorId)
                    ));
                    if (!$vendorsAcl) {
                        $vendorsAcl = new \Tourpage\Models\VendorsAcl();
                    }
                    $vendorsAcl->vendorId = $employee->vendorId;
                    $vendorsAcl->aclData = serialize($acl);
                    $vendorsAcl->save();
                    $this->flash->success('Employee added successfully');
                    $this->response->redirect('/vendor/employees');
                } else {
                    foreach ($employee->getMessages() as $messages) {
                        $this->flash->error((string) $messages);
                    }
                }
            }
        }
        $this->tag->setTitle('Edit Employee');
        $this->view->employeeAcl = $employeeAcl;
        $this->view->formType = 'edit';
        $this->view->form = $employeeForm;
        $this->view->pick('employees/form');
    }

    /**
     * Action for Remove Employee
     * @param int $employeeId
     * @return boolean
     */
    public function removeAction($employeeId = 0) {
        $employee = \Tourpage\Models\Vendors::findFirst($employeeId);
        if (!$employee) {
            return FALSE;
        }
        if ($employee && $employee->count() > 0) {
            $employee->removeData();
            $this->flash->success('Employee removed successfully');
        }
        $this->response->redirect('/vendor/employees');
    }

}
