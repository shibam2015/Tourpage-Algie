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
 * Controller for AgentsController
 * @author amit
 */
class AgentsController extends VendorController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index Acton for Agents Controller
     */
    public function indexAction($agentType = 'local', $page = 1) {
        $this->response->redirect('/vendor/agents/local');
    }

    /**
     * Action for Local Agents
     * @param int $page
     */
    public function localAction($page = 1) {
        $agents = \Tourpage\Models\VendorsLocalAgents::find(array(
                    'conditions' => 'vendorId = :vendor_id:',
                    'bind' => array('vendor_id' => !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId()),
                    'order' => 'vlagentId DESC'
        ));
        $pager = new \Tourpage\Library\Pager(array(
            'data' => $agents,
            'page' => $page,
        ));
        $this->tag->setTitle('Local Agents');
        $this->view->agents = $pager;
    }

    /**
     * Action for Registered Agents
     * @param int $page
     */
    public function registeredAction($page = 1) {
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        $agents = \Tourpage\Models\VendorsRegisteredAgents::find(array(
                    'conditions' => 'vendorId = :vendor_id: AND requestStatus = :request_status:',
                    'bind' => array(
                        'vendor_id' => $vendorId,
                        'request_status' => \Tourpage\Models\VendorsRegisteredAgents::AGENT_APPROVED_STATUS_CODE,
                    ),
                    'order' => 'vragentId DESC'
        ));
        $agentRequests = \Tourpage\Models\VendorsRegisteredAgents::count(array(
                    'conditions' => 'vendorId = :vendor_id: AND requestStatus = :request_status:',
                    'bind' => array(
                        'vendor_id' => $vendorId,
                        'request_status' => \Tourpage\Models\VendorsRegisteredAgents::AGENT_PENDING_STATUS_CODE,
                    ),
        ));
        $pager = new \Tourpage\Library\Pager(array(
            'data' => $agents,
            'page' => $page,
        ));
        $this->tag->setTitle('Registered Agents');
        $this->view->agents = $pager;
        $this->view->agentRequests = $agentRequests;
    }

    /**
     * Action for create new local agent
     */
    public function addAction() {
        $vendorLocalAgentForm = new \Multiple\Vendor\Forms\VendorsLocalAgentForm();
        if ($this->request->getPost()) {
            if ($vendorLocalAgentForm->isValid($this->request->getPost())) {
                $localAgent = new \Tourpage\Models\VendorsLocalAgents();
                $localAgent->vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
                $localAgent->commission = $this->request->getPost('commission', 'float');
                $localAgent->firstName = $this->request->getPost('first_name', array('string', 'striptags'));
                $localAgent->lastName = $this->request->getPost('last_name', array('string', 'striptags'));
                $localAgent->passWord = $this->request->getPost('password', array('string', 'striptags'));
                $localAgent->passWord = \Tourpage\Helpers\Utils::encryptPassword($localAgent->passWord);
                $localAgent->emailAddress = $this->request->getPost('email_address', array('email'));
                $localAgent->phone = $this->request->getPost('phone');
                $localAgent->addressOne = $this->request->getPost('address_1', array('string', 'striptags'));
                $localAgent->addressTwo = $this->request->getPost('address_2', array('string', 'striptags'));
                $localAgent->city = $this->request->getPost('city', array('string', 'striptags'));
                $localAgent->stateId = $this->request->getPost('state');
                $localAgent->countryId = $this->request->getPost('country');
                $localAgent->zipCode = $this->request->getPost('zip_code');
                $localAgent->status = $this->request->getPost('status');
                $localAgent->createdOn = \Tourpage\Helpers\Utils::currentDate();
                if ($localAgent->save()) {
                    $messageBody = '<h1>Congradulation! ' . $localAgent->firstName . ' ' . $localAgent->lastName . '</h1>';
                    $messageBody .= 'You have assign as a local agent by below store<br/>';
                    $messageBody .= '----------------------------------------------------------------------<br/>';
                    $messageBody .= '<strong>Store</strong> ' . $this->tag->linkTo(array($this->vendors->getVendorData()->getStorFrontUri(), $this->vendors->getVendorData()->businessName, 'local' => false)) . '<br/>';
                    $messageBody .= '<strong>Operator</strong> ' . $this->vendors->getFullName() . '<br/>';
                    $mail = new \Tourpage\Library\Mail();
                    $mail->setFrom($this->vendors->getEmail());
                    $mail->setTo($localAgent->emailAddress, $localAgent->firstName . ' ' . $localAgent->lastName);
                    $mail->setSubject('Local Agent Assignment');
                    $mail->setBody($messageBody);
                    $mail->send();

                    $this->flash->success('Local agent has been created successfully.');
                    $this->response->redirect('/vendor/agents');
                } else {
                    foreach ($localAgent->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->tag->setTitle('New Local Agents');
        $this->view->form = $vendorLocalAgentForm;
        $this->view->formType = 'new';
        $this->view->pick('agents/form');
    }

    /**
     * Action for edit local agents
     * @param int $agentId
     * @param string $agentTye
     */
    public function editAction($agentId, $agentTye = 'local') {
        if ($agentTye == 'local') {
            $localAgent = \Tourpage\Models\VendorsLocalAgents::findFirst($agentId);
            if (!$localAgent) {
                return FALSE;
            }
            $vendorLocalAgentForm = new \Multiple\Vendor\Forms\VendorsLocalAgentForm($localAgent, array('edit' => TRUE));
            if ($this->request->getPost()) {
                if ($vendorLocalAgentForm->isValid($this->request->getPost())) {
                    $passWord = $this->request->getPost('password', array('string', 'striptags'));
                    $localAgent->commission = $this->request->getPost('commission', 'float');
                    $localAgent->firstName = $this->request->getPost('first_name', array('string', 'striptags'));
                    $localAgent->lastName = $this->request->getPost('last_name', array('string', 'striptags'));
                    $localAgent->emailAddress = $this->request->getPost('email_address', array('email'));
                    $localAgent->phone = $this->request->getPost('phone');
                    $localAgent->addressOne = $this->request->getPost('address_1', array('string', 'striptags'));
                    $localAgent->addressTwo = $this->request->getPost('address_2', array('string', 'striptags'));
                    $localAgent->city = $this->request->getPost('city', array('string', 'striptags'));
                    $localAgent->stateId = $this->request->getPost('state');
                    $localAgent->countryId = $this->request->getPost('country');
                    $localAgent->zipCode = $this->request->getPost('zip_code');
                    $localAgent->status = $this->request->getPost('status');

                    if (!empty($passWord)) {
                        $localAgent->passWord = \Tourpage\Helpers\Utils::encryptPassword($passWord);
                    }
                    if ($localAgent->save()) {
                        $this->flash->success('Local agent has been updated successfully.');
                        $this->response->redirect('/vendor/agents');
                    } else {
                        foreach ($localAgent->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }
                }
            }
            $this->tag->setTitle('Edit Local Agents');
            $this->view->form = $vendorLocalAgentForm;
            $this->view->formType = 'edit';
            $this->view->pick('agents/form');
        }
        if ($agentTye == 'registered') {
            $registeredAgent = \Tourpage\Models\VendorsRegisteredAgents::findFirst($agentId);
            if (!$registeredAgent) {
                return FALSE;
            }
            $vendorRegisteredAgentForm = new \Multiple\Vendor\Forms\VendorsRegisteredAgentForm($registeredAgent, array('edit' => TRUE));
            if ($this->request->getPost()) {
                if ($vendorRegisteredAgentForm->isValid($this->request->getPost())) {
                    $registeredAgent->commission = $this->request->getPost('commission', 'float');
                    $registeredAgent->status = $this->request->getPost('status');
                    $registeredAgent->requestStatus = $this->request->getPost('request_status');
                    if ($registeredAgent->save()) {
                        $this->flash->success('Regustered agent has been updated successfully.');
                        $this->response->redirect('/vendor/agents/registered');
                    } else {
                        foreach ($localAgent->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }
                }
            }
            $this->tag->setTitle('Edit Registered Agents');
            $this->view->form = $vendorRegisteredAgentForm;
            $this->view->registeredAgent = $registeredAgent;
            $this->view->pick('agents/form_registered');
        }
    }

    /**
     * Action for remove local agent
     * @param int $agentId
     * @param string $agentTye
     */
    public function removeAction($agentId, $agentTye = 'local') {
        $redirectUrl = '/vendor/agents';
        if ($agentTye == 'local') {
            $localAgent = \Tourpage\Models\VendorsLocalAgents::findFirst($agentId);
            if (!$localAgent) {
                return FALSE;
            }
            $localAgent->removeData();
            $this->flash->success('Local agent has been removed successfully');
        }
        if ($agentTye == 'registered') {
            $redirectUrl .= '/index/registered';
            $registeredAgent = \Tourpage\Models\VendorsRegisteredAgents::findFirst($agentId);
            if (!$registeredAgent) {
                return FALSE;
            }
            $registeredAgent->member->isAgent = \Tourpage\Models\Members::IS_NOT_AGENT_STATUS_CODE;
            $registeredAgent->member->save();
            $registeredAgent->removeData();
            $this->flash->success('Registered agent has been removed successfully');
        }
        $this->response->redirect($redirectUrl);
    }

    /**
     * Action for view registered agents pending request
     * @param int $page
     */
    public function requestsAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        if ($this->request->isPost()) {
            $requests = $this->request->getPost('rqt');
            $requestAction = $this->request->getPost('request_action');
            if ($requests && count($requests) > 0) {
                foreach ($requests as $request) {
                    $agent = \Tourpage\Models\VendorsRegisteredAgents::findFirst($request);
                    if ($agent && $agent->count() > 0) {
                        switch ($requestAction) {
                            case 'approve':
                                $agent->commission = $agent->vendor->default_commission;
                                $agent->status = \Tourpage\Models\VendorsRegisteredAgents::AGENT_ACTIVE_STATUS_CODE;
                                $agent->requestStatus = \Tourpage\Models\VendorsRegisteredAgents::AGENT_APPROVED_STATUS_CODE;
                                $agent->approveOn = \Tourpage\Helpers\Utils::currentDate();
                                $agent->save();
                                $agent->member->status = \Tourpage\Models\Members::ACTIVE_STATUS_CODE;
                                $agent->member->isAgent = \Tourpage\Models\Members::IS_AGENT_STATUS_CODE;
                                $agent->member->save();
                                break;
                            case 'reject':
                                $agent->requestStatus = \Tourpage\Models\VendorsRegisteredAgents::AGENT_REJECTED_STATUS_CODE;
                                $agent->save();
                                break;
                            case 'remove':
                                $agent->removeData();
                                break;
                        }
                    }
                }
                $this->flash->success('Action has been applied to the selected requests');
            }
            $this->response->redirect('/vendor/agents/requests');
        }
        $agents = \Tourpage\Models\VendorsRegisteredAgents::find(array(
                    'conditions' => 'vendorId = :vendor_id: AND requestStatus = :request_status:',
                    'bind' => array(
                        'vendor_id' => !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId(),
                        'request_status' => \Tourpage\Models\VendorsRegisteredAgents::AGENT_PENDING_STATUS_CODE,
                    ),
                    'order' => 'vragentId DESC'
        ));
        $pager = new \Tourpage\Library\Pager(array(
            'data' => $agents,
            'page' => $page,
        ));
        $this->tag->setTitle('Registered Agents Request');
        $this->view->agents = $pager;
    }

    /**
     * 
     * @param int $agentId
     * @param string $agentTye
     * @param int $page
     */
    public function reportAction($agentId, $agentTye = 'registered', $page = 1) {
        if (!preg_match_all('/[0-9]+/', $agentId, $matches)) {
            return false;
        }
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        if ($agentTye == 'registered') {
            $registeredAgent = \Tourpage\Models\VendorsRegisteredAgents::findFirst($agentId);
            if (!$registeredAgent) {
                return FALSE;
            }
            $earningAmount = 0;
            $bookingCriteria = array(
                'conditions' => 'agentId = :agent_id:',
                'bind' => array(
                    'agent_id' => $registeredAgent->memberId
                )
            );
            //$bookings = \Tourpage\Models\Booking::find($bookingCriteria);

            $modelBind = [];
            $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
            $booking = \Tourpage\Models\BookingTours::query();
            $booking->addwhere("\Tourpage\Models\BookingTours.vendorId = :vendor_id:");
            $modelBind['vendor_id'] = $vendorId;
            $booking->join('\Tourpage\Models\Booking', 'b.bookingId = \Tourpage\Models\BookingTours.bookingId', 'b');
            $booking->addWhere("b.agentId = :bookingAgent:");
            $modelBind['bookingAgent'] = $registeredAgent->memberId;
            if (count($modelBind) > 0) {
                $booking->bind($modelBind);
            }
            $booking->order("\Tourpage\Models\BookingTours.bookingId DESC");
            $booking->groupBy("\Tourpage\Models\BookingTours.bookingId");
            $bookings = $booking->execute();
            $pager = new \Tourpage\Library\Pager(array(
                'data' => $bookings,
                'page' => $page,
                    //'limit' => 2,
            ));
            $pager->setUriPattern('/vendor/agents/report/' . $agentId . '/' . $agentTye . '/{page}');
            if ($bookings && $bookings->count() > 0) {
                foreach ($bookings as $booking) {
                    $earningAmount += ($booking->booking->bookingAmount * $registeredAgent->commission) / 100;
                }
            }

            $this->tag->setTitle('Registered Agents Report');
            $this->view->registeredAgent = $registeredAgent;
            $this->view->bookings = $pager;
            $this->view->earningAmount = $earningAmount;
            $this->view->pick('agents/report_registered');
        }
    }

}
