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
 * Class Index Controler
 * @author amit
 */
class IndexController extends BackendController {

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
    public function indexAction() {
        $months = [];
        $months['names'] = [];
        $months['bookings'] = [];
        foreach (\Tourpage\Helpers\Utils::getMonths() as $monthIndex => $monthTitle) {
            $months['names'][] = '"' . $monthTitle . '"';
            $months['bookings'][\Tourpage\Helpers\Utils::padInt($monthIndex)] = 0;
        }
        $bookings = \Tourpage\Models\Booking::find(array(
                    'conditions' => 'bookedOn BETWEEN :start_on: AND :end_to:',
                    'bind' => array(
                        'start_on' => \Tourpage\Helpers\Utils::__getCurrentYear() . '-01-01',
                        'end_to' => \Tourpage\Helpers\Utils::__getCurrentYear() . '-12-31'
                    )
        ));

        if ($bookings && $bookings->count() > 0) {
            foreach ($bookings as $booking) {
                $bookingMonth = date('m', strtotime($booking->bookedOn));
                if (!isset($months['bookings'][$bookingMonth])) {
                    $months['bookings'][$bookingMonth] = 0;
                }
                $months['bookings'][$bookingMonth] = $months['bookings'][$bookingMonth] + $booking->bookingAmount;
            }
        }

        $recentActivity = new \stdClass();

        $dateTimes = new \DateTime();
        $currentDate = $dateTimes->format('Y-m-d');
        $dateTimes->sub(new \DateInterval('P7D'));
        $previousDateSeven = $dateTimes->format('Y-m-d');

        $recentActivity->Bookings = \Tourpage\Models\Booking::find(array(
                    'conditions' => 'bookedOn BETWEEN :start_on: AND :end_to:',
                    'bind' => array(
                        'start_on' => $previousDateSeven,
                        'end_to' => $currentDate
                    ),
                    'order' => 'bookingId DESC',
                    'limit' => 10
        ));

        $recentActivity->Members = \Tourpage\Models\Members::find(array(
                    'conditions' => 'createdOn BETWEEN :start_on: AND :end_to:',
                    'bind' => array(
                        'start_on' => $previousDateSeven,
                        'end_to' => $currentDate
                    ),
                    'order' => 'memberId DESC',
                    'limit' => 10
        ));

        $recentActivity->Vendors = \Tourpage\Models\Vendors::find(array(
                    'conditions' => 'createdOn BETWEEN :start_on: AND :end_to: AND parentId = 0',
                    'bind' => array(
                        'start_on' => $previousDateSeven,
                        'end_to' => $currentDate
                    ),
                    'order' => 'vendorId DESC',
                    'limit' => 10
        ));

        $recentActivity->Tours = \Tourpage\Models\Tours::find(array(
                    'conditions' => 'tourCreatedOn BETWEEN :start_on: AND :end_to:',
                    'bind' => array(
                        'start_on' => $previousDateSeven,
                        'end_to' => $currentDate
                    ),
                    'order' => 'tourId DESC',
                    'limit' => 10
        ));

        $this->assets->collection('headerJs')->addJs(COMMON_DIR . 'js/highcharts/highcharts.js');
        $this->assets->collection('headerJs')->addJs(COMMON_DIR . 'js/highcharts/modules/exporting.js');
        $this->tag->prependTitle('Dashboard');
        $this->view->months = $months;
        $this->view->recentActivity = $recentActivity;
    }

}
