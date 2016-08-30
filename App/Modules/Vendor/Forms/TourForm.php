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

namespace Multiple\Vendor\Forms;

use Tourpage\Forms\CForm;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\Digit as DigitValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Regex as RegexValidator;

/**
 * Tour Form
 * @author amit
 */
class TourForm extends CForm {

    private function attachElementVendor() {
        $tourVendor = new Hidden('vendor');
        $tourVendor->setDefault($this->vendors->getId());
        $this->add($tourVendor);
    }

    /**
     * Element for Tour Title
     */
    private function attachElementTitle() {
        $tourTitle = new Text('tour_title');
        $tourTitle->setLabel('Tour Title');
        $tourTitle->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourTitle)) {
                $tourTitle->setDefault($this->entity->tourTitle);
            }
            $tourTitle->setAttribute('disabled', 'disabled');
        } else {
            $tourTitle->addValidators(array(
                new PresenceOf(array('message' => 'Tour title is required'))
            ));
        }
        $this->add($tourTitle);
    }

    /**
     * Element for Tour Sub Title
     */
    private function attachElementSubTitle() {
        $tourSubTitle = new Text('tour_sub_title');
        $tourSubTitle->setLabel('Tour Sub Title');
        $tourSubTitle->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourSubTitle)) {
                $tourSubTitle->setDefault($this->entity->tourSubTitle);
            }
            $tourSubTitle->setAttribute('disabled', 'disabled');
        }
        $this->add($tourSubTitle);
    }

    private function attachElementCountry() {
        $formCountry = ['' => 'Please choose one...'];
        $countryData = \Tourpage\Models\Country::query();
        $countryData->columns(array('countryId', 'name'));
        $countryData->where("status = :status:", array('status' => \Tourpage\Models\Country::ACTIVE_STATUS_CODE));
        $countries = $countryData->execute();
        if ($countries->count() > 0) {
            foreach ($countries as $countryDp) {
                $formCountry[$countryDp->countryId] = $countryDp->name;
            }
        }
        $tourCountry = new Select('tour_country');
        $tourCountry->setOptions($formCountry);
        $tourCountry->setLabel('Country');
        $tourCountry->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourCountryId)) {
                $tourCountry->setDefault($this->entity->tourCountryId);
            }
            $tourCountry->setAttribute('disabled', 'disabled');
        } else {
            $tourCountry->addValidators(array(
                new PresenceOf(array('message' => 'Country is required')),
            ));
        }
        $this->add($tourCountry);
    }

    private function attachElementState() {
        $formState = ['' => 'Please choose one...'];
        $tourState = new Select('tour_state');
        $tourState->setLabel('Province / State / County');
        $tourState->setAttribute('class', 'form-control');
        $tourSelectCountry = $tourSelectState = 0;
        if (isset($this->options['edit']) && $this->options['edit']) {
            $tourSelectCountry = $this->entity->tourCountryId;
            $tourSelectState = $this->entity->tourStateId;
        }
        if ($this->request->isPost()) {
            $tourSelectCountry = $this->request->getPost('tour_country');
            $tourSelectState = $this->request->getPost('tour_state');
        }
        if ($this->request->isPost() || (isset($this->options['edit']) && $this->options['edit'])) {
            $states = \Tourpage\Models\State::find(array(
                        "countryId = :country_id: AND status = :state_status:",
                        "bind" => array(
                            ":country_id" => $tourSelectCountry,
                            ":state_status" => \Tourpage\Models\State::ACTIVE_STATUS_CODE
                        )
            ));
            if ($states && $states->count() > 0) {
                foreach ($states as $state) {
                    $formState[$state->stateId] = $state->name;
                }
            }
        }
        $tourState->setOptions($formState);
        if ($tourSelectState > 0) {
            $tourState->setDefault($tourSelectState);
        }
        if (isset($this->options['edit']) && $this->options['edit']) {
            $tourState->setAttribute('disabled', 'disabled');
        } else {
            $tourState->addValidators(array(
                new PresenceOf(array('message' => 'State is required')),
            ));
        }
        $this->add($tourState);
    }

    private function attachElementCity() {
        $tourCity = new Text('tour_city');
        $tourCity->setLabel('City');
        $tourCity->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourCity)) {
                $tourCity->setDefault($this->entity->tourCity);
            }
            $tourCity->setAttribute('disabled', 'disabled');
        } else {
            $tourCity->addValidators(array(
                new PresenceOf(array('message' => 'City is required')),
            ));
        }
        $this->add($tourCity);
    }

    /**
     * Element for Tour Status
     */
    private function attachElementStatus() {
        $tourStatus = new Select('tour_status');
        $tourStatus->setLabel('Tour Status');
        $tourStatus->setAttribute('class', 'form-control');
        $tourStatus->setDefault(\Tourpage\Models\VendorsTours::INACTIVE_STATUS_CODE);
        $tourStatus->setOptions(array(
            \Tourpage\Models\VendorsTours::ACTIVE_STATUS_CODE => 'Active',
            \Tourpage\Models\VendorsTours::INACTIVE_STATUS_CODE => 'Inactive'
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourVendor->status)) {
                $tourStatus->setDefault($this->entity->tourVendor->status);
            }
        }
        $this->add($tourStatus);
    }

    /**
     * Element for Tour Booking Status
     */
    private function attachElementBookingStatus() {
        $tourBookingStatus = new Select('tour_booking_status');
        $tourBookingStatus->setLabel('Tour Booking Status');
        $tourBookingStatus->setAttribute('class', 'form-control');
        $tourBookingStatus->setDefault(\Tourpage\Models\Tours::BOOKING_UPCOMMING_STATUS_CODE);
        $tourBookingStatus->setOptions(array(
            \Tourpage\Models\Tours::BOOKING_OPEN_STATUS_CODE => 'Open',
            \Tourpage\Models\Tours::BOOKING_CLOSE_STATUS_CODE => 'Close',
            \Tourpage\Models\Tours::BOOKING_UPCOMMING_STATUS_CODE => 'Up Comming'
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourBookingStatus)) {
                $tourBookingStatus->setDefault($this->entity->tourBookingStatus);
            }
        }
        $this->add($tourBookingStatus);
    }

    /**
     * Element for Tour Category
     */
    private function attachElementCategory() {
        $tourCategory = new Check('tour_category[]');
        $this->add($tourCategory);
    }

    /**
     * Element for Tour Start Date
     */
    private function attachElementStartDate() {
        $tourStartDate = new Text('tour_start_date');
        $tourStartDate->setLabel('Start Date');
        $tourStartDate->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourStartFrom)) {
                $tourStartDate->setDefault(\Tourpage\Helpers\Utils::formatMySqlToDatepicker($this->entity->tourStartFrom));
            }
        } else {
            $tourStartDate->addValidators(array(
                new RegexValidator(array('pattern' => '/^(0\d|1[0-2])\/([0-2]\d|3[0-1])\/[0-9]{4}$/', 'message' => 'Invalid Start Date')),
            ));
        }
        $this->add($tourStartDate);
    }

    /**
     * Element for Tour End Date
     */
    private function attachElementEndDate() {
        $tourEndDate = new Text('tour_end_date');
        $tourEndDate->setLabel('End Date');
        $tourEndDate->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourEndTo)) {
                $tourEndDate->setDefault(\Tourpage\Helpers\Utils::formatMySqlToDatepicker($this->entity->tourEndTo));
            }
        } else {
            $tourEndDate->addValidators(array(
                new RegexValidator(array('pattern' => '/^(0\d|1[0-2])\/([0-2]\d|3[0-1])\/[0-9]{4}$/', 'message' => 'Invalid End Date')),
            ));
        }
        $this->add($tourEndDate);
    }

    /**
     * Element for Tour Length
     */
    private function attachElementLength() {
        $tourLength = new Select('tour_length');
        $tourLength->setLabel('Tour type');
        $tourLength->setAttribute('class', 'form-control');
        $tourLength->setOptions(array(
            '' => '-- Select tour duration type --',
            \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_CODE => \Tourpage\Models\Tours::LENGTH_MULTIPLE_DAYS_TEXT,
            \Tourpage\Models\Tours::LENGTH_FIXED_DAYS_CODE => \Tourpage\Models\Tours::LENGTH_FIXED_DAYS_TEXT,
            \Tourpage\Models\Tours::LENGTH_SINGLE_DAY_CODE => \Tourpage\Models\Tours::LENGTH_SINGLE_DAY_TEXT,
            \Tourpage\Models\Tours::LENGTH_HOURLY => \Tourpage\Models\Tours::LENGTH_HOURLY_TEXT
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourDuration->lengthType)) {
                $tourLength->setDefault($this->entity->tourDuration->lengthType);
            }
        } else {
            $tourLength->addValidators(array(
                new PresenceOf(array('message' => 'Tour type is required'))
            ));
        }
        $this->add($tourLength);
    }

    /**
     * Element for Tour Duration
     */
    private function attachElementDuration() {
        $tourMultiDaysDuration = new Text('lmd_duration');
        $tourMultiDaysDuration->setLabel('Duration - Total day(s) [*Numbers only]');
        $tourMultiDaysDuration->setAttribute('class', 'form-control');
        $tourMultiDaysDuration->setAttribute('placeholder', 'Total day(s)');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourDuration->totalDays)) {
                $tourMultiDaysDuration->setDefault($this->entity->tourDuration->totalDays);
            }
        }
        $tourMultiDaysDuration->addValidators(array(
            new DigitValidator(array('message' => 'Tour duration must be numeric', 'allowEmpty' => true))
        ));
        $this->add($tourMultiDaysDuration);
    }

    /**
     * Element for Tour Descriptions
     */
    private function attachElementDescriptionKeys() {
        $toursDescriptionKeys = new Check('tour_description_keys[]');
        $this->add($toursDescriptionKeys);
    }

    /**
     * Element for Tour Price
     */
    private function attachElementPrice() {
        $tourPrice = new Text('tour_price');
        $tourPrice->setLabel('Tour Price');
        $tourPrice->setAttribute('class', 'form-control');
        $tourPrice->setAttribute('placeholder', '0.00');
        $tourPrice->addValidators(array(
            new Numericality(array('message' => 'Tour price must be numeric', 'allowEmpty' => true))
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourPrice->data->priceDefault)) {
                $tourPrice->setDefault($this->entity->tourPrice->data->priceDefault);
            }
        }
        $this->add($tourPrice);
    }

    /**
     * Element for Tour Price Discount
     */
    private function attachElementDiscount() {
        $disable = TRUE;
        $tourDiscount = new Text('tour_discount');
        $tourDiscount->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && !empty($this->entity->tourPrice->data->discount->price)) {
                if ($this->entity->tourPrice->hasDiscount(TRUE)) {
                    $tourDiscount->setDefault($this->entity->tourPrice->data->discount->price);
                }
            }
        } else {
            if ($this->request->isPost()) {
                if ($this->request->hasPost('special_disc_enb')) {
                    $disable = FALSE;
                }
            }
        }
       /* if ($disable) {
            $tourDiscount->setAttribute('disabled', 'disabled');
        }*/
        $this->add($tourDiscount);
    }

    /**
     * Element for Tour Price Discount Start
     */
    private function attachElementDiscountStart() {
        $disable = TRUE;
        $tourDiscountStart = new Text('tour_discount_start');
        $tourDiscountStart->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && !empty($this->entity->tourPrice->data->discount->start)) {
                if ($this->entity->tourPrice->hasDiscount(TRUE)) {
                    $tourDiscountStart->setDefault(\Tourpage\Helpers\Utils::formatMySqlToDatepicker($this->entity->tourPrice->data->discount->start));
                }
            }
        } else {
            if ($this->request->isPost()) {
                if ($this->request->hasPost('special_disc_enb')) {
                    $disable = FALSE;
                }
            }
        }
        if ($disable) {
            $tourDiscountStart->setAttribute('disabled', 'disabled');
        }
        $this->add($tourDiscountStart);
    }

    /**
     * Element for Tour Price Discount End
     */
    private function attachElementDiscountEnd() {
        $disable = TRUE;
        $tourDiscountEnd = new Text('tour_discount_end');
        $tourDiscountEnd->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && !empty($this->entity->tourPrice->data->discount->end)) {
                if ($this->entity->tourPrice->hasDiscount(TRUE)) {
                    $tourDiscountEnd->setDefault(\Tourpage\Helpers\Utils::formatMySqlToDatepicker($this->entity->tourPrice->data->discount->end));
                }
            }
        } else {
            if ($this->request->isPost()) {
                if ($this->request->hasPost('special_disc_enb')) {
                    $disable = FALSE;
                }
            }
        }
        if ($disable) {
            $tourDiscountEnd->setAttribute('disabled', 'disabled');
        }
        $this->add($tourDiscountEnd);
    }

    /**
     * Element for Tour Multiple Purches Discount Percentage
     */
    private function attachElementMpDiscount() {
        $tourMpDiscount = new Text('tour_mp_discount');
        $tourMpDiscount->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourPrice->data->discount->multiplePurchase->percentage)) {
                $tourMpDiscount->setDefault($this->entity->tourPrice->data->discount->multiplePurchase->percentage);
            }
            $tourMpDiscount->setAttribute('disabled', 'disabled');
        } else {
            $tourMpDiscount->addValidators(array(
                new Numericality(array('message' => 'Tour multiple purchase discount must be numeric', 'allowEmpty' => true)),
                new Between(array('minimum' => 0, 'maximum' => 100, 'message' => 'Tour multiple purchase discount must be between 0 and 100'))
            ));
        }
        $this->add($tourMpDiscount);
    }

    /**
     * Element for Tour Multiple Purches Discount Head Count 
     */
    private function attachElementMpCount() {
        $tourMpCount = new Text('tour_mp_count');
        $tourMpCount->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourPrice->data->discount->multiplePurchase->count)) {
                $tourMpCount->setDefault($this->entity->tourPrice->data->discount->multiplePurchase->count);
            }
            $tourMpCount->setAttribute('disabled', 'disabled');
        } else {
            $tourMpCount->addValidators(array(
                new DigitValidator(array('message' => 'Tour number of purchase must be numeric', 'allowEmpty' => true))
            ));
        }
        $this->add($tourMpCount);
    }

    /**
     * Element for Tour Price Type
     */
    private function attachElementPriceType() {
        $tourPriceType = new Select('tour_price_type');
        $tourPriceType->setLabel('Tour Price Type');
        $tourPriceType->setAttribute('class', 'form-control');
        $tourPriceType->setOptions(array(
            \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_CODE => \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_PERSON_TEXT,
            \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_CODE => \Tourpage\Models\ToursPrice::PRICE_TYPE_PER_GROUP_TEXT,
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourPrice->data->priceType)) {
                $tourPriceType->setDefault($this->entity->tourPrice->data->priceType);
            }
        }
        $this->add($tourPriceType);
    }

    /**
     * Element for Tour Capacity
     */
    private function attachElementCapacity() {
        $tourCapacity = new Text('tour_capacity');
        $tourCapacity->setLabel('Tour Capacity');
        $tourCapacity->setAttribute('class', 'form-control');
        $tourCapacity->setDefault(0);
        $tourCapacity->setUserOptions(array('hints' => '0 - no limit or number of max booking capacity'));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourCapacity)) {
                $tourCapacityCount = $this->entity->tourCapacity;
                if ($tourCapacityCount < 0) {
                    $tourCapacityCount = 0;
                }
                $tourCapacity->setDefault($tourCapacityCount);
            }
        } else {
            $tourCapacity->addValidators(array(
                new Numericality(array('message' => 'Tour Capacity must be numeric'))
            ));
        }
        $this->add($tourCapacity);
    }

    /**
     * Element for Tour Keyword
     */
    private function attachElementKeyword() {
        $tourKeyword = new TextArea('tour_keyword');
        $tourKeyword->setLabel('Tour Keywords');
        $tourKeyword->setAttribute('class', 'form-control');
        $tourKeyword->setUserOptions(array('hints' => 'Enter your keywords seperated with coma(,)'));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourKeyword)) {
                $tourKeyword->setDefault(\Tourpage\Helpers\Utils::decodeString($this->entity->tourKeyword));
            }
        }
        $this->add($tourKeyword);
    }

    private function attachElementLinksFacebook() {
        $socialMediaFacebook = new Text('social_media_links_facebook');
        $socialMediaFacebook->setAttribute('class', 'form-control input-sm');
        $socialMediaFacebook->setAttribute('placeholder', 'Facebook link');
        $socialMediaFacebook->addValidators(array(
            new UrlValidator(array('message' => 'Facebook link is not a valid url', 'allowEmpty' => true))
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->sm->links)) {
                if (isset($this->entity->sm->links['facebook']) && !empty($this->entity->sm->links['facebook'])) {
                    $socialMediaFacebook->setDefault($this->entity->sm->links['facebook']);
                }
            }
        }
        $this->add($socialMediaFacebook);
    }

    private function attachElementLinksTwitter() {
        $socialMediaTwitter = new Text('social_media_links_twitter');
        $socialMediaTwitter->setAttribute('class', 'form-control input-sm');
        $socialMediaTwitter->setAttribute('placeholder', 'Twitter link');
        $socialMediaTwitter->addValidators(array(
            new UrlValidator(array('message' => 'Twitter link is not a valid url', 'allowEmpty' => true))
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->sm->links)) {
                if (isset($this->entity->sm->links['twitter']) && !empty($this->entity->sm->links['twitter'])) {
                    $socialMediaTwitter->setDefault($this->entity->sm->links['twitter']);
                }
            }
        }
        $this->add($socialMediaTwitter);
    }

    private function attachElementLinksInstagram() {
        $socialMediaInstagram = new Text('social_media_links_instagram');
        $socialMediaInstagram->setAttribute('class', 'form-control input-sm');
        $socialMediaInstagram->setAttribute('placeholder', 'Instagram link');
        $socialMediaInstagram->addValidators(array(
            new UrlValidator(array('message' => 'Instagram link is not a valid url', 'allowEmpty' => true))
        ));
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->sm->links)) {
                if (isset($this->entity->sm->links['instagram']) && !empty($this->entity->sm->links['instagram'])) {
                    $socialMediaInstagram->setDefault($this->entity->sm->links['instagram']);
                }
            }
        }
        $this->add($socialMediaInstagram);
    }

    private function attachElementPolicy() {
        $tourPolicy = new TextArea('tour_policy');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->tourPolicy)) {
                $tourPolicy->setDefault(\Tourpage\Helpers\Utils::decodeString($this->entity->tourPolicy));
            }
        }
        $this->add($tourPolicy);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('tsubmit');
        $submitButton->setAttribute('id', 'tsubmit');
        $submitButton->setAttribute('name', 'tsubmit');
        $submitButton->setAttribute('value', 'Submit');
        $submitButton->setAttribute('class', 'btn btn-danger');
        $this->add($submitButton);
    }

    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = null, $options = null) {
        $this->setFormEntity($entity);
        $this->setFormOptions($options);
        $this->attachElementVendor();
        $this->attachElementTitle();
        $this->attachElementSubTitle();
        $this->attachElementStatus();
        $this->attachElementCountry();
        $this->attachElementState();
        $this->attachElementCity();
        $this->attachElementBookingStatus();
        $this->attachElementCategory();
        $this->attachElementStartDate();
        $this->attachElementEndDate();
        $this->attachElementLength();
        $this->attachElementDuration();
        $this->attachElementDescriptionKeys();
        $this->attachElementPrice();
        $this->attachElementDiscount();
        $this->attachElementDiscountStart();
        $this->attachElementDiscountEnd();
        $this->attachElementMpDiscount();
        $this->attachElementMpCount();
        $this->attachElementPriceType();
        $this->attachElementCapacity();
        $this->attachElementKeyword();
        $this->attachElementLinksFacebook();
        $this->attachElementLinksTwitter();
        $this->attachElementLinksInstagram();
        $this->attachElementPolicy();
        $this->attachElementSubmit();
    }

    public function renderCategory($options = array()) {
        global $categoryString;
        $categoryString = '';
        $categoryData = \Tourpage\Models\CategoryTour::find(array(
                    'categoryParentId = :parent_id: AND categoryStatus = :status:',
                    'bind' => array('parent_id' => 0, 'status' => \Tourpage\Models\CategoryTour::ACTIVE_STATUS_CODE)
        ));
        $this->rederCategoryRecursive($categoryData, 'form-category', $options);
        echo $categoryString;
    }

    private function rederCategoryRecursive($categoryData, $class = 'form-category', $options = array()) {
        global $categoryString;
        if ($categoryData->count() > 0) {
            foreach ($categoryData as $category) {
                $attributes = array(
                    'value' => $category->categoryId
                );
                if ($this->request->getPost('tour_category')) {
                    $categoryPost = $this->request->getPost('tour_category');
                    if (in_array($category->categoryId, $categoryPost)) {
                        $attributes['checked'] = 'checked';
                    }
                }
                if (isset($options['edit']) && $options['edit']) {
                    if (isset($options['ids']) && count($options['ids']) > 0) {
                        if (in_array($category->categoryId, $options['ids'])) {
                            $attributes['checked'] = 'checked';
                        }
                    }
                }

                $categoryString .= '<div class="' . $class . '"><label>';
                $categoryString .= $this->render('tour_category[]', $attributes) . ' ' . $category->categoryTitle;
                $categoryString .= '</label>';
                if ($category->hasChild()) {
                    $categoryChild = \Tourpage\Models\CategoryTour::find(array(
                                'categoryParentId = :parent_id: AND categoryStatus = :status:',
                                'bind' => array('parent_id' => $category->categoryId, 'status' => \Tourpage\Models\CategoryTour::ACTIVE_STATUS_CODE)
                    ));
                    $this->rederCategoryRecursive($categoryChild, 'form-sub-category', $options);
                }
                $categoryString .= '</div>';
            }
        }
    }

}
