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
 * UsersController Class
 * @author amit
 */
class CustomersController extends BackendController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index action for list users
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
    }

    /**
     * Action for view user details
     * @param int $memberId
     * @return boolean
     */
    public function viewAction($memberId = 0) {
        if (!preg_match_all('/[0-9]+/', $memberId) || $memberId == 0) {
            return FALSE;
        }
        $member = \Tourpage\Models\Members::findFirst($memberId);
        if (!$member) {
            return FALSE;
        }
        $monthsQueue = [];
        foreach (\Tourpage\Helpers\Utils::getMonths() as $monthNumber => $monthTitle) {
            $monthsQueue[\Tourpage\Helpers\Utils::padInt($monthNumber)] = 0;
        }

        if ($member->bookings->count() > 0) {
            foreach ($member->bookings as $booking) {
                $bookingYear = date('Y', strtotime($booking->bookedOn));
                if ($bookingYear == \Tourpage\Helpers\Utils::__getCurrentYear()) {
                    $bookingMonth = date('m', strtotime($booking->bookedOn));
                    $monthsQueue[$bookingMonth] = $monthsQueue[$bookingMonth] + $booking->bookingAmount;
                }
            }
        }

        $this->assets->collection('headerJs')->addJs(COMMON_DIR . 'js/highcharts/highcharts.js');
        $this->assets->collection('headerJs')->addJs(COMMON_DIR . 'js/highcharts/modules/exporting.js');
        $this->tag->prependTitle('View Customer Details');
        $this->view->member = $member;
        $this->view->monthsQueue = $monthsQueue;
    }

    /**
     * Edit User action
     * @param int $memberId
     * @return boolean
     */
    public function editAction($memberId = 0) {
        if (!preg_match_all('/[0-9]+/', $memberId) || $memberId == 0) {
            return FALSE;
        }
        $member = \Tourpage\Models\Members::findFirst($memberId);
        if (!$member) {
            return FALSE;
        }
        $form = new \Multiple\Backend\Forms\MemberForm($member, array('edit' => TRUE));
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $member->firstName = $this->request->getPost('first_name', array('string', 'striptags'));
                $member->lastName = $this->request->getPost('last_name', array('string', 'striptags'));
                $member->city = $this->request->getPost('city', array('string', 'striptags'));
                $member->stateId = $this->request->getPost('state');
                $member->countryId = $this->request->getPost('country');
                $member->status = $this->request->getPost('status');

                $newPassword = $this->request->getPost('password');
                $newRePassword = $this->request->getPost('re_password');
                if (!empty($newPassword)) {
                    if (strcmp($newPassword, $newRePassword) == 0) {
                        $member->passWord = \Tourpage\Helpers\Utils::encryptPassword($newPassword);
                    } else {
                        $this->flash->error((string) 'Retype new password correctly');
                    }
                }
                if ($member->save()) {
                    $this->flash->success("Customer has been updated successfuly.");
                    $this->response->redirect('/admin/customers');
                }
            }
        }
        $this->tag->prependTitle('Edit Customer');
        $this->view->form = $form;
        $this->view->pick('customers/form');
    }

    /**
     * Remove user action
     * @param int $memberId
     */
    public function removeAction($memberId = 0) {
        if (!preg_match_all('/[0-9]+/', $memberId) || $memberId == 0) {
            return FALSE;
        }
        $member = \Tourpage\Models\Members::findFirst($memberId);
        if (!$member) {
            return FALSE;
        }
        if ($this->request->isPost()) {
            $key = $this->request->getPost('key');
            if ($key == md5('confirm')) {
                if ($member->removeData()) {
                    $this->flash->success("Customer has been removed successfuly.");
                    $this->response->redirect('/admin/customers');
                }
            }
        }
        $this->tag->prependTitle('Remove Customer');
        $this->view->member = $member;
    }

    /**
     * Customers Reviews & Rating Action
     * @param type $page
     * @return boolean
     */
    public function reviewsAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        if ($this->request->isPost()) {
            $reviewIds = $this->request->getPost('rvt');
            $reviewAction = $this->request->getPost('review_action');
            if (count($reviewIds) > 0) {
                foreach ($reviewIds as $reviewId) {
                    $review = \Tourpage\Models\ToursReview::findFirst($reviewId);
                    if ($review && $review->count() > 0) {
                        switch ($reviewAction) {
                            case 'active':
                                $review->reviewStatus = \Tourpage\Models\ToursReview::ACTIVE_STATUS_CODE;
                                $review->save();
                                break;
                            case 'inactive':
                                $review->reviewStatus = \Tourpage\Models\ToursReview::INACTIVE_STATUS_CODE;
                                $review->save();
                                break;
                            case 'remove':
                                $review->removeData();
                                break;
                        }
                    }
                }
            }
            $this->flash->success('Selected action has been applied on to reviews.');
            $this->response->redirect($this->router->getRewriteUri());
        }
        $modelBind = [];
        $reviews = \Tourpage\Models\ToursReview::query();
        if (count($modelBind) > 0) {
            $reviews->bind($modelBind);
        }
        $reviews->order("reviewId DESC");
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $reviews->execute(),
            "page" => $page,
        ));
        $this->assets->collection('headerCss')->addCss(FRONT_END_DIR . 'css/jquery.rateyo.min.css');
        $this->assets->collection('headerJs')->addJs(FRONT_END_DIR . 'js/jquery.rateyo.min.js');
        $this->tag->prependTitle('Reviews & Rating');
        $this->view->pager = $pager;
    }

    /**
     * Action for Place of Attractions
     * @param string $action Type of Action -- list/create/edit/remove
     * @param int $page Pagination page index
     * @param int $attractionId
     * @return boolean
     */
    public function attractionsAction($action = 'list', $page = 1, $attractionId = 0) {
        if ($action == 'list') {
            if (!preg_match_all('/[0-9]+/', $page, $matches)) {
                return false;
            }
            $defaultValues = [];
            if ($this->request->isPost()) {
                $formType = $this->request->getPost('formtp');
                switch ($formType) {
                    case 'fltr':
                        $queryString = '';
                        $attractionName = $this->request->getPost('poan');
                        $attractionCity = $this->request->getPost('ct');
                        $attractionState = $this->request->getPost('p');
                        $attractionCountry = $this->request->getPost('c');
                        $attractionStatus = $this->request->getPost('s');
                        $redirectTo = $this->url->get('/admin/customers/attractions');
                        if ($attractionName != '') {
                            $queryString .= 'poan=' . urlencode($attractionName) . '&';
                        }
                        if ($attractionCity != '') {
                            if ($attractionCity != '[all]') {
                                $queryString .= 'ct=' . $attractionCity . '&';
                            }
                        }
                        if ($attractionState != '') {
                            if ($attractionState != '[all]') {
                                $queryString .= 'p=' . $attractionState . '&';
                            }
                        }
                        if ($attractionCountry != '') {
                            if ($attractionCountry != '[all]') {
                                $queryString .= 'c=' . $attractionCountry . '&';
                            }
                        }
                        if ($attractionStatus != '') {
                            if ($attractionStatus != '[all]') {
                                $queryString .= 's=' . $attractionStatus . '&';
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
            $attractions = \Tourpage\Models\PlaceOfAttractions::query();
            if ($this->request->hasQuery('poan')) {
                $defaultValues['poan'] = trim(urldecode($this->request->getQuery('poan')));
                $mn = explode(' ', $defaultValues['poan']);
                if (count($mn) > 0) {
                    $counter = 0;
                    foreach ($mn as $n) {
                        $attractions->orWhere("attractionName LIKE :name_" . $counter . ":", array('name_' . $counter => "%" . $n . "%"));
                        $counter++;
                    }
                }
            }
            if ($this->request->hasQuery('ct')) {
                $defaultValues['ct'] = $this->request->getQuery('ct');
                $attractions->andWhere("cityId = :city:", array('city' => $defaultValues['ct']));
            }
            if ($this->request->hasQuery('p')) {
                $defaultValues['p'] = $this->request->getQuery('p');
                $attractions->andWhere("stateId = :sid:", array('sid' => $defaultValues['p']));
            }
            if ($this->request->hasQuery('c')) {
                $defaultValues['c'] = $this->request->getQuery('c');
                $attractions->andWhere("countryId = :cid:", array('cid' => $defaultValues['c']));
            }
            if ($this->request->hasQuery('s')) {
                $defaultValues['s'] = $this->request->getQuery('s');
                $attractions->andWhere("attraction_status = :status:", array('status' => $defaultValues['s']));
            }
            if (count($defaultValues) > 0) {
                \Phalcon\Tag::setDefaults($defaultValues);
            }
            $attractions->order("attractionId DESC");
            $pager = new \Tourpage\Library\Pager(array(
                "data" => $attractions->execute(),
                "page" => $page,
            ));
            $pager->setUriPattern('/admin/customers/attractions/list/{page}');
            $this->view->defaultValues = $defaultValues;
            $this->view->pager = $pager;
            $this->tag->prependTitle('Place of Attractions');
        }
        if ($action == 'create') {
            $placeOfAttractionsForm = new \Multiple\Backend\Forms\PlaceOfAttractionsForm();
            if ($this->request->isPost()) {
                if ($placeOfAttractionsForm->isValid($this->request->getPost())) {
                    $coutryId = $this->request->getPost('country');
                    $stateId = $this->request->getPost('state');
                    $cityId = $this->request->getPost('city');
                    $name = $this->request->getPost('attraction_name');
                    $status = $this->request->getPost('attraction_status');

                    $attraction = new \Tourpage\Models\PlaceOfAttractions();
                    $attraction->countryId = $coutryId;
                    $attraction->stateId = $stateId;
                    $attraction->cityId = $cityId;
                    $attraction->attractionName = $name;
                    $attraction->status = $status;
                    if ($attraction->save()) {
                        $this->flash->success('Place of Attraction has been saved successfully.');
                        $this->response->redirect('/admin/customers/attractions');
                    } else {
                        foreach ($attraction->getMessages() as $messages) {
                            $this->flash->error((string) $messages);
                        }
                    }
                }
            }
            $this->view->form = $placeOfAttractionsForm;
            $this->view->formType = 'new';
            $this->tag->prependTitle('New Place of Attractions');
            $this->view->pick('customers/attractionsForm');
        }
        if ($action == 'edit') {
            $attraction = \Tourpage\Models\PlaceOfAttractions::findFirst($attractionId);
            if (!$attraction) {
                return false;
            }
            $placeOfAttractionsForm = new \Multiple\Backend\Forms\PlaceOfAttractionsForm($attraction, array('edit' => TRUE));
            if ($this->request->isPost()) {
                if ($placeOfAttractionsForm->isValid($this->request->getPost())) {
                    $coutryId = $this->request->getPost('country');
                    $stateId = $this->request->getPost('state');
                    $cityId = $this->request->getPost('city');
                    $name = $this->request->getPost('attraction_name');
                    $status = $this->request->getPost('attraction_status');

                    $attraction->countryId = $coutryId;
                    $attraction->stateId = $stateId;
                    $attraction->cityId = $cityId;
                    $attraction->attractionName = $name;
                    $attraction->status = $status;
                    if ($attraction->save()) {
                        $this->flash->success('Place of Attraction has been updated successfully.');
                        $this->response->redirect('/admin/customers/attractions');
                    } else {
                        foreach ($attraction->getMessages() as $messages) {
                            $this->flash->error((string) $messages);
                        }
                    }
                }
            }

            $this->view->form = $placeOfAttractionsForm;
            $this->view->formType = 'edit';
            $this->tag->prependTitle('Edit Place of Attractions');
            $this->view->pick('customers/attractionsForm');
        }
        if ($action == 'remove') {
            $attraction = \Tourpage\Models\PlaceOfAttractions::findFirst($attractionId);
            if (!$attraction) {
                return false;
            }
            if ($this->request->isPost()) {
                $key = $this->request->getPost('key');
                if ($key == md5('confirm')) {
                    if ($attraction->removeData()) {
                        $this->flash->success("Place of Attraction has been removed successfuly.");
                        $this->response->redirect('/admin/customers/attractions');
                    }
                }
            }
            $this->view->attraction = $attraction;
            $this->tag->prependTitle('Remove Place of Attractions');
            $this->view->pick('customers/removeAttraction');
        }
    }

    /**
     * Action for Place of Activities
     * @param string $action Type of Action -- list/create/edit/remove
     * @param int $page Pagination page index
     * @param int $activityId
     * @return boolean
     */
    public function activitiesAction($action = 'list', $page = 1, $activityId = 0) {
        if ($action == 'list') {
            if (!preg_match_all('/[0-9]+/', $page, $matches)) {
                return false;
            }
            $defaultValues = [];
            if ($this->request->isPost()) {
                $formType = $this->request->getPost('formtp');
                switch ($formType) {
                    case 'fltr':
                        $queryString = '';
                        $activityName = $this->request->getPost('poan');
                        $activityCity = $this->request->getPost('ct');
                        $activityState = $this->request->getPost('p');
                        $activityCountry = $this->request->getPost('c');
                        $activityStatus = $this->request->getPost('s');
                        $redirectTo = $this->url->get('/admin/customers/activities');
                        if ($activityName != '') {
                            $queryString .= 'poan=' . urlencode($activityName) . '&';
                        }
                        if ($activityCity != '') {
                            if ($activityCity != '[all]') {
                                $queryString .= 'ct=' . $activityCity . '&';
                            }
                        }
                        if ($activityState != '') {
                            if ($activityState != '[all]') {
                                $queryString .= 'p=' . $activityState . '&';
                            }
                        }
                        if ($activityCountry != '') {
                            if ($activityCountry != '[all]') {
                                $queryString .= 'c=' . $activityCountry . '&';
                            }
                        }
                        if ($activityStatus != '') {
                            if ($activityStatus != '[all]') {
                                $queryString .= 's=' . $activityStatus . '&';
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
            $activities = \Tourpage\Models\PlaceOfActivities::query();
            if ($this->request->hasQuery('poan')) {
                $defaultValues['poan'] = trim(urldecode($this->request->getQuery('poan')));
                $mn = explode(' ', $defaultValues['poan']);
                if (count($mn) > 0) {
                    $counter = 0;
                    foreach ($mn as $n) {
                        $activities->orWhere("activityName LIKE :name_" . $counter . ":", array('name_' . $counter => "%" . $n . "%"));
                        $counter++;
                    }
                }
            }
            if ($this->request->hasQuery('ct')) {
                $defaultValues['ct'] = $this->request->getQuery('ct');
                $activities->andWhere("cityId = :city:", array('city' => $defaultValues['ct']));
            }
            if ($this->request->hasQuery('p')) {
                $defaultValues['p'] = $this->request->getQuery('p');
                $activities->andWhere("stateId = :sid:", array('sid' => $defaultValues['p']));
            }
            if ($this->request->hasQuery('c')) {
                $defaultValues['c'] = $this->request->getQuery('c');
                $activities->andWhere("countryId = :cid:", array('cid' => $defaultValues['c']));
            }
            if ($this->request->hasQuery('s')) {
                $defaultValues['s'] = $this->request->getQuery('s');
                $activities->andWhere("activity_status = :status:", array('status' => $defaultValues['s']));
            }
            if (count($defaultValues) > 0) {
                \Phalcon\Tag::setDefaults($defaultValues);
            }
            $activities->order("activityId DESC");
            $pager = new \Tourpage\Library\Pager(array(
                "data" => $activities->execute(),
                "page" => $page,
            ));
            $pager->setUriPattern('/admin/customers/activities/list/{page}');
            $this->view->defaultValues = $defaultValues;
            $this->view->pager = $pager;
            $this->tag->prependTitle('Place of Activities');
        }
        if ($action == 'create') {
            $placeOfActivitiesForm = new \Multiple\Backend\Forms\PlaceOfActivitiesForm();
            if ($this->request->isPost()) {
                if ($placeOfActivitiesForm->isValid($this->request->getPost())) {
                    $coutryId = $this->request->getPost('country');
                    $stateId = $this->request->getPost('state');
                    $cityId = $this->request->getPost('city');
                    $name = $this->request->getPost('activity_name');
                    $status = $this->request->getPost('activity_status');

                    $activity = new \Tourpage\Models\PlaceOfActivities();
                    $activity->countryId = $coutryId;
                    $activity->stateId = $stateId;
                    $activity->cityId = $cityId;
                    $activity->activityName = $name;
                    $activity->status = $status;
                    if ($activity->save()) {
                        $this->flash->success('Place of activity has been saved successfully.');
                        $this->response->redirect('/admin/customers/activities');
                    } else {
                        foreach ($activity->getMessages() as $messages) {
                            $this->flash->error((string) $messages);
                        }
                    }
                }
            }
            $this->view->form = $placeOfActivitiesForm;
            $this->view->formType = 'new';
            $this->tag->prependTitle('New Place of Activity');
            $this->view->pick('customers/activityForm');
        }
        if ($action == 'edit') {
            $activity = \Tourpage\Models\PlaceOfActivities::findFirst($activityId);
            if (!$activity) {
                return false;
            }
            $placeOfActivitiesForm = new \Multiple\Backend\Forms\PlaceOfActivitiesForm($activity, array('edit' => TRUE));
            if ($this->request->isPost()) {
                if ($placeOfActivitiesForm->isValid($this->request->getPost())) {
                    $coutryId = $this->request->getPost('country');
                    $stateId = $this->request->getPost('state');
                    $cityId = $this->request->getPost('city');
                    $name = $this->request->getPost('activity_name');
                    $status = $this->request->getPost('activity_status');

                    $activity->countryId = $coutryId;
                    $activity->stateId = $stateId;
                    $activity->cityId = $cityId;
                    $activity->activityName = $name;
                    $activity->status = $status;
                    if ($activity->save()) {
                        $this->flash->success('Place of activity has been updated successfully.');
                        $this->response->redirect('/admin/customers/activities');
                    } else {
                        foreach ($activity->getMessages() as $messages) {
                            $this->flash->error((string) $messages);
                        }
                    }
                }
            }

            $this->view->form = $placeOfActivitiesForm;
            $this->view->formType = 'edit';
            $this->tag->prependTitle('Edit Place of Activity');
            $this->view->pick('customers/activityForm');
        }
        if ($action == 'remove') {
            $activity = \Tourpage\Models\PlaceOfActivities::findFirst($activityId);
            if (!$activity) {
                return false;
            }
            if ($this->request->isPost()) {
                $key = $this->request->getPost('key');
                if ($key == md5('confirm')) {
                    if ($activity->removeData()) {
                        $this->flash->success("Place of activity has been removed successfuly.");
                        $this->response->redirect('/admin/customers/activities');
                    }
                }
            }
            $this->view->activity = $activity;
            $this->tag->prependTitle('Remove Place of Activity');
            $this->view->pick('customers/removeActivity');
        }
    }
	
	/**
     * Action for Place of Activities
     * @param string $action Type of Action -- list/create/edit/remove
     * @param int $page Pagination page index
     * @param int $activityId
     * @return boolean
     */
    public function notificationsAction($action = 'list', $page = 1, $notificationId = 0) {
        if ($action == 'list') {
            if (!preg_match_all('/[0-9]+/', $page, $matches)) {
                return false;
            }
            $defaultValues = [];
            if ($this->request->isPost()) {
                $formType = $this->request->getPost('formtp');
                switch ($formType) {
                    case 'fltr':
                        $queryString = '';
                        $notificationStatus = $this->request->getPost('s');
                        $redirectTo = $this->url->get('/admin/customers/notifications');
                        if ($notificationStatus != '') {
                            if ($notificationStatus != '[all]') {
                                $queryString .= 's=' . $notificationStatus . '&';
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
            $notifications = \Tourpage\Models\MembersNotifications::query();
            if ($this->request->hasQuery('s')) {
                $defaultValues['s'] = $this->request->getQuery('s');
                $notifications->andWhere("memberNotificationStatus = :status:", array('status' => $defaultValues['s']));
            }
            if (count($defaultValues) > 0) {
                \Phalcon\Tag::setDefaults($defaultValues);
            }
            $notifications->order("memberNotificationId DESC");
            $pager = new \Tourpage\Library\Pager(array(
                "data" => $notifications->execute(),
                "page" => $page,
            ));
            $pager->setUriPattern('/admin/customers/notifications/list/{page}');
            $this->view->defaultValues = $defaultValues;
            $this->view->pager = $pager;
            $this->tag->prependTitle('Notifications');
        }
        if ($action == 'create') {
            $membersNotificationsForm = new \Multiple\Backend\Forms\MembersNotificationsForm();
            if ($this->request->isPost()) {
                if ($membersNotificationsForm->isValid($this->request->getPost())) {
                    $notificationText = $this->request->getPost('notification_text');
					$memberId = $this->request->getPost('member');
					$notifications = new \Tourpage\Models\Notifications();
					$notifications->notificationText = $notificationText;
					if ($notifications->save()) {
                    $membersNotifications = new \Tourpage\Models\MembersNotifications();
					$membersNotifications->notificationId = $notifications->notificationId;
					$membersNotifications->memberId = $memberId;
                    $membersNotifications->memberNotificationStatus = \Tourpage\Models\MembersNotifications::UNREAD_STATUS_CODE;
                    if ($membersNotifications->save()) {
                        $this->flash->success('Notification Posted Successfully.');
                        $this->response->redirect('/admin/customers/notifications');
                    } else {
                        foreach ($membersNotifications->getMessages() as $messages) {
                            $this->flash->error((string) $messages);
                        }
                    }
					} else {
                        foreach ($notifications->getMessages() as $messages) {
                            $this->flash->error((string) $messages);
                        }
                    }
                }
            }
            $this->view->form = $membersNotificationsForm;
            $this->view->formType = 'new';
            $this->tag->prependTitle('New Notifications');
            $this->view->pick('customers/notificationsForm');
        }
        /*if ($action == 'edit') {
            $activity = \Tourpage\Models\PlaceOfActivities::findFirst($activityId);
            if (!$activity) {
                return false;
            }
            $placeOfActivitiesForm = new \Multiple\Backend\Forms\PlaceOfActivitiesForm($activity, array('edit' => TRUE));
            if ($this->request->isPost()) {
                if ($placeOfActivitiesForm->isValid($this->request->getPost())) {
                    $coutryId = $this->request->getPost('country');
                    $stateId = $this->request->getPost('state');
                    $cityId = $this->request->getPost('city');
                    $name = $this->request->getPost('activity_name');
                    $status = $this->request->getPost('activity_status');

                    $activity->countryId = $coutryId;
                    $activity->stateId = $stateId;
                    $activity->cityId = $cityId;
                    $activity->activityName = $name;
                    $activity->status = $status;
                    if ($activity->save()) {
                        $this->flash->success('Place of activity has been updated successfully.');
                        $this->response->redirect('/admin/customers/activities');
                    } else {
                        foreach ($activity->getMessages() as $messages) {
                            $this->flash->error((string) $messages);
                        }
                    }
                }
            }

            $this->view->form = $placeOfActivitiesForm;
            $this->view->formType = 'edit';
            $this->tag->prependTitle('Edit Place of Activity');
            $this->view->pick('customers/activityForm');
        }
        if ($action == 'remove') {
            $activity = \Tourpage\Models\PlaceOfActivities::findFirst($activityId);
            if (!$activity) {
                return false;
            }
            if ($this->request->isPost()) {
                $key = $this->request->getPost('key');
                if ($key == md5('confirm')) {
                    if ($activity->removeData()) {
                        $this->flash->success("Place of activity has been removed successfuly.");
                        $this->response->redirect('/admin/customers/activities');
                    }
                }
            }
            $this->view->activity = $activity;
            $this->tag->prependTitle('Remove Place of Activity');
            $this->view->pick('customers/removeActivity');
        }*/
    }

}
