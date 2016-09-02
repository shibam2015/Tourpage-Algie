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
 * Class for Vendor Account Management
 * @module Vendor
 */
class AccountController extends VendorController {

    /**
     * Index action for vendor account overview
     */
    public function indexAction() {
        $this->tag->setTitle('Account Overview');
        $this->view->vendor = $this->vendors;
    }

    /**
     * Action for modify account
     */
    public function editAction() {
        $this->tag->setTitle('Account Setting');
        //$vendor = $this->vendors->getVendorData();
        $vendorId = $this->vendors->getId();
        $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
        $form = new \Multiple\Vendor\Forms\RegistrationForm($vendor, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $vendor->firstName = $this->request->getPost('vendor_first_name', array('string', 'striptags'));
                $vendor->lastName = $this->request->getPost('vendor_last_name', array('string', 'striptags'));
                if ($vendor->isParent()) {
                    $vendor->jobTitle = $this->request->getPost('vendor_job_title', array('string', 'striptags'));
                    $vendor->businessName = $this->request->getPost('vendor_business_name', array('string', 'striptags'));
                }
                $vendor->supportEmail = $this->request->getPost('support_email');
                $vendor->phone = $this->request->getPost('vendor_phone');
                $vendor->addressOne = $this->request->getPost('vendor_address_1');
                $vendor->addressTwo = $this->request->getPost('vendor_address_2');
                $vendor->city = $this->request->getPost('vendor_city');
                $vendor->zipCode = $this->request->getPost('vendor_zip');
                $vendor->stateId = $this->request->getPost('vendor_state');
                $vendor->countryId = $this->request->getPost('vendor_country');
                if ($vendor->isParent()) {
                    $vendor->vendorCategory = $this->request->getPost('vendor_category');
                    $vendor->isTripAdv = $this->request->getPost('vendor_is_trip_advisor');
                    $vendor->tripAdvLink = $this->request->getPost('vendor_trip_advisor_link');
                }
                if ($vendor->save()) {
                    if ($vendor->isParent()) {
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
                        } else {
                            $this->flash->error((string) 'Tour & activities Type is required');
                        }
                    }
                    $removeAtatar = $this->request->getPost('remove_avatar');
                    if ($removeAtatar) {
                        if ($vendor->avatar) {
                            if (file_exists($vendor->getAvatarUri($vendor->avatar, TRUE))) {
                                unlink($vendor->getAvatarUri($vendor->avatar, TRUE));
                            }
                        }
                        $vendor->avatar = NULL;
                    }
                    if ($this->request->hasFiles(TRUE)) {
                        $baseLocation = $this->vendors->getTourImagesPath();
                        $i = 1;
                        foreach ($this->request->getUploadedFiles(TRUE) as $file) {
                            $imageName = 'avatar' . time() . $i . '.' . $file->getExtension();
                            if ($vendor->avatar) {
                                if (file_exists($vendor->getAvatarUri($vendor->avatar, TRUE))) {
                                    unlink($vendor->getAvatarUri($vendor->avatar, TRUE));
                                }
                            }
                            if ($file->moveTo($baseLocation . '/' . $imageName)) {
                                $vendor->avatar = $imageName;
                                $logo = new \Phalcon\Image\Adapter\GD($baseLocation . '/' . $imageName);
                                $logo->resize(300, 300);
                                $logo->save($baseLocation . '/' . $imageName);
                            }
                            $i++;
                        }
                    }
                    if ($vendor->save()) {
                        $this->vendors->refresh();
                        $this->flash->success("Account has been updated successfuly.");
                        $this->response->redirect($this->router->getRewriteUri());
                    } else {
                        foreach ($vendor->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }
                } else {
                    foreach ($vendor->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->view->vendor = $vendor;
        $this->view->form = $form;
    }

    /**
     * Action for modify store details
     */
    public function storesettingAction() {
        $this->tag->setTitle('Store Setting');
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
        $storeSettingForm = new \Multiple\Vendor\Forms\StoreSettingForm($vendor, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($storeSettingForm->isValid($this->request->getPost())) {
                $vendor->slogan = $this->request->getPost('store_slogan', array('string', 'striptags'));
                $vendor->aboutUs = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('store_about_us'));
                $vendor->introduction = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('store_introduction'));
                $vendor->policy = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('store_policy'));
                $vendor->cancelPolicy = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('cancel_policy'));
                $vendor->estd = $this->request->getPost('store_estd');
                $vendor->default_commission = $this->request->getPost('default_commission', 'float');
                ////for aboutUs banner upload start///
                 $removeBanner = $this->request->getPost('remove_banner');
                if ($removeBanner) {
                    if ($vendor->aboutUsBanner) {
                        if (file_exists($vendor->getAboutUsBannerUri($vendor->aboutUsBanner, TRUE))) {
                            unlink($vendor->getAboutUsBannerUri($vendor->aboutUsBanner, TRUE));
                        }
                    }
                    $vendor->aboutUsBanner = NULL;
                }
                if ($this->request->hasFiles(TRUE)) {
                    
                    $baseLocation = $this->vendors->getTourImagesPath();
                    $i = 1;
                    foreach ($this->request->getUploadedFiles(TRUE) as $file) {
                        $imageName = 'aboutUsBanner' . time() . $i . '.' . $file->getExtension();
                        if ($vendor->aboutUsBanner) {
                            
                            if (file_exists($vendor->getAboutUsBannerUri($vendor->aboutUsBanner, TRUE))) {
                              //die("hii4");  
                                unlink($vendor->getAboutUsBannerUri($vendor->aboutUsBanner, TRUE));
                            }
                        }
                        if ($file->moveTo($baseLocation . '/' . $imageName)) {
                            //die($baseLocation);
                            $vendor->aboutUsBanner = $imageName;
                            $abUsBanner = new \Phalcon\Image\Adapter\GD($baseLocation . '/' . $imageName);
                            $abUsBanner->resize(800, 360);
                            $abUsBanner->save($baseLocation . '/' . $imageName);
                            }
                        $i++;
                    }
                }
                ////for aboutUs banner upload end///
                 $vendor->aboutUsAdvance = \Tourpage\Helpers\Utils::encodeString($this->request->getPost('store_advance_about_us'));
                $advanceAboutUsStatus = $this->request->getPost('adv_setting');
                 $vendor->aboutUsStatus = $advanceAboutUsStatus;
                ////////for advance about us end////
                $removeLogo = $this->request->getPost('remove_logo');
                if ($removeLogo) {
                    if ($vendor->logo) {
                        if (file_exists($vendor->getLogoUri($vendor->logo, TRUE))) {
                            unlink($vendor->getLogoUri($vendor->logo, TRUE));
                        }
                    }
                    $vendor->logo = NULL;
                }
                if ($this->request->hasFiles(TRUE)) {
                    $baseLocation = $this->vendors->getTourImagesPath();
                    $i = 1;
                    foreach ($this->request->getUploadedFiles(TRUE) as $file) {
                        $imageName = 'logo' . time() . $i . '.' . $file->getExtension();
                        if ($vendor->logo) {
                            if (file_exists($vendor->getLogoUri($vendor->logo, TRUE))) {
                                unlink($vendor->getLogoUri($vendor->logo, TRUE));
                            }
                        }
                        if ($file->moveTo($baseLocation . '/' . $imageName)) {
                            $vendor->logo = $imageName;
                            $logo = new \Phalcon\Image\Adapter\GD($baseLocation . '/' . $imageName);
                            $logo->resize(300, 300);
                            $logo->save($baseLocation . '/' . $imageName);
                        }
                        $i++;
                    }
                }
                $socialMedia = [];
                if (!isset($socialMedia['links'])) {
                    $socialMedia['links'] = [];
                }
                if ($this->request->getPost('social_media_links_facebook')) {
                    if (!isset($socialMedia['links']['facebook'])) {
                        $socialMedia['links']['facebook'] = '';
                    }
                    $socialMedia['links']['facebook'] = urlencode($this->request->getPost('social_media_links_facebook'));
                }
                if ($this->request->getPost('social_media_links_twitter')) {
                    if (!isset($socialMedia['links']['twitter'])) {
                        $socialMedia['links']['twitter'] = '';
                    }
                    $socialMedia['links']['twitter'] = urlencode($this->request->getPost('social_media_links_twitter'));
                }
                if ($this->request->getPost('social_media_links_instagram')) {
                    if (!isset($socialMedia['links']['instagram'])) {
                        $socialMedia['links']['instagram'] = '';
                    }
                    $socialMedia['links']['instagram'] = urlencode($this->request->getPost('social_media_links_instagram'));
                }
                $vendor->socialMedia = serialize($socialMedia);
                $vendor->themeBackground = $this->request->getPost('theme_bckgrnd');
                if ($vendor->save()) {
                    $this->flash->success("Store setting has been updated");
                    $this->response->redirect($this->router->getRewriteUri());
                } else {
                    foreach ($vendor->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }
        $this->view->vendor = $vendor;
        $this->view->form = $storeSettingForm;
    }

    /**
     * Action for reset password
     */
    public function resetPasswordAction() {
        $this->tag->setTitle('Reset Password');
        $resetPasswordForm = new \Multiple\Vendor\Forms\ResetPasswordForm(null, array('edit' => TRUE));
        //$vendorData = $this->vendors->getVendorData();
        $vendorId = $this->vendors->getId();
        $vendorData = \Tourpage\Models\Vendors::findFirst($vendorId);
        if ($this->request->isPost()) {
            if ($resetPasswordForm->isValid($this->request->getPost())) {
                $vendorSql = 'emailAddress = "' . $vendorData->emailAddress . '" AND passWord = "' . \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('current_password')) . '"';
                $vendor = \Tourpage\Models\Vendors::findFirst($vendorSql);
                if ($vendor && $vendor->count() > 0) {
                    $vendor->passWord = \Tourpage\Helpers\Utils::encryptPassword($this->request->getPost('new_password'));
                    if ($vendor->update()) {
                        $this->flash->success("Password has been reseted.");
                        $this->response->redirect($this->router->getRewriteUri());
                    } else {
                        foreach ($vendor->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }
                } else {
                    $this->flash->error("Invalid Current Password");
                }
            }
        }
        $this->view->form = $resetPasswordForm;
    }

    /**
     * Action for vendor banner management
     */
    public function storebannerAction() {
        $this->tag->setTitle('Banners Setting');
        $error = [];
        //$vendor = $this->vendors->getVendorData();
        $vendorId = !$this->vendors->getVendorData()->isParent() ? $this->vendors->getVendorData()->parentId : $this->vendors->getId();
        $vendor = \Tourpage\Models\Vendors::findFirst($vendorId);
        if ($this->request->isPost()) {
            $bannerLink = $this->request->getPost('banner_link');
            $bannerStatus = $this->request->getPost('banner_status');
            $bannerCaption = $this->request->getPost('banner_caption');
            $removeBanner = $this->request->getPost('remove_banner');
            if (count($removeBanner) > 0) {
                foreach ($removeBanner as $rb) {
                    $mbanner = \Tourpage\Models\VendorsBanner::findFirst(array(
                                'bannerId = :banner_id: AND vendorId = :vendor_id:',
                                'bind' => array(
                                    'banner_id' => $rb,
                                    'vendor_id' => $vendor->vendorId
                                )
                    ));
                    if ($mbanner->count() > 0) {
                        if (file_exists($mbanner->getBannerUri(TRUE))) {
                            unlink($mbanner->getBannerUri(TRUE));
                        }
                        $mbanner->delete();
                    }
                }
            }
            if (count($bannerLink) > 0) {
                foreach ($bannerLink as $blKey => $bl) {
                    if ($bl != '') {
                        $lbanner = \Tourpage\Models\VendorsBanner::findFirst(array(
                                    'bannerId = :banner_id: AND vendorId = :vendor_id:',
                                    'bind' => array(
                                        'banner_id' => $blKey,
                                        'vendor_id' => $vendor->vendorId
                                    )
                        ));
                        if ($lbanner && $lbanner->count() > 0) {
                            $lbanner->bannerLink = $bl;
                            $lbanner->save();
                        }
                    }
                }
            }
            if (count($bannerStatus) > 0) {
                foreach ($bannerStatus as $bsKey => $bs) {
                    $sbanner = \Tourpage\Models\VendorsBanner::findFirst(array(
                                'bannerId = :banner_id: AND vendorId = :vendor_id:',
                                'bind' => array(
                                    'banner_id' => $bsKey,
                                    'vendor_id' => $vendor->vendorId
                                )
                    ));
                    if ($sbanner && $sbanner->count() > 0) {
                        $sbanner->bannerStatus = $bs;
                        $sbanner->save();
                    }
                }
            }
            
            /**
             * @desc save banner caption
             * @auth Algie Caballes
             */
            if (count($bannerCaption) > 0) {
                foreach ($bannerCaption as $cKey => $caption) {
                    $cbanner = \Tourpage\Models\VendorsBanner::findFirst(array(
                                'bannerId = :banner_id: AND vendorId = :vendor_id:',
                                'bind' => array(
                                    'banner_id' => $cKey,
                                    'vendor_id' => $vendor->vendorId
                                )
                    ));
                    if ($cbanner && $cbanner->count() > 0) {
                        $cbanner->bannerCaption = $caption;
                        $cbanner->save();
                    }
                }
            }
            
            if ($this->request->hasFiles(true)) {
                $i = 1;
                $baseLocation = $this->vendors->getTourImagesPath();
                foreach ($this->request->getUploadedFiles(true) as $file) {
                    //if ($file->getSize() <= 65000) {
                        $banner = new \Phalcon\Image\Adapter\GD($file->getTempName());
                        //if ($banner->getWidth() <= 800 && $banner->getHeight() <= 360) {
                            $key = str_replace('banner_image.', '', $file->getKey());
                            //if (isset($bannerLink[$key]) && !empty($bannerLink[$key])) {

                                $imageName = 'banner' . time() . $i . '.' . $file->getExtension();
                                
                                if ($file->moveTo($baseLocation . '/' . $imageName)) {
                                    $bannerResize = new \Phalcon\Image\Adapter\GD($baseLocation . '/' . $imageName);
                                    $bannerResize->resize(800, 360);
                                    $bannerResize->save($baseLocation . '/' . $imageName);
                                    $modelBanner = \Tourpage\Models\VendorsBanner::findFirst(array(
                                                'bannerId = :banner_id: AND vendorId = :vendor_id:',
                                                'bind' => array(
                                                    'banner_id' => $key,
                                                    'vendor_id' => $vendor->vendorId
                                                )
                                    ));
                                    if (!$modelBanner || $modelBanner->count() == 0) {
                                        $modelBanner = new \Tourpage\Models\VendorsBanner();
                                    }
                                    if ($modelBanner->bannerImage != NULL) {
                                        if (file_exists($modelBanner->getBannerUri(TRUE))) {
                                            unlink($modelBanner->getBannerUri(TRUE));
                                        }
                                    }
                                    $modelBanner->vendorId = $vendor->vendorId;
                                    $modelBanner->bannerImage = $imageName;
                                    $modelBanner->bannerLink = (isset($bannerLink[$key]) && !empty($bannerLink[$key]) ? $bannerLink[$key] : '');
                                    $modelBanner->bannerStatus = $bannerStatus[$key];
                                    $modelBanner->bannerCaption = $bannerCaption[$key];
                                    $modelBanner->imageUploadedOn = \Tourpage\Helpers\Utils::currentDate();
                                    if (!$modelBanner->save()) {
                                        foreach ($modelBanner->getMessages() as $message) {
                                            $error[] = (string) $message;
                                        }
                                    }
                                }
                                $i++;
                            /*} else {
                                $error[] = 'Link missing for banner ' . $file->getName();
                            }*/
                        /*} else {
                            $error[] = 'File: ' . $file->getName() . ' is too big. Max allowed resolution 800px x 360px.';
                        }*/
                    /*} else {
                        $error[] = 'File: ' . $file->getName() . ' size is too big. Max allowed size 65KB.';
                    }*/
                }
            }
            if (count($error) > 0) {
                foreach ($error as $er) {
                    $this->flash->error($er);
                }
            } else {
                $this->flash->success('Banner saved successfully');
            }
            $this->response->redirect($this->router->getRewriteUri());
        }
        $this->view->vendor = $vendor;
    }

}
