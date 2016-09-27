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
 * Description of VendorsController
 * @author amit
 */
class VendorsController extends BackendController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Action to list all vendors
     */
    public function indexAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $defaultValues = [];
        if ($this->request->isPost()) {
            $formType = $this->request->getPost('formtp');
            switch ($formType) {
                case 'fltr':
                    $queryString = '';
                    $vendorName = $this->request->getPost('vn');
                    $storeFront = $this->request->getPost('sf');
                    $vendorCountry = $this->request->getPost('c');
                    $vendorState = $this->request->getPost('p');
                    $vendorStatus = $this->request->getPost('s');
                    $redirectTo = $this->url->getBaseUri() . '/admin/vendors';
                    if ($vendorName != '') {
                        $queryString .= 'vn=' . urlencode($vendorName) . '&';
                    }
                    if ($storeFront != '') {
                        $queryString .= 'sf=' . $storeFront . '&';
                    }
                    if ($vendorCountry != '') {
                        if ($vendorCountry != '[all]') {
                            $queryString .= 'c=' . $vendorCountry . '&';
                        }
                    }
                    if ($vendorState != '') {
                        if ($vendorState != '[all]') {
                            $queryString .= 'p=' . $vendorState . '&';
                        }
                    }
                    if ($vendorStatus != '') {
                        if ($vendorStatus != '[all]') {
                            $queryString .= 's=' . $vendorStatus . '&';
                        }
                    }
                    if ($queryString) {
                        $queryString = ((substr($queryString, -1) == '&') ? substr($queryString, 0, -1) : $queryString);
                        $redirectTo = $redirectTo . '?' . $queryString;
                    }
                    $this->response->redirect($redirectTo);
                    break;
                case 'actn':
                    $vendorIds = $this->request->getPost('v');
                    $actionType = $this->request->getPost('acttp');
                    if ($vendorIds && count($vendorIds) > 0) {
                        foreach ($vendorIds as $vid) {
                            $vendorModelData = \Tourpage\Models\Vendors::findFirst($vid);
                            switch ($actionType) {
                                case 'active':
                                    $vendorModelData->status = \Tourpage\Models\Vendors::ACTIVE_STATUS_CODE;
                                    //$vendorModelData->save();
                                    break;
                                case 'inactive':
                                    $vendorModelData->status = \Tourpage\Models\Vendors::INACTIVE_STATUS_CODE;
                                    //$vendorModelData->save();
                                    break;
                                case 'remove':
                                    //$vendorModelData->removeData();
                                    break;
                            }
                        }
                    }
                    $this->response->redirect($this->router->getRewriteUri());
                    break;
            }
        }
        $this->tag->prependTitle('Vendors');
        $vendors = \Tourpage\Models\Vendors::query();
        $vendors->where('parentId = 0');
        if ($this->request->hasQuery('vn')) {
            $defaultValues['vn'] = trim(urldecode($this->request->getQuery('vn')));
            $mn = explode(' ', $defaultValues['vn']);
            if (count($mn) > 0) {
                $counter = 0;
                foreach ($mn as $n) {
                    $vendors->orWhere("firstName LIKE :fname_" . $counter . ":", array('fname_' . $counter => "%" . $n . "%"));
                    $vendors->orWhere("lastName LIKE :lname_" . $counter . ":", array('lname_' . $counter => "%" . $n . "%"));
                    $counter++;
                }
            }
        }
        if ($this->request->hasQuery('sf')) {
            $defaultValues['sf'] = $this->request->getQuery('sf');
            $vendors->andWhere("businessName LIKE :bname:", array('bname' => "%" . $defaultValues['sf'] . "%"));
        }
        if ($this->request->hasQuery('c')) {
            $defaultValues['c'] = $this->request->getQuery('c');
            $vendors->andWhere("countryId = :cid:", array('cid' => $defaultValues['c']));
        }
        if ($this->request->hasQuery('p')) {
            $defaultValues['p'] = $this->request->getQuery('p');
            $vendors->andWhere("stateId = :sid:", array('sid' => $defaultValues['p']));
        }
        if ($this->request->hasQuery('s')) {
            $defaultValues['s'] = $this->request->getQuery('s');
            $vendors->andWhere("status = :status:", array('status' => $defaultValues['s']));
        }
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        $vendors->order("vendorId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $vendors->execute(),
            "page" => $page,
        ));
        $this->view->defaultValues = $defaultValues;
        $this->view->pager = $pager;
    }

    /**
     * Action for create new vendor
     */
    public function createAction() {
        /* $form = new \Multiple\Backend\Forms\VendorForm(NULL, array('form_type' => 'new'));
          if ($this->request->isPost()) {
          if ($form->isValid($this->request->getPost())) {

          }
          }
          $this->tag->prependTitle('New Vendor');
          $this->view->formType = 'new';
          $this->view->form = $form;
          $this->view->pick('vendors/form'); */
        return false;
    }

    /**
     * Action for view vendor detils
     * @param int $vendorId
     */
    public function viewAction($vendorId = 0) {
        if (!preg_match_all('/[0-9]+/', $vendorId) || $vendorId == 0) {
            return FALSE;
        }
        $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
        if (!$vendor) {
            return FALSE;
        }
        $this->tag->prependTitle('Vendor Details');
        $this->view->vendor = $vendor;
    }

    /**
     * Edit Vendor action
     * @param int $vendorId
     * @return boolean
     */
    public function editAction($vendorId = 0) {
        if (!preg_match_all('/[0-9]+/', $vendorId) || $vendorId == 0) {
            return FALSE;
        }
        $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
        if (!$vendor) {
            return FALSE;
        }
        $form = new \Multiple\Backend\Forms\VendorForm($vendor, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $vendor->firstName = $this->request->getPost('vendor_first_name', array('string', 'striptags'));
                $vendor->lastName = $this->request->getPost('vendor_last_name', array('string', 'striptags'));
                $vendor->jobTitle = $this->request->getPost('vendor_job_title', array('string', 'striptags'));
                $vendor->businessName = $this->request->getPost('vendor_business_name', array('string', 'striptags'));
                $vendor->phone = $this->request->getPost('vendor_phone');
                $vendor->city = $this->request->getPost('vendor_city');
                $vendor->stateId = $this->request->getPost('vendor_state');
                $vendor->countryId = $this->request->getPost('vendor_country');
                $vendor->vendorCategory = $this->request->getPost('vendor_category');
                $vendor->isTripAdv = $this->request->getPost('vendor_is_trip_advisor');
                $vendor->tripAdvLink = $this->request->getPost('vendor_trip_advisor_link');
                $vendor->status = $this->request->getPost('vendor_status');

                $newPassword = $this->request->getPost('vendor_password');
                $newRePassword = $this->request->getPost('vendor_re_password');
                if (!empty($newPassword)) {
                    if (strcmp($newPassword, $newRePassword) == 0) {
                        $vendors->passWord = \Tourpage\Helpers\Utils::encryptPassword($newPassword);
                    } else {
                        $this->flash->error((string) 'Retype new password correctly');
                    }
                }
                if ($vendor->save()) {
                    $vendor_tour_activity_type = $this->request->getPost('vendor_tour_activity_type');
                    if (count($vendor_tour_activity_type) > 0) {
                        $extVendorTourTypes = \Tourpage\Models\VendorsTourTypes::find(array(
                                    'vendorId = :vendor_id:',
                                    'bind' => array('vendor_id' => $vendor->vendorId)
                        ));
                        if ($extVendorTourTypes->count() > 0) {
                            $extVendorTourTypes->delete();
                        }
                        foreach ($vendor_tour_activity_type as $vtat) {
                            $vendorTourTypes = new \Tourpage\Models\VendorsTourTypes();
                            $vendorTourTypes->vendorId = $vendor->vendorId;
                            $vendorTourTypes->tourTypesId = $vtat;
                            $vendorTourTypes->save();
                        }
                        $this->flash->success("Vendor has been updated successfuly.");
                        //$this->response->redirect($this->router->getRewriteUri());
                        $this->response->redirect('/admin/vendors');
                    } else {
                        $this->flash->error((string) 'Tour & activities Type is required');
                    }
                }
            }
        }
        $this->tag->prependTitle('Edit Vendor');
        $this->view->formType = 'edit';
        $this->view->vendor = $vendor;
        $this->view->form = $form;
        $this->view->pick('vendors/form');
    }

    /**
     * Remove vendor action
     * @param int $vendorId
     */
    public function removeAction($vendorId = 0) {
        if (!preg_match_all('/[0-9]+/', $vendorId) || $vendorId == 0) {
            return FALSE;
        }
        $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
        if (!$vendor) {
            return FALSE;
        }
        if ($this->request->isPost()) {
            $key = $this->request->getPost('key');
            if ($key == md5('confirm')) {
                if ($vendor->removeData()) {
                    $this->flash->success("Vendor has been removed successfuly.");
                    $this->response->redirect('/admin/vendors');
                }
            }
        }
        $this->tag->prependTitle('Remove Vendor');
        $this->view->vendor = $vendor;
    }

    /**
     * Action to list vendor category
     * @param int $page
     * @return boolean
     */
    public function categoryAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $this->tag->prependTitle('Vendor Category');
        $category = \Tourpage\Models\CategoryVendor::find(array(
                    'order' => 'categoryId DESC'
        ));
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $category,
            "page" => $page,
        ));
        $this->view->pager = $pager;
    }

    /**
     * Action for add new vendor category and sub category
     */
    public function addcategoryAction() {
        $this->tag->prependTitle('New Category');
        $vendorCategoryForm = new \Multiple\Backend\Forms\VendorCategoryForm();
        if ($this->request->isPost()) {
            if ($vendorCategoryForm->isValid($this->request->getPost())) {
                $category = new \Tourpage\Models\CategoryVendor();
                $category->categoryParentId = 0;
                if ($this->request->getPost('category_parent')) {
                    $category->categoryParentId = $this->request->getPost('category_parent');
                }
                $category->categoryTitle = $this->request->getPost('category_title');
                $category->categorySlug = \Tourpage\Helpers\Utils::slug($category->categoryTitle);
                $category->categoryStatus = $this->request->getPost('category_status');
                $category->categoryCreatedOn = \Tourpage\Helpers\Utils::currentDate();
                if ($category->save()) {
                    $this->flash->success("Category has been added successfuly.");
                    $this->response->redirect('/admin/vendors/category');
                } else {
                    foreach ($category->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->view->form = $vendorCategoryForm;
        $this->view->categoryId = 0;
        $this->view->pick('vendors/categoryform');
    }

    /**
     * Edit vendor category and sub category
     * @param int $categoryId
     * @return boolean
     */
    public function editcategoryAction($categoryId = '') {
        if (!preg_match_all('/[0-9]+/', $categoryId, $matches)) {
            return false;
        }
        $vendorCategory = \Tourpage\Models\CategoryVendor::findFirst($categoryId);
        if (!$vendorCategory) {
            return FALSE;
        }
        $vendorCategoryForm = new \Multiple\Backend\Forms\VendorCategoryForm($vendorCategory, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($vendorCategoryForm->isValid($this->request->getPost())) {
                $vendorCategory->categoryParentId = 0;
                if ($this->request->getPost('category_parent')) {
                    $vendorCategory->categoryParentId = $this->request->getPost('category_parent');
                }
                $vendorCategory->categoryTitle = $this->request->getPost('category_title');
                $vendorCategory->categorySlug = \Tourpage\Helpers\Utils::slug($vendorCategory->categoryTitle);
                $vendorCategory->categorySummery = $this->request->getPost('category_summery');
                $vendorCategory->categoryStatus = $this->request->getPost('category_status');
                if ($vendorCategory->save()) {
                    $this->flash->success("Category has been updated successfuly.");
                    $this->response->redirect('/admin/vendors/category');
                } else {
                    foreach ($vendorCategory->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->tag->prependTitle('Edit Category');
        $this->view->form = $vendorCategoryForm;
        $this->view->categoryId = $categoryId;
        $this->view->pick('vendors/categoryform');
    }

    /**
     * Remove vendor category and sub category
     * @param int $categoryId
     * @return boolean
     */
    public function removecategoryAction($categoryId = '') {
        if (!preg_match_all('/[0-9]+/', $categoryId, $matches)) {
            return false;
        }
        $vendorCategory = \Tourpage\Models\CategoryVendor::findFirst($categoryId);
        if (!$vendorCategory) {
            return FALSE;
        }
        if ($this->request->isPost()) {
            if ($vendorCategory->removeData()) {
                $this->flash->success("Category has been removed successfuly.");
                $this->response->redirect('/admin/vendors/category');
            } else {
                foreach ($vendorCategory->getMessages() as $message) {
                    $this->flash->error((string) $message);
                }
            }
        }
        $this->tag->prependTitle('Remove Category');
        $this->view->category = $vendorCategory;
    }

    /**
     * Action for list vendors tour and activity type
     */
    public function touractivitiesAction() {
        $this->tag->prependTitle('Tour & Activities');
        $tourTypes = \Tourpage\Models\TourTypes::find(array(
                    'order' => 'tourTypesId DESC'
        ));
        $this->view->tourTypes = $tourTypes;
    }

    /**
     * Action for add new tour and activeity type
     */
    public function addtouractivitiesAction() {
        $form = new \Multiple\Backend\Forms\TourTypesForm();
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $tourType = new \Tourpage\Models\TourTypes();
                $tourType->tourTypesTitle = $this->request->getPost('tour_type_title', array('string', 'striptags'));
                $tourType->tourTypesStatus = $this->request->getPost('tour_type_status');
                if ($tourType->save()) {
                    $this->flash->success("Tour & Activities has been added successfuly.");
                    $this->response->redirect('/admin/vendors/touractivities');
                } else {
                    foreach ($tourType->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->tag->prependTitle('New Tour & Activities');
        $this->view->formType = 'new';
        $this->view->form = $form;
        $this->view->pick('vendors/touractivityform');
    }

    /**
     * Edit vendor tour and activity type
     * @param int $tourTypesId
     * @return boolean
     */
    public function edittouractivitiesAction($tourTypesId = '') {
        if (!preg_match_all('/[0-9]+/', $tourTypesId, $matches)) {
            return false;
        }
        $tourType = \Tourpage\Models\TourTypes::findFirst($tourTypesId);
        if (!$tourType) {
            return FALSE;
        }
        $form = new \Multiple\Backend\Forms\TourTypesForm($tourType, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $tourType->tourTypesTitle = $this->request->getPost('tour_type_title', array('string', 'striptags'));
                $tourType->tourTypesStatus = $this->request->getPost('tour_type_status');
                if ($tourType->save()) {
                    $this->flash->success("Tour & Activities has been updated successfuly.");
                    $this->response->redirect('/admin/vendors/touractivities');
                } else {
                    foreach ($tourType->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->tag->prependTitle('Edit Tour & Activities');
        $this->view->formType = 'edit';
        $this->view->form = $form;
        $this->view->pick('vendors/touractivityform');
    }

    /**
     * Remove vendor tour and activity type
     * @param int $tourTypesId
     * @return boolean
     */
    public function removetouractivitiesAction($tourTypesId = '') {
        if (!preg_match_all('/[0-9]+/', $tourTypesId, $matches)) {
            return false;
        }
        $tourType = \Tourpage\Models\TourTypes::findFirst($tourTypesId);
        if (!$tourType) {
            return FALSE;
        }
        if ($this->request->isPost()) {
            $key = $this->request->getPost('key');
            if ($key == md5('confirm')) {
                if ($tourType->removeData()) {
                    $this->flash->success("Tour & Activities has been removed successfuly.");
                    $this->response->redirect('/admin/vendors/touractivities');
                }
            }
        }
        $this->tag->prependTitle('Remove Tour & Activities');
        $this->view->tourType = $tourType;
    }
    
    public function activateAction($vendorId = 0)
    {
        $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
        $vendor->status = 1;
        $vendor->save();
        // send email
        $mail = new \Tourpage\Library\Mail();
        $mail->setTo($vendor->emailAddress, $vendor->firstName . ' ' . $vendor->lastName);
        $mail->setSubject('Your Account has been Activated!');
        $mail->setBody($this->getEmailBody());
        $mail->send();
        $this->flash->success("Vendor activated successfuly. An email already sent to notify the vendor.");
        $this->response->redirect('/admin/vendors');
    }
    
    private function getEmailBody()
    {
        $url = $this->url->get('/vendor/auth/');
        return "Hi,<br>We're glad to inform you that the administrator approved and activated your account.<br><br>Please visit this <a href='{$url}' target='_blank'>link</a> to login.";
    }
}
