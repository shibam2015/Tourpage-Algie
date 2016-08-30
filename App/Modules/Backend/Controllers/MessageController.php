<?php

namespace Multiple\Backend\Controllers;

/**
 * MessageController Class
 * @author satya
 */
class MessageController extends BackendController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index action for list messages
     */
    /*public function indexAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $defaultValues = [];
        if ($this->request->isPost()) {
            $formType = $this->request->getPost('formtp');
            switch ($formType) {
                case 'fltr':
                    $queryString = '';
                    $memberName = $this->request->getPost('mn');
                    $memberCity = $this->request->getPost('ct');
                    $memberState = $this->request->getPost('p');
                    $memberCountry = $this->request->getPost('c');
                    $memberStatus = $this->request->getPost('s');
                    $redirectTo = $this->url->get('/admin/customers');
                    if ($memberName != '') {
                        $queryString .= 'mn=' . urlencode($memberName) . '&';
                    }
                    if ($memberCity != '') {
                        $queryString .= 'ct=' . urlencode($memberCity) . '&';
                    }
                    if ($memberState != '') {
                        if ($memberState != '[all]') {
                            $queryString .= 'p=' . $memberState . '&';
                        }
                    }
                    if ($memberCountry != '') {
                        if ($memberCountry != '[all]') {
                            $queryString .= 'c=' . $memberCountry . '&';
                        }
                    }
                    if ($memberStatus != '') {
                        if ($memberStatus != '[all]') {
                            $queryString .= 's=' . $memberStatus . '&';
                        }
                    }
                    if ($queryString) {
                        $queryString = ((substr($queryString, -1) == '&') ? substr($queryString, 0, -1) : $queryString);
                        $redirectTo = $redirectTo . '?' . $queryString;
                    }
                    $this->response->redirect($redirectTo);
                    break;
            }
        }
        $this->tag->prependTitle('Customers');
        $members = \Tourpage\Models\Members::query();
        if ($this->request->hasQuery('mn')) {
            $defaultValues['mn'] = trim(urldecode($this->request->getQuery('mn')));
            $mn = explode(' ', $defaultValues['mn']);
            if (count($mn) > 0) {
                $counter = 0;
                foreach ($mn as $n) {
                    $members->orWhere("firstName LIKE :fname_" . $counter . ":", array('fname_' . $counter => "%" . $n . "%"));
                    $members->orWhere("lastName LIKE :lname_" . $counter . ":", array('lname_' . $counter => "%" . $n . "%"));
                    $counter++;
                }
            }
        }
        if ($this->request->hasQuery('ct')) {
            $defaultValues['ct'] = trim(urldecode($this->request->getQuery('ct')));
            $members->andWhere("city = :city:", array('city' => $defaultValues['ct']));
        }
        if ($this->request->hasQuery('p')) {
            $defaultValues['p'] = $this->request->getQuery('p');
            $members->andWhere("stateId = :sid:", array('sid' => $defaultValues['p']));
        }
        if ($this->request->hasQuery('c')) {
            $defaultValues['c'] = $this->request->getQuery('c');
            $members->andWhere("countryId = :cid:", array('cid' => $defaultValues['c']));
        }
        if ($this->request->hasQuery('s')) {
            $defaultValues['s'] = $this->request->getQuery('s');
            $members->andWhere("status = :status:", array('status' => $defaultValues['s']));
        }
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        $members->order("memberId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $members->execute(),
            "page" => $page,
        ));
        $this->view->defaultValues = $defaultValues;
        $this->view->pager = $pager;
    }*/

	/**
     * Action for list messages
     * @param string $action Type of Action -- list/create/edit/remove
     * @param int $page Pagination page index
     * @param int $messageId
     * @return boolean
     */
    public function indexAction($action = 'list', $msgType = 'vendor', $messageId = 0, $page = 1) {
        if ($action == 'list') {
            if (!preg_match_all('/[0-9]+/', $page, $matches)) {
                return false;
            }
            $defaultValues = [];
			$defaultValues2 = [];
            if ($this->request->isPost()) {
                $formType = $this->request->getPost('formmsg');
                switch ($formType) {
                    case 'fltr':
                        $queryString = '';
                        $messageStatus = $this->request->getPost('s');
                        $redirectTo = $this->url->get('/admin/message/index');
                        if ($messageStatus != '') {
                            if ($messageStatus != '[all]') {
                                $queryString .= 's=' . $messageStatus . '&';
                            }
                        }
                        if ($queryString) {
                            $queryString = ((substr($queryString, -1) == '&') ? substr($queryString, 0, -1) : $queryString);
                            $redirectTo = $redirectTo . '?' . $queryString;
                        }
                        $this->response->redirect($redirectTo);
                        break;
                }
            }
            $messages = \Tourpage\Models\MembersMessages::query();
            if ($this->request->hasQuery('s')) {
                $defaultValues['s'] = $this->request->getQuery('s');
                $messages->andWhere("memberMessageStatus = :status:", array('status' => $defaultValues['s']));
            }
            if (count($defaultValues) > 0) {
                \Phalcon\Tag::setDefaults($defaultValues);
            }
            $messages->order("memberMessageId DESC");
            $pager = new \Tourpage\Library\Pager(array(
                "data" => $messages->execute(),
                "page" => $page,
            ));
			$vendors = \Tourpage\Models\VendorsMessages::query();
            if ($this->request->hasQuery('st')) {
                $defaultValues2['st'] = $this->request->getQuery('st');
                $vendors->andWhere("vendorMessageStatus = :status:", array('status' => $defaultValues2['st']));
            }
            if (count($defaultValues2) > 0) {
                \Phalcon\Tag::setDefaults($defaultValues2);
            }
            $vendors->order("vendorMessageId DESC");
            $pager2 = new \Tourpage\Library\Pager(array(
                "data" => $vendors->execute(),
                "page" => $page,
            ));
            $pager->setUriPattern('/admin/message/list/{page}');
            $this->view->defaultValues = $defaultValues;
			$this->view->defaultValues2 = $defaultValues2;
            $this->view->pager = $pager;
			$this->view->pager2 = $pager2;
            $this->tag->prependTitle('Messages');
        }
        if ($action == 'create') {
			if($msgType == 'member'){
				$memberMessageForm = new \Multiple\Backend\Forms\MembersMessagesForm();
			} else if($msgType == 'vendor'){
				$vendorMessageForm = new \Multiple\Backend\Forms\VendorsMessagesForm();
			}
            if ($this->request->isPost()) {
			if($msgType == 'member'){
			  if ($memberMessageForm->isValid($this->request->getPost())) {
				  $messageText = $this->request->getPost('message_text');
				  $memberId = $this->request->getPost('member');
				  $message = new \Tourpage\Models\Messages();
				  $message->messageText = $messageText;
				  if ($message->save()) {
				  $membersMessages = new \Tourpage\Models\MembersMessages();
				  $membersMessages->messageId = $message->messageId;
				  $membersMessages->memberId = $memberId;
				  $membersMessages->memberMessageStatus = \Tourpage\Models\MembersMessages::UNREAD_STATUS_CODE;
				  if ($membersMessages->save()) {
					  $this->flash->success('Message Posted Successfully.');
					  $this->response->redirect('/admin/message/index');
				  } else {
					  foreach ($membersMessages->getMessages() as $messages) {
						  $this->flash->error((string) $messages);
					  }
				  }
				  } else {
					  foreach ($message->getMessages() as $messages) {
						  $this->flash->error((string) $messages);
					  }
				  }
			  }
			  } else if($msgType == 'vendor'){
			  if ($vendorMessageForm->isValid($this->request->getPost())) {
				  $messageText = $this->request->getPost('message_text');
				  $vendorId = $this->request->getPost('vendor');
				  $message = new \Tourpage\Models\Messages();
				  $message->messageText = $messageText;
				  if ($message->save()) {
				  $vendorsMessages = new \Tourpage\Models\VendorsMessages();
				  $vendorsMessages->messageId = $message->messageId;
				  $vendorsMessages->vendorId = $vendorId;
				  $vendorsMessages->vendorMessageStatus = \Tourpage\Models\VendorsMessages::UNREAD_STATUS_CODE;
				  if ($vendorsMessages->save()) {
					  $this->flash->success('Message Posted Successfully.');
					  $this->response->redirect('/admin/message/index');
				  } else {
					  foreach ($vendorsMessages->getMessages() as $messages) {
						  $this->flash->error((string) $messages);
					  }
				  }
				  } else {
					  foreach ($message->getMessages() as $messages) {
						  $this->flash->error((string) $messages);
					  }
				  }
			  }
			  }
            }
			if($msgType == 'member'){
				$this->view->form = $memberMessageForm;
				$this->view->formType = 'new';
				$this->tag->prependTitle('New Message');
				$this->view->pick('message/memberMessageForm');
			} else if($msgType == 'vendor'){
				$this->view->form = $vendorMessageForm;
			 	$this->view->formType = 'new';
			 	$this->tag->prependTitle('New Message');
			 	$this->view->pick('message/vendorMessageForm');
			}
        }
        if ($action == 'remove') {
			if($msgType == 'member'){
            	$message = \Tourpage\Models\MembersMessages::findFirst(array(
                	'messageId = :message_id:',
                	'bind' => array('message_id' => $messageId),
                ));
			} else if($msgType == 'vendor'){
				$message = \Tourpage\Models\VendorsMessages::findFirst(array(
                	'messageId = :message_id:',
                	'bind' => array('message_id' => $messageId),
                ));
			}
            if (!$message) {
                return false;
            }
            if ($this->request->isPost()) {
                $key = $this->request->getPost('key');
                if ($key == md5('confirm')) {
                    if ($message->message->removeData()) {
                        $this->flash->success("Message has been removed successfuly.");
                        $this->response->redirect('/admin/message/index');
                    }
                }
            }
            $this->view->message = $message;
            $this->tag->prependTitle('Remove Message');
            $this->view->pick('message/removeMessage');
        }
    }

}
