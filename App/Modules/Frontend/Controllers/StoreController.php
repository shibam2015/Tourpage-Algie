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
 * Class StoreController
 * This controll is use to display vendor store
 * @author amit
 */
class StoreController extends FrontendController {

    /**
     * Action for view vendor store
     * @param int $store
     */
    public function indexAction($store = '') {
        if (!$store) {
            return FALSE;
        }

        if (!preg_match_all('/[0-9]/', $store)) {
            return FALSE;
        }
        $storeVendor = \Tourpage\Models\Vendors::findFirst($store);
        if (!$storeVendor || $storeVendor->count() == 0) {
            return FALSE;
        }

        $storeVendorBanner = [];
        if ($storeVendor->vendorBanners->count() > 0) {
            foreach ($storeVendor->vendorBanners as $vendorBanner) {
                if ($vendorBanner->bannerStatus == $vendorBanner::ACTIVE_STATUS_CODE) {
                    $storeVendorBanner[] = $vendorBanner;
                }
            }
        }

        $groupVendors = \Tourpage\Models\GroupsVendors::find(array(
                    'conditions' => 'vendorId = :vendor_id:',
                    'order' => 'groupVendorsId DESC',
                    'limit' => 5,
                    'bind' => array(
                        'vendor_id' => $storeVendor->vendorId
                    )
        ));
		$groupTours = \Tourpage\Models\GroupsTours::find(array(
                    'conditions' => 'vendorId = :vendor_id:',
                    'order' => 'groupToursId DESC',
                    'limit' => 6,
                    'bind' => array(
                    'vendor_id' => $storeVendor->vendorId
                    )
        ));
		
		/*$builder = $this->modelsManager->createBuilder();
           $builder->columns('vendorId, firstname');
                   $builder->from('vendor');
                   $builder->orderBy('id');

             $paginator = new PaginatorQueryBuilder(
              array(
                  "builder" => $builder,
                  "limit"   => 4,
                  "page"    => 1
                 )
              );*/
	         // if($groupTours->)	
		$otherToursQuery = \Tourpage\Models\ToursCategory::query();
        $otherToursQuery->leftJoin('\Tourpage\Models\VendorsTours', 'vt.tourId = \Tourpage\Models\ToursCategory.tourId', 'vt');
        $otherToursQuery->andWhere('vt.status = :status:');
		$otherToursQuery->andWhere('vt.vendorId = :vendor_id:');
        $otherToursQuery->bind(array(
            'vendor_id' => $storeVendor->vendorId,
            'status' => \Tourpage\Models\Tours::ACTIVE_STATUS_CODE
        ));
        $otherToursQuery->orderBy('\Tourpage\Models\ToursCategory.tourCategoryId DESC');
        $otherToursQuery->limit('3');
		//$otherToursQuery->ra
        $otherTours = $otherToursQuery->execute();

        $reviews = \Tourpage\Models\ToursReview::find(array(
                    'conditions' => 'vendorId = :vendor_id: AND reviewStatus = :status: AND starCount >= 4',
                    'bind' => array('vendor_id' => $storeVendor->vendorId, 'status' => \Tourpage\Models\ToursReview::ACTIVE_STATUS_CODE),
                    'order' => 'reviewId DESC',
                    'limit' => 5
        ));
        /////for style selection start//////
//        $classGrid = $classList = 'hidden';
//        if ($this->cookies->has('tour_view_style')) {
//            $viewStyle = $this->cookies->get('tour_view_style');
//            if ($viewStyle == 'grid') {
//                $classGrid = 'show';
//            }
//            if ($viewStyle == 'list') {
//                $classList = 'show';
//            }
//        } else {
//            $classGrid = 'show';
//        }
//        $this->view->classGrid = $classGrid;
//        $this->view->classList = $classList;
        //////////for style selection end/////////
	
        $this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->tag->settitle($storeVendor->businessName);
        $this->view->storeVendor = $storeVendor;
        $this->view->groupVendors = $groupVendors;
	$this->view->groupTours = $groupTours;
        $this->view->storeVendorBanner = $storeVendorBanner;
	$this->view->otherTours = $otherTours;
        $this->view->reviews = $reviews;
		$this->view->bannerImages = $storeVendor->vendorBanners;
 
		
	
	}


    /**
     * Action for store tour list
     * @param int $store
     * @param int $page
     */
    public function toursAction($store = '', $page = 1) {
        if (!$store) {
            return FALSE;
        }

        if (!preg_match_all('/[0-9]/', $store)) {
            return FALSE;
        }
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $storeVendor = \Tourpage\Models\Vendors::findFirst($store);
        if (!$storeVendor || $storeVendor->count() == 0) {
            return FALSE;
        }
        /////////////////
//         $storeVendorBanner = [];
//        if ($storeVendor->vendorBanners->count() > 0) {
//            foreach ($storeVendor->vendorBanners as $vendorBanner) {
//                if ($vendorBanner->bannerStatus == $vendorBanner::ACTIVE_STATUS_CODE) {
//                    $storeVendorBanner[] = $vendorBanner;
//                }
//            }
//        }

        $groupVendors = \Tourpage\Models\GroupsVendors::find(array(
                    'conditions' => 'vendorId = :vendor_id:',
                    'order' => 'groupVendorsId DESC',
                    'limit' => 5,
                    'bind' => array(
                        'vendor_id' => $storeVendor->vendorId
                    )
        ));
		$groupTours = \Tourpage\Models\GroupsTours::find(array(
                    'conditions' => 'vendorId = :vendor_id:',
                    'order' => 'groupToursId DESC',
                    //'limit' => 6,
                    'bind' => array(
                    'vendor_id' => $storeVendor->vendorId
                    )
        ));
        
        //////////////////////

        $vendorTours = \Tourpage\Models\VendorsTours::find(array(
                    'conditions' => 'vendorId = :vendor_id: AND status = :status:',
                    'order' => 'vendorTourId DESC',
                    'bind' => array(
                        'vendor_id' => $storeVendor->vendorId,
                        'status' => \Tourpage\Models\VendorsTours::ACTIVE_STATUS_CODE
                    )
        ));
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $vendorTours,
            "page" => $page,
            "limit" => 12,
        ));
        $classGrid = $classList = 'hidden';
        if ($this->cookies->has('tour_view_style')) {
            $viewStyle = $this->cookies->get('tour_view_style');
            if ($viewStyle == 'grid') {
                $classGrid = 'show';
            }
            if ($viewStyle == 'list') {
                $classList = 'show';
            }
        } else {
            $classGrid = 'show';
        }
        $pager->setUriPattern('/store/tours/' . $store . '/{page}');
        $this->assets->collection('header_css')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('header_js')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->tag->settitle($storeVendor->businessName . '- Tours');
        $this->view->storeVendor = $storeVendor;
        /////////
        $this->view->groupVendors = $groupVendors;
        $this->view->groupTours = $groupTours;
        ///////////////
        $this->view->pager = $pager;
        $this->view->classGrid = $classGrid;
        $this->view->classList = $classList;
    }

    /**
     * Stie action for login and register
     * @param int $store
     * @param string $action
     * @return boolean
     */
    public function siteAction($store = '', $action = '') {
        global $form;
        if (!$store) {
            return FALSE;
        }

        if (!preg_match_all('/[0-9]/', $store)) {
            return FALSE;
        }
        $storeVendor = \Tourpage\Models\Vendors::findFirst($store);
        if (!$storeVendor || $storeVendor->count() == 0) {
            return FALSE;
        }

        switch ($action) {
            case 'login':
                $this->_siteActionLogin($storeVendor);
                break;
            case 'register':
                $this->_siteActionRegister($storeVendor);
                break;
            case 'logout':
                $this->_siteActionLogout($storeVendor);
                break;
            case 'resetpassword':
                break;
        }
        $this->view->form = $form;
        $this->view->storeVendor = $storeVendor;
    }

    /**
     * Site Member Login Process
     * @return boolean
     */
    private function _siteActionLogin(&$storeVendor) {
        global $form;
        if ($this->member->isLoggedIn()) {
            $this->response->redirect($storeVendor->getStorFrontUri(), true);
            return false;
        }
        $this->tag->settitle('Login');
        $form = new \Multiple\Frontend\Forms\MemberLoginForm();
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $emailAddress = $this->request->getPost('useremail');
                $password = $this->request->getPost('password');
                $rememberMe = $this->request->getPost('remember_me');
                $memberSql = array(
                    //'emailAddress=:email: AND passWord=:password: AND status=:status:',
                    'emailAddress=:email: AND passWord=:password:',
                    'bind' => array(
                        'email' => $emailAddress,
                        'password' => \Tourpage\Helpers\Utils::encryptPassword($password),
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
                        $this->response->redirect($storeVendor->getStorFrontUri(), true);
                    } else {
                        $this->flash->notice('Your account activation is still pending. Please verify your email to activate your account.');
                    }
                } else {
                    $this->flash->error('Invalid Email/Password');
                }
            } else {
                foreach ($form->getMessages() as $formMessage) {
                    $this->flash->error((string) $formMessage);
                }
            }
        }
        $this->view->pick('store/site/login');
    }

    /**
     * Site Member Registration Process
     * @return boolean
     */
    private function _siteActionRegister(&$storeVendor) {
        global $form;
        if ($this->member->isLoggedIn()) {
            $this->response->redirect($storeVendor->getStorFrontUri(), true);
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

                    $memberToVendor = \Tourpage\Models\MembersToVendor::findFirst(array(
                                'conditions' => 'memberId = :member_id: AND vendorId = :vendor_id:',
                                'bind' => array(
                                    'member_id' => $member->memberId,
                                    'vendor_id' => $storeVendor->vendorId
                                )
                    ));
                    if (!$memberToVendor) {
                        $memberToVendorCreate = new \Tourpage\Models\MembersToVendor();
                        $memberToVendorCreate->memberId = $member->memberId;
                        $memberToVendorCreate->vendorId = $storeVendor->vendorId;
                        $memberToVendorCreate->save();
                    }

                    $is_agent = $this->request->getPost('is_agent');
                    if ($is_agent && $is_agent == 1) {
                        $vendorsRegisteredAgent = new \Tourpage\Models\VendorsRegisteredAgents();
                        $vendorsRegisteredAgent->memberId = $member->memberId;
                        $vendorsRegisteredAgent->vendorId = $storeVendor->vendorId;
                        $vendorsRegisteredAgent->requestOn = \Tourpage\Helpers\Utils::currentDate();
                        $vendorsRegisteredAgent->save();

                        $messageBody = 'Request for agent by ' . ucwords($member->firstName . ' ' . $member->lastName) . '<' . $member->emailAddress . '>';
                        $mail = new \Tourpage\Library\Mail();
                        $mail->setFrom($member->emailAddress, $member->firstName . ' ' . $member->lastName);
                        $mail->setTo($storeVendor->emailAddress, $storeVendor->firstName . ' ' . $storeVendor->lastName);
                        $mail->setSubject('Request for Agent');
                        $mail->setBody($messageBody);
                        $mail->send();
                    }

                    $mail = new \Tourpage\Library\Mail();
                    $mail->setTo($member->emailAddress, $member->firstName . ' ' . $member->lastName);
                    $mail->setSubject('Registration Successful');
                    $mail->setBody($this->getTemplateForActiveNewAccount($memberEventModel));
                    $mail->send();
                    $this->flash->success("Your account has been created successfuly. An activation email has been sent to your email address. Please activate your account within 24 hrs.");
                    //$this->response->redirect($this->router->getRewriteUri());
                    //$this->session->set('member', $member);
                    $this->response->redirect($storeVendor->getStorFrontUri(), true);
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
        $this->view->pick('store/site/register');
    }

    /**
     * Site Member Logout Process
     * @return boolean
     */
    private function _siteActionLogout(&$storeVendor) {
        if (!$this->member->isLoggedIn()) {
            $this->response->redirect($storeVendor->getStorFrontUri() . '/login', true);
            return false;
        }
        $this->session->remove('member');
        $cookie = $this->cookies->get('member_remember_me');
        $cookie->delete();
        $this->response->redirect($storeVendor->getStorFrontUri(), true);
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
     * Page action for store CMS
     * @param int $store
     * @param string $content
     * @return boolean
     */
    public function pageAction($store = '', $content = '') {
        if (!$store) {
            return FALSE;
        }

        if (!preg_match_all('/[0-9]/', $store)) {
            return FALSE;
        }
        $storeVendor = \Tourpage\Models\Vendors::findFirst($store);
        if (!$storeVendor || $storeVendor->count() == 0) {
            return FALSE;
        }
        $pageContent = '';
        $pageBanner = '';
        switch ($content) {
            case 'about-us':
                $this->tag->settitle('About Us');
                $pageContent = \Tourpage\Helpers\Utils::decodeString($storeVendor->aboutUs);
                $pageAboutUsStatus = \Tourpage\Helpers\Utils::decodeString($storeVendor->aboutUsStatus);
                $pageContentAdvance = \Tourpage\Helpers\Utils::decodeString($storeVendor->aboutUsAdvance);
                
                $pageBanner = $this->tag->image(array(
                                $storeVendor->getAboutUsBannerUri(),
                                "alt" => $storeVendor->businessName,
                                "title" => $storeVendor->businessName
                               // "class" => "img-thumbnail",
                            ));
                //$pageBanner = $storeVendor->aboutUsBanner;
                
                break;
            case 'terms-conditions':
                $this->tag->settitle('Terms & Conditions');
                $pageContent = \Tourpage\Helpers\Utils::decodeString($storeVendor->policy);
                break;
        }

        $this->view->pageContent = $pageContent;
         $this->view->pageBanner = $pageBanner;
         $this->view->pageAboutUsStatus = $pageAboutUsStatus;
         $this->view->pageContentAdvance = $pageContentAdvance;
        $this->view->storeVendor = $storeVendor;
		$this->view->bannerImages = $storeVendor->vendorBanners;
    }

    /**
     * Action for add vendor to member favorite list
     * @param int $store
     */
    public function addtofavAction($store = '') {
        if (!$store) {
            return FALSE;
        }
        if (!preg_match_all('/[0-9]/', $store)) {
            return FALSE;
        }
        $storeVendor = \Tourpage\Models\Vendors::findFirst($store);
        if (!$storeVendor || $storeVendor->count() == 0) {
            return FALSE;
        }
        $acturl = $this->request->getQuery('acturl');
        $redirectTo = $this->url->getBaseUri();
        if ($acturl) {
            $redirectTo = base64_decode($acturl);
        }
        if (!$this->member->isLoggedIn()) {
            $redirectTo = $this->url->get('/auth/login?_rt=' . base64_encode('/store/addtofav/' . $store . '?acturl=' . $acturl));
        }
        if ($this->member->isLoggedIn()) {
            if (!$this->member->isFavoriteVendor($store)) {
                $this->member->addToFavorite($store, 'vendor');
                $this->flash->success("\"<em>" . $storeVendor->businessName . "</em>\" has been added to your favorite list.");
            } else {
                $this->flash->notice("\"<em>" . $storeVendor->businessName . "</em>\" already in your favorite list.");
            }
        }
        $this->response->redirect($redirectTo, true);
    }
	public function groupToursAction($vendorId,$groupId){
		if ($this->request->isAjax()) {
            $groupTours = \Tourpage\Models\GroupsTours::find(array(
                        "groupId = :group_id: AND vendorId = :vendor_id:",
                        "bind" => array(
                            ":group_id" => $groupId,
                            ":vendor_id" => $vendorId
                        )
            ));
        }
		$this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
		$this->view->groupTours = $groupTours;
	}


public function contactsAction($store = '', $content = '') {
        if (!$store) {
            return FALSE;
        }

        if (!preg_match_all('/[0-9]/', $store)) {
            return FALSE;
        }
        $storeVendor = \Tourpage\Models\Vendors::findFirst($store);
        if (!$storeVendor || $storeVendor->count() == 0) {
            return FALSE;
        }
//       $pageContent = '';
//        switch ($content) {           
//		case 'contacts':
//                $this->tag->settitle('Contacts');                     
//                $pageContent = \Tourpage\Helpers\Utils::decodeString($storeVendor->contacts);
//                break;            
//        }        
        //$this->view->pageContent = $pageContent;
          $this->view->storeVendor = $storeVendor;
          
          $this->tag->settitle('Store Contacts'); 
          //$contactForm = new \Multiple\Frontend\Forms\ContactForm();
           $form = new \Multiple\Frontend\Forms\ContactForm();
           
           if ($this->request->isPost()) {
           // if ($form->isValid($this->request->getPost())) {
             //$vendor = \Tourpage\Models\Vendors();
               // $vendorId = $this->vendors->getId();
               $vendorId="";
             $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
            // echo $vendor->supportEmail;
            
                $subject= $this->request->getPost('subject', array('string', 'striptags'));
                $name= $this->request->getPost('name', array('string', 'striptags'));
                $email_address= $this->request->getPost('email_address', 'email');
                $phone= $this->request->getPost('phone', array('string', 'striptags'));
                $message= $this->request->getPost('message', array('string', 'striptags'));
                 //$member->createdOn = \Tourpage\Helpers\Utils::currentDate();
                //if ($member->save()) {
                              
                
                
                    $mail = new \Tourpage\Library\Mail();
                    //$mail->setTo($member->emailAddress, $member->firstName . ' ' . $member->lastName);
                    //$mail->setTo($vendor->supportEmail, $phone . ' ' . $phone . ' ' . $email_address);
                    $mail->setTo($vendor->supportEmail);
                    $mail->setSubject($subject);
                    //$mail->setBody($this->getTemplateForActiveNewAccount($memberEventModel));
                    $mail->setBody($message .'<br> Sender Details:<br>Name:' . $name . '<br>Phone: ' . $phone . '<br>Mail: ' .$email_address);
                     //$mail->setBody($message);
                    $mail->send();
                    $this->flash->success("Your message has been sent successfuly.");
                    $this->response->redirect($this->router->getRewriteUri());
                    
                   // $this->response->redirect('/');
//                } else {
//                    foreach ($member->getMessages() as $memberMessage) {
//                        $this->flash->error((string) $memberMessage);
//                    }
//                }
               
           // } 
        }
           
           $this->view->form = $form;
    }

	public function aboutusAction($params = '')
    {
		$this->tag->settitle('About Us');
        $vendorId = $this->request->getPost()['vendor_id'];
        $storeVendor = \Tourpage\Models\Vendors::findFirst((int)$vendorId);
		$pageAboutUsStatus = \Tourpage\Helpers\Utils::decodeString($storeVendor->aboutUsStatus);
        $pageContentAdvance = \Tourpage\Helpers\Utils::decodeString($storeVendor->aboutUsAdvance);
        $pageContentDefault = \Tourpage\Helpers\Utils::decodeString($storeVendor->aboutUs);
        $this->view->pageContent = (int)$pageAboutUsStatus == 2 ? $pageContentAdvance : $pageContentDefault;
        $this->view->storeVendor = $storeVendor;
		$this->view->bannerImages = $storeVendor->vendorBanners;
    }


}
