<?php

namespace Multiple\Vendor\Controllers;

/**
 * Description of GroupsController
 * @author 
 */
class GroupsController extends VendorController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index action for Booking Controller
     */
    public function indexAction($page = 1,$id='') {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $defaultValues = [];
        $modelBind = [];
		//$listForm = new \Multiple\Vendor\Forms\ListForm();
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        if ($this->request->isPost()) {
            $queryString = '';
            $groupName = $this->request->getPost('group_name');
            $redirectTo = $this->url->get('/vendor/groups');
            if ($groupName != '') {
                $queryString .= 'grp=' . urlencode($groupName) . '&';
            }
            if ($queryString) {
                $queryString = ((substr($queryString, -1) == '&') ? substr($queryString, 0, -1) : $queryString);
                $redirectTo = $redirectTo . '?' . $queryString;
            }
            $this->response->redirect($redirectTo);
        }
        $group = \Tourpage\Models\GroupsVendors::query();
        $group->where("\Tourpage\Models\GroupsVendors.vendorId = :vendor_id:");
        $modelBind['vendor_id'] = $vendorId;
        if ($this->request->hasQuery('grp')) {
            $defaultValues['grp'] = urldecode($this->request->getQuery('grp'));
            $modelBind['groupName'] = $defaultValues['grp'];
            $group->join('\Tourpage\Models\Groups', 'bgrp.groupId = \Tourpage\Models\GroupsVendors.groupId', 'bgrp');
            $group->addWhere("bgrp.groupName = :groupName:");
        }
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        if (count($modelBind) > 0) {
            $group->bind($modelBind);
        }

        $group->order("\Tourpage\Models\GroupsVendors.groupVendorsId DESC");
        $group->groupBy("\Tourpage\Models\GroupsVendors.groupVendorsId");
        $groups = $group->execute();

        $pager = new \Tourpage\Library\Pager(array(
            "data" => $groups,
            "page" => $page,
        ));
       
		$this->view->vendorId = $vendorId;
        $this->tag->setTitle('Groups');
        $this->view->setVars(array(
            'pager' => $pager,
            'defaultValues' => $defaultValues
        ));
       //$this->tag->setTitle('Manage Group');
       // if ($this->request->isPost()){
            //$submitType = $this->request->getPost('submit');
            /*if ($groupForm->isValid($this->request->getPost())) {
                $group=\Tourpage\Models\Groups::findFirstByGroupId($id->groupId);
				 $group->groupDisplayOrder=$this->request->getPost('','');	  
					if(){}  
					  //$group->groupName=$this->request->getPost("group_name");
				      $group->groupDisplayOrder=$this->request->getPost("group_order");
					  $group->groupDisplay=$this->request->getPost("group_display");
			}*/
		//}
   //$this->view->form = $listForm;
    }
	 public function addAction() {
				$this->tag->setTitle('Create New Group');
				$groupForm = new \Multiple\Vendor\Forms\GroupForm();
				if ($this->request->isPost()){
					$submitType = $this->request->getPost('submit');
					if ($groupForm->isValid($this->request->getPost())) {
						
				      $group= new \Tourpage\Models\Groups();
					  $group->groupName=$this->request->getPost("group_name");
				      $group->groupDisplayOrder=$this->request->getPost("group_order");
					  $group->groupDisplay=$this->request->getPost("group_display");
					  
					  if($group->save()){
						$vendorGroup = new \Tourpage\Models\GroupsVendors();
                        $vendorGroup->vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
                        $vendorGroup->groupId = $group->groupId;
						if($vendorGroup->save()){
						  $this->flash->success("Group added successfuly.");
						     $this->response->redirect('/vendor/groups');
							 } else {
                        foreach ($vendorGroup->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
						}
                    } else {
                        foreach ($group->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }


} else {
                        foreach ($groupForm->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }
	}
	$this->view->form = $groupForm;
}
	 public function mapAction($page = 1) {					  
		if (!preg_match_all('/[0-9]+/', $page, $matches)) {
			return false;
		}
		$defaultValues = [];
		$modelBind = [];
		$vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->                    vendors->getId();
		$tour = \Tourpage\Models\VendorsTours::query();
		$tour->where("\Tourpage\Models\VendorsTours.vendorId = :vendor_id:");
		$modelBind['vendor_id'] = $vendorId;
		if (count($defaultValues) > 0) {
			\Phalcon\Tag::setDefaults($defaultValues);
		}
		if (count($modelBind) > 0) {
			$tour->bind($modelBind);
		}
		
		$tour->order("\Tourpage\Models\VendorsTours.vendorTourId DESC");
		$tour->groupBy("\Tourpage\Models\VendorsTours.vendorTourId");
		$tours = $tour->execute();
		$this->view->toursData = $this->getToursData($tours);
		/*
		$pager = new \Tourpage\Library\Pager(array(
			"data" => $tours,
			"page" => $page,
		));
		*/
		$this->tag->setTitle('Tours');
		$this->view->vendorId = $vendorId;
		$this->view->setVars(array(
			//'pager' => $pager,
			'defaultValues' => $defaultValues
		));
		$this->tag->setTitle('Map Tour');
		$mapForm = new \Multiple\Vendor\Forms\MapForm();      
		if ($this->request->isAjax()){
			 		$groupId=$this->request->getPost('group_name');
			 		$items = explode(',',$this->request->getPost('items')); 
			 		$val = 1;
			  		foreach($items as $item){
						$item = substr($item,strpos($item,'_')+1);
							$grpSql = 'vendorId='.$vendorId.' AND tourId="'.$item.'" AND groupId='.$groupId; 
							$tGroup = \Tourpage\Models\GroupsTours::findFirst($grpSql);
							$trGroup = new \Tourpage\Models\GroupsTours();
							//var_dump($item);
							if($tGroup){
								$tGroup->groupMapOrder = $val;
								$tGroup->update();
							} else{
								$trGroup->groupId = $groupId;
								$trGroup->tourId = $item;
								$trGroup->vendorId = $vendorId;
								$trGroup->groupMapOrder = $val;
								$trGroup->save();
							}						
							$val++; 
					}
			
		}       
		$this->view->vendorId = $vendorId;
		$this->view->groupTours = $groupTours;
	    $this->view->form = $mapForm;
	}
	
	private function getToursData($result)
	{
		$data = [];
		foreach ($result as $item) {
			$groupTour = \Tourpage\Models\GroupsTours::find([
				'conditions' => 'tourId = :tour_id: AND vendorId = :vendor_id:',
				'bind' => ['tour_id' => $item->tourId, 'vendor_id' => $item->vendorId]
			]);
			// exclude the group tours that are already mapped
			if ($groupTour->count() > 0) {
				continue;
			} else {
				$data[] = $item;
			}
		}
		return $data;
	}

	public function listAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $defaultValues = [];
        $modelBind = [];
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
		if ($this->request->isPost()) {
            $queryString = '';
            $tourName = $this->request->getPost('tour_name');
            $redirectTo = $this->url->get('/vendor/groups/list');
            if ($tourName != '') {
                $queryString .= 'trp=' . urlencode($tourName) . '&';
            }
            if ($queryString) {
                $queryString = ((substr($queryString, -1) == '&') ? substr($queryString, 0, -1) : $queryString);
                $redirectTo = $redirectTo . '?' . $queryString;
            }
            $this->response->redirect($redirectTo);
        }
        $group = \Tourpage\Models\GroupsTours::query();
        $group->where("\Tourpage\Models\GroupsTours.vendorId = :vendor_id:");
        $modelBind['vendor_id'] = $vendorId;
		if ($this->request->hasQuery('trp')) {
            $defaultValues['trp'] = urldecode($this->request->getQuery('trp'));
            $modelBind['tourTitle'] = $defaultValues['trp'];
            $group->join('\Tourpage\Models\Tours', 'btrp.tourId = \Tourpage\Models\GroupsTours.tourId', 'btrp');
            $group->addWhere("btrp.tourTitle = :tourTitle:");
        }
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        if (count($modelBind) > 0) {
            $group->bind($modelBind);
        }

        $group->order("\Tourpage\Models\GroupsTours.groupToursId DESC");
        $group->groupBy("\Tourpage\Models\GroupsTours.groupToursId");
        $groups = $group->execute();

        $pager = new \Tourpage\Library\Pager(array(
            "data" => $groups,
            "page" => $page,
        ));
        $this->tag->setTitle('Map Tour List');
        $this->view->setVars(array(
            'pager' => $pager,
            'defaultValues' => $defaultValues
        ));
    }



  public function orderAction($page = 1) {
      
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $defaultValues = [];
        $modelBind = [];
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
		
        $group = \Tourpage\Models\GroupsVendors::query();
        $group->where("\Tourpage\Models\GroupsVendors.vendorId = :vendor_id:");
        $modelBind['vendor_id'] = $vendorId;
		
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        if (count($modelBind) > 0) {
            $group->bind($modelBind);
        }

        $group->order("\Tourpage\Models\GroupsVendors.groupVendorsOrder");
        $group->groupBy("\Tourpage\Models\GroupsVendors.groupVendorsId");
        $groups = $group->execute();
        //print_r($groups);die("kk");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $groups,
            "page" => $page,
        ));
        
         
        $this->tag->setTitle('Group Order List');
        $this->view->setVars(array(
            'pager' => $pager,
            'defaultValues' => $defaultValues
        ));
		if ($this->request->isPost()){
			  $sdata = explode(',',$this->request->getPost('sdata'));
			  $usdata = explode(',',$this->request->getPost('usdata'));
			  //$redirectTo = $this->url->get('/vendor/groups/order');
			  $val = 1;
			  $flag = 0;
			  if (count($sdata) > 1) {
				foreach($sdata as $data){
					if (!empty($data)) {
						$data = substr($data,strpos($data,"_")+1);
						$groupSql = 'vendorId = "' . $vendorId . '" AND groupId = "' . $data . '"';
						$group_order = \Tourpage\Models\GroupsVendors::findFirst($groupSql);
						$group_order->groupVendorsDisplay='Y';
						$group_order->groupVendorsOrder = $val;
						$group_order->update();
					}
					$flag = 1;
					$val++;
				}
			  }
			  if (count($usdata) > 1) {
				foreach($usdata as $udata){
					if (!empty($udata)) {
						$udata = substr($udata,strpos($udata,"_")+1);
						$ugroupSql = 'vendorId = "' . $vendorId . '" AND groupId = "' . $udata . '"';
						$group_order = \Tourpage\Models\GroupsVendors::findFirst($ugroupSql);
						$group_order->groupVendorsDisplay='N';
						$group_order->groupVendorsOrder = 0;
						$group_order->update();
					}
					$flag = 1;
				}
			  }
			  if($flag == 1){
				  $this->flash->success("Group Order success."); 
			  } else{
				  foreach ($group_order->getMessages() as $message) {
					  $this->flash->error((string) $message);
                  }
			  }
			  exit();
			  //return $this->response->redirect($this->url->get('/vendor/groups/order'));
		  }
    }
/**
     * Action for group delete
     * @param string $groupId
     * @return boolean
     */
    public function removeAction($groupId = '',$groupstatus= '') {
        $group = \Tourpage\Models\Groups::findFirstByGroupId($groupId);
        if (!$group) {
            return false;
        }
        $this->tag->setTitle('Remove Group');
        if ($this->request->isPost()) {
            $key = $this->request->getPost('key');
            if ($key == md5('confirm')) {
                if($groupstatus == '1'){
                $group->status = '2';
                }
                if($groupstatus == '2'){
                $group->status = '1';
                }
        if ($group->update()) {                
                   $this->flash->success("Group has been updated successfuly.");
                    $this->response->redirect('/vendor/groups');
                }
            }
        }
        $this->view->group = $group;
    }
	
	public function removemappedtourAction($groupToursId = 0)
	{
		$groupTours = \Tourpage\Models\GroupsTours::findFirstByGroupToursId($groupToursId);
		$groupTours->delete();
        $this->flash->success("Mapped tour has been deleted successfuly.");
        $this->response->redirect('/vendor/groups/list');
    }

}


 
