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
 * Backend Booking Controller
 * @author amit
 */
class BookingController extends BackendController {

    /**
     * Initializing controller
     */
    public function initialize() {
        parent::initialize();
        $this->initializeLayout();
    }

    /**
     * Index Action
     */
    public function indexAction($page = 1) {
        if (!preg_match_all('/[0-9]+/', $page, $matches)) {
            return false;
        }
        $defaultValues = [];
        $modelBind = [];
        if ($this->request->isPost()) {
            $queryString = '';
            $invoiceNumber = $this->request->getPost('invn');
            $bookingFrom = $this->request->getPost('bkf');
            $bookingTo = $this->request->getPost('bkt');
            $bookingMember = $this->request->getPost('bm');
            $bookingStatus = $this->request->getPost('bs');
            $redirectTo = $this->url->get('/admin/booking');
            if ($invoiceNumber != '') {
                $queryString .= 'invn=' . urlencode($invoiceNumber) . '&';
            }
            if ($bookingFrom != '') {
                $queryString .= 'bkf=' . urlencode($bookingFrom) . '&';
            }
            if ($bookingTo != '') {
                $queryString .= 'bkt=' . urlencode($bookingTo) . '&';
            }
            if ($bookingMember != '') {
                if ($bookingMember != '[all]') {
                    $queryString .= 'bm=' . $bookingMember . '&';
                }
            }
            if ($bookingStatus != '') {
                if ($bookingStatus != '[all]') {
                    $queryString .= 'bs=' . $bookingStatus . '&';
                }
            }
            if ($queryString) {
                $queryString = ((substr($queryString, -1) == '&') ? substr($queryString, 0, -1) : $queryString);
                $redirectTo = $redirectTo . '?' . $queryString;
            }
            $this->response->redirect($redirectTo);
        }
        $booking = \Tourpage\Models\Booking::query();
        if ($this->request->hasQuery('bkf') && $this->request->hasQuery('bkt')) {
            $defaultValues['bkf'] = urldecode($this->request->getQuery('bkf'));
            $modelBind['fbookedOn'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($defaultValues['bkf']);
            $defaultValues['bkt'] = urldecode($this->request->getQuery('bkt'));
            $modelBind['tbookedOn'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($defaultValues['bkt']);
            $booking->addWhere("bookedOn >= :fbookedOn: AND bookedOn <= :tbookedOn:");
        } else {
            if ($this->request->hasQuery('bkf')) {
                $defaultValues['bkf'] = urldecode($this->request->getQuery('bkf'));
                $modelBind['bookedOn'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($defaultValues['bkf']);
                $booking->addWhere("bookedOn >= :bookedOn:");
            }
            if ($this->request->hasQuery('bkt')) {
                $defaultValues['bkt'] = urldecode($this->request->getQuery('bkt'));
                $modelBind['bookedOn'] = \Tourpage\Helpers\Utils::formatDatepickerToMySql($defaultValues['bkt']);
                $booking->addWhere("bookedOn <= :bookedOn:");
            }
        }
        if ($this->request->hasQuery('invn')) {
            $defaultValues['invn'] = urldecode($this->request->getQuery('invn'));
            $modelBind['invoiceNumber'] = $defaultValues['invn'];
            $booking->addWhere("invoiceNumber = :invoiceNumber:");
        }
        if ($this->request->hasQuery('bm')) {
            $defaultValues['bm'] = $this->request->getQuery('bm');
            $modelBind['memberId'] = $defaultValues['bm'];
            $booking->addWhere("memberId = :memberId:");
        }
        if ($this->request->hasQuery('bs')) {
            $defaultValues['bs'] = $this->request->getQuery('bs');
            $modelBind['bookingStatus'] = $defaultValues['bs'];
            $booking->addWhere("bookingStatus = :bookingStatus:");
        }
        if (count($defaultValues) > 0) {
            \Phalcon\Tag::setDefaults($defaultValues);
        }
        if (count($modelBind) > 0) {
            $booking->bind($modelBind);
        }
        $booking->order("bookingId DESC");
        $bookings = $booking->execute();

        $memberQuery = \Tourpage\Models\Booking::query();
        $bookingMembers = $memberQuery->execute();
        $bookedByMembers = [];
        if ($bookings->count() > 0) {
            foreach ($bookingMembers as $bMember) {
                if (!isset($bookedByMembers[$bMember->memberId])) {
                    $bookedByMembers[$bMember->memberId] = $bMember->member->getName() . ($bMember->member->isAgent() ? ' (Reg. Agent)' : '');
                }
            }
        }
        $pager = new \Tourpage\Library\Pager(array(
            "data" => $bookings,
            "page" => $page,
        ));
        $this->tag->prependTitle('Booking');
        $this->view->setVars(array(
            'pager' => $pager,
            'defaultValues' => $defaultValues,
            'bookedByMembers' => $bookedByMembers
        ));
    }

    /**
     * Action for view Booking Detials
     * @param int $bookingId
     * @return boolean
     */
    public function detailsAction($bookingId) {
        if (!preg_match_all('/[0-9]+/', $bookingId, $matches)) {
            return false;
        }
        $booking = \Tourpage\Models\Booking::findFirst($bookingId);
        if (!$booking) {
            return false;
        }
        $this->tag->setTitle('Booking Details');
        $this->view->setVar('booking', $booking);
    }

    /**
     * Action for remove booking
     * @param int $bookingId
     * @return boolean
     */
    public function removeAction($bookingId) {
        if (!preg_match_all('/[0-9]+/', $bookingId, $matches)) {
            return false;
        }
        $booking = \Tourpage\Models\Booking::findFirst($bookingId);
        if (!$booking) {
            return false;
        }
        $booking->removeData();
        $this->response->redirect('/admin/booking');
    }

}
