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

namespace Multiple\Backend\Controllers\Settings;

/**
 * Description of AdsController
 * @author amit
 */
class AdsController extends \Multiple\Backend\Controllers\BackendController {

    public $pageHeading = array(
        'home' => 'Home ads banners',
        'vendorlogin' => 'Vendor Login Ad banner',
        'userlogin' => 'User Login Ad banner',
    );

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Action for Banner list and add
     * @param string $type values:home, supplierhome etc.
     * @return boolean
     */
    public function indexAction($type = '') {
        if ($type == '') {
            return false;
        }
        if (!preg_match_all('/[a-z]+/', $type, $matches)) {
            return false;
        }
        $banners = \Tourpage\Models\Banners::find(array(
                    'conditions' => 'bannerType = :type:',
                    'bind' => array('type' => $type)
        ));
        $this->tag->prependTitle((isset($this->pageHeading[$type]) ? $this->pageHeading[$type] : ''));
        $this->view->banners = $banners;
        $this->view->pageHeading = isset($this->pageHeading[$type]) ? $this->pageHeading[$type] : '';
        $this->view->type = $type;
        $this->view->pick('settings/ads/index');
    }

    /**
     * Add new banner
     * @param string $type
     * @return boolean
     */
    public function addAction($type = '') {
        if ($type == '') {
            return false;
        }
        if (!preg_match_all('/[a-z]+/', $type, $matches)) {
            return false;
        }
        $form = new \Multiple\Backend\Forms\BannerForm();
        $formFile = new \Tourpage\Forms\CFilesForm(NULL, array(
            'name' => 'banner_img',
            'label' => 'Ads & Banner Image',
            'maxSize' => 0,
            'maxResolution' => 0,
            'attribute' => array(
                'class' => 'form-control'
            ),
        ));
        if ($this->request->isPost()) {
            $valid = $form->isValid($this->request->getPost());
            $valid = $formFile->isValid($_FILES) && $valid;
            if ($valid) {
                if ($this->request->hasFiles(TRUE)) {
                    $baseLocation = \Tourpage\Models\Banners::getUploadUri(TRUE);
                    foreach ($this->request->getUploadedFiles(true) as $file) {
                        $imageName = 'adsbanner' . time() . rand(0, 9) . '.' . $file->getExtension();
                        $file->moveTo($baseLocation . '/' . $imageName);
                    }

                    $banner = new \Tourpage\Models\Banners();
                    $banner->bannerImage = $imageName;
                    $banner->bannerLink = $this->request->getPost('banner_link');
                    $banner->bannerType = $type;
                    $banner->bannerStatus = \Tourpage\Models\Banners::ACTIVE_STATUS_CODE;
                    $banner->imageUploadedOn = \Tourpage\Helpers\Utils::currentDate();
                    if ($banner->save()) {
                        $this->flash->success('Banner has been added successfully');
                        $this->response->redirect('/admin/settings/banner/ads/index/' . $type);
                    } else {
                        if (count($banner->getMessages()) > 0) {
                            foreach ($banner->getMessages() as $message) {
                                $this->flash->error((string) $message);
                            }
                        }
                    }
                }
            }
        }
        $this->tag->prependTitle(isset($this->pageHeading[$type]) ? 'Add New ' . $this->pageHeading[$type] : '');
        $this->view->pageHeading = isset($this->pageHeading[$type]) ? 'Add New ' . $this->pageHeading[$type] : '';
        $this->view->type = $type;
        $this->view->form = $form;
        $this->view->formFile = $formFile;
        $this->view->formType = 'new';
        $this->view->pick('settings/ads/form');
    }

    /**
     * Edit banner
     * @param int $adsId banner id
     * @param string $type
     * @return boolean
     */
    public function editAction($adsId = 0, $type = '') {
        if ($adsId == 0) {
            return FALSE;
        }
        if (!preg_match_all('/[0-9]+/', $adsId, $matches)) {
            return false;
        }
        if ($type == '') {
            return false;
        }
        if (!preg_match_all('/[a-z]+/', $type, $matches)) {
            return false;
        }
        $banner = \Tourpage\Models\Banners::findFirst($adsId);
        if (!$banner) {
            return FALSE;
        }
        $form = new \Multiple\Backend\Forms\BannerForm($banner, array('edit' => TRUE));
        $formFile = new \Tourpage\Forms\CFilesForm(NULL, array(
            'name' => 'banner_img',
            'label' => 'Ads & Banner Image',
            'maxSize' => 0,
            'maxResolution' => 0,
            'attribute' => array(
                'class' => 'form-control'
            ),
            'allowEmpty' => TRUE,
        ));
        if ($this->request->isPost()) {
            $valid = $form->isValid($this->request->getPost());
            $valid = $formFile->isValid($_FILES) && $valid;
            if ($valid) {
                if ($this->request->hasFiles(TRUE)) {
                    $banner->removeImage();
                    $baseLocation = \Tourpage\Models\Banners::getUploadUri(TRUE);
                    foreach ($this->request->getUploadedFiles(true) as $file) {
                        $imageName = 'adsbanner' . time() . rand(0, 9) . '.' . $file->getExtension();
                        $file->moveTo($baseLocation . '/' . $imageName);
                    }
                    $banner->bannerImage = $imageName;
                }
                $banner->bannerLink = $this->request->getPost('banner_link');
                $banner->bannerStatus = $this->request->getPost('banner_status');
                if ($banner->save()) {
                    $this->flash->success('Banner has been updated successfully');
                    $this->response->redirect('/admin/settings/banner/ads/index/' . $type);
                } else {
                    if (count($banner->getMessages()) > 0) {
                        foreach ($banner->getMessages() as $message) {
                            $this->flash->error((string) $message);
                        }
                    }
                }
            }
        }

        $this->tag->prependTitle(isset($this->pageHeading[$type]) ? 'Edit ' . $this->pageHeading[$type] : '');
        $this->view->pageHeading = isset($this->pageHeading[$type]) ? 'Edit ' . $this->pageHeading[$type] : '';
        $this->view->banner = $banner;
        $this->view->type = $type;
        $this->view->form = $form;
        $this->view->formFile = $formFile;
        $this->view->formType = 'edit';
        $this->view->pick('settings/ads/form');
    }

    /**
     * Remove banner
     * @param int $adsId banner id
     * @param string $type
     * @return boolean
     */
    public function removeAction($adsId = 0, $type = '') {
        if ($adsId == 0) {
            return FALSE;
        }
        if (!preg_match_all('/[0-9]+/', $adsId, $matches)) {
            return false;
        }
        if ($type == '') {
            return false;
        }
        if (!preg_match_all('/[a-z]+/', $type, $matches)) {
            return false;
        }

        $banner = \Tourpage\Models\Banners::findFirst($adsId);
        if (!$banner) {
            return FALSE;
        } else {
            if ($banner->removeData()) {
                $this->flash->success('Banner has been removed successfuly');
                $this->response->redirect('/admin/settings/banner/ads/index/' . $type);
            }
        }
    }

    /**
     * Action for list vendor banner
     * Which banner is uploaded by vendors
     * @param string $type
     * @param int $page
     * @return boolean
     */
    public function vendorAction($type = '', $page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $pattern = '/admin/settings/banner/ads/vendor/' . $type . '/{page}';
        $banners = \Tourpage\Models\VendorsBanner::find();
        $this->tag->prependTitle('Vendor Home ad banners');
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $banners,
            "page" => $page,
        ));
        $pager->setUriPattern($pattern);
        $this->view->pager = $pager;
        $this->view->pick('settings/ads/vendor/index');
    }

    /**
     * Action to manage vendor banner
     * Which banners are uploaded by vendor
     * Administrator can only change the status of the banner
     * or can remove the banner without any permission of related vendor
     * @param int $bannerId
     * @return boolean
     */
    public function editvendorAction($bannerId = 0) {
        if ($bannerId == 0) {
            return false;
        }
        if (!preg_match_all('/[0-9]+/', $bannerId, $matches)) {
            return false;
        }
        $banner = \Tourpage\Models\VendorsBanner::findFirst($bannerId);
        if (!$banner) {
            return FALSE;
        }
        if ($this->request->isPost()) {
            $banner->bannerStatus = $this->request->getPost('banner_status');
            if ($banner->save()) {
                $this->flash->success('Vendor Banner has been updated successfully');
                $this->response->redirect('/admin/settings/banner/ads/vendor');
            } else {
                if (count($banner->getMessages()) > 0) {
                    foreach ($banner->getMessages() as $message) {
                        $this->flash->error((string) $message);
                    }
                }
            }
        }

        $this->tag->prependTitle('Edit Vendor banner');
        $this->view->banner = $banner;
        $this->view->pick('settings/ads/vendor/form');
    }

    /**
     * Action to remove vendor banner
     * Which banners are uploaded by vendor
     * Administrator can only change the status of the banner
     * or can remove the banner without any permission of related vendor
     * @param int $bannerId
     * @return boolean
     */
    public function removevendorAction($bannerId = 0) {
        if ($bannerId == 0) {
            return false;
        }
        if (!preg_match_all('/[0-9]+/', $bannerId, $matches)) {
            return false;
        }
        $banner = \Tourpage\Models\VendorsBanner::findFirst($bannerId);
        if (!$banner) {
            return FALSE;
        } else {
            if ($banner->removeData()) {
                $this->flash->success('Vendor Banner has been removed successfuly');
                $this->response->redirect('/admin/settings/banner/ads/vendor');
            }
        }
    }

    /**
     * Manage accouncement for vendor dashboard
     * @param string $type
     * @return boolean
     */
    public function announcementAction($type = '') {
        if ($type == '') {
            return false;
        }
        if (!preg_match_all('/[a-z]+/', $type, $matches)) {
            return false;
        }
        $pageHeading = '';
        switch ($type) {
            case 'vendordashboard':
                $pageHeading = 'Dashboard Announcement';
                $annountement = \Tourpage\Models\Announcement::findFirst(array(
                            'conditions' => 'ancType = :type:',
                            'bind' => array('type' => $type)
                ));
                if (!$annountement) {
                    $annountement = new \Tourpage\Models\Announcement();
                }
                if ($this->request->isPost()) {
                    $announcementContent = $this->request->getPost('anc_content');
                    $annountement->ancContent = \Tourpage\Helpers\Utils::encodeString($announcementContent);
                    $annountement->ancType = $type;
                    if ($annountement->save()) {
                        $this->flash->success("Dashboard Announcement saved successfuly.");
                    }
                    $this->response->redirect($this->router->getRewriteUri());
                }
                break;
        }
        $this->tag->prependTitle($pageHeading);
        $this->view->pageHeading = $pageHeading;
        $this->view->announcementContent = \Tourpage\Helpers\Utils::decodeString($annountement->ancContent);
        $this->view->type = $type;
        $this->view->pick('settings/ads/announcement');
    }

}
