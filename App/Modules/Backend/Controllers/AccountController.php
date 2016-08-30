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

namespace Multiple\Backend\Controllers;

/**
 * Admin AccountController Class
 * @author amit
 */
class AccountController extends BackendController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
    }

    /**
     * Action for account overview
     */
    public function indexAction() {
        $this->tag->prependTitle('Account');
        $this->view->admin = $this->admin;
    }

    /**
     * Edit account
     */
    public function editAction() {
        $this->tag->prependTitle('Account Edit');
        $admin = $this->admin->getAdminData();
        $form = new \Multiple\Backend\Forms\AccountForm($admin, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $admin->firstName = $this->request->getPost('first_name');
                $admin->lastName = $this->request->getPost('last_name');
                $admin->emailAddress = $this->request->getPost('email');
                $admin->phone = $this->request->getPost('phone');
                //$admin->status = $this->request->getPost('status');
                if ($admin->save()) {
                    $this->flash->success("Account has been updated successfuly.");
                    $this->response->redirect('/admin/account');
                } else {
                    if (count($admin->getMessages()) > 0) {
                        foreach ($admin->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }
                }
            }
        }
        $this->view->admin = $admin;
        $this->view->form = $form;
    }

    /**
     * Reset Password
     */
    public function resetpasswordAction() {
        $this->tag->prependTitle('Reset Password');
        $admin = $this->admin->getAdminData();
        $form = new \Multiple\Backend\Forms\ResetPasswordForm(NULL, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $adminSql = 'userName = "' . $admin->userName . '" AND passWord = "' . \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('current_password')) . '"';
                $admin = \Tourpage\Models\Admins::findFirst($adminSql);
                if ($admin && $admin->count() > 0) {
                    $admin->passWord = \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('new_password'));
                    if ($admin->update()) {
                        $this->flash->success("Password has been reseted.");
                        $this->response->redirect('/admin/account');
                    } else {
                        if (count($admin->getMessages()) > 0) {
                            foreach ($admin->getMessages() as $message) {
                                $this->flash->error((string) $message);
                            }
                        }
                    }
                } else {
                    $this->flash->error("Invalid Current Password");
                }
            }
        }
        $this->view->form = $form;
    }

}
