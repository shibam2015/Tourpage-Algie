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
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
//use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Between;

/**
 * Store Setting Form
 * @author amit
 */
class StoreSettingForm extends CForm {

    private function attachElementSlogan() {
        $storeSlogan = new Text('store_slogan');
        $storeSlogan->setLabel('Store Slogan');
        $storeSlogan->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->slogan)) {
                $storeSlogan->setDefault($this->entity->slogan);
            }
        }
        $this->add($storeSlogan);
    }

    private function attachElementEstd() {
        $storeEstd = new Select('store_estd');
        $storeEstd->setLabel('Established since');
        $storeEstd->setAttribute('class', 'form-control');
        $storeEstd->setDefault(\Tourpage\Helpers\Utils::__getCurrentYear());
        $storeEstd->setOptions(\Tourpage\Helpers\Utils::getYears());
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->estd)) {
                $storeEstd->setDefault($this->entity->estd);
            }
        }
        $this->add($storeEstd);
    }

    private function attachElementLogo() {
        $storeLogo = new File('store_logo');
        $storeLogo->setLabel('Store Logo');
        $storeLogo->setUserOptions(array('hints' => 'Supported file type are .JPG, .JPEG, .PNG, .GIF. Max allowed size 20KB and max allowed resolution 230px x 65px.'));
        $this->add($storeLogo);
    }
    private function attachAboutUsBanner() {
        $aboutUsBanner = new File('about_us_banner');
        $aboutUsBanner->setLabel('About Us Banner');
        //$aboutUsBanner->setUserOptions(array('hints' => 'Supported file type are .JPG, .JPEG, .PNG, .GIF. Max allowed size 20KB and max allowed resolution 230px x 65px.'));
        $this->add($aboutUsBanner);
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

    private function attachElementDefaultCommission() {
        $defaultCommission = new Text('default_commission');
        $defaultCommission->setLabel('Default Commission for Registered Agents');
        $defaultCommission->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->default_commission)) {
                $defaultCommission->setDefault($this->entity->default_commission);
            }
        }
        $defaultCommission->addValidators(array(
            new Numericality(array('message' => 'Commission must be numeric', 'allowEmpty' => true)),
            new Between(array('minimum' => 0, 'maximum' => 100, 'message' => 'Commission must be between 0 and 100', 'allowEmpty' => true))
        ));
        $this->add($defaultCommission);
    }

    private function attachElementAboutUs() {
        $storeAboutus = new TextArea('store_about_us');
        $storeAboutus->setLabel('About us');
        $storeAboutus->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->aboutUs)) {
		$value = !empty($this->entity->aboutUs) ? \Tourpage\Helpers\Utils::decodeString($this->entity->aboutUs) : $this->defaultAboutUs();
                $storeAboutus->setDefault($value);
            }
        }
        $this->add($storeAboutus);
    }
    
     private function attachElementAdvanceAboutUs() {
        $storeAboutus = new TextArea('store_advance_about_us');
        $storeAboutus->setLabel('About us(Advance)');
        $storeAboutus->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->aboutUsAdvance)) {
		$value = !empty($this->entity->aboutUsAdvance) ? \Tourpage\Helpers\Utils::decodeString($this->entity->aboutUsAdvance) : $this->defaultAboutUs();
                $storeAboutus->setDefault($value);
            }
        }
        $this->add($storeAboutus);
    }

    private function attachElementIntroduction() {
        $storeIntroduction = new TextArea('store_introduction');
        $storeIntroduction->setLabel('Store Introduction');
        $storeIntroduction->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->introduction)) {
                $storeIntroduction->setDefault(\Tourpage\Helpers\Utils::decodeString($this->entity->introduction));
            }
        }
        $this->add($storeIntroduction);
    }

    private function attachElementPolicy() {
        $storePolicy = new TextArea('store_policy');
        $storePolicy->setLabel('Terms & Conditions');
        $storePolicy->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->policy)) {
                $storePolicy->setDefault(\Tourpage\Helpers\Utils::decodeString($this->entity->policy));
            }
        }
        $this->add($storePolicy);
    }

    private function attachElementCancelPolicy() {
        $cancelPolicy = new TextArea('cancel_policy');
        $cancelPolicy->setLabel('Cancelation Policies');
        $cancelPolicy->setAttribute('class', 'form-control');
        if (isset($this->options['edit']) && $this->options['edit']) {
            if ($this->entity && isset($this->entity->cancelPolicy)) {
                $cancelPolicy->setDefault(\Tourpage\Helpers\Utils::decodeString($this->entity->cancelPolicy));
            }
        }
        $this->add($cancelPolicy);
    }

    private function attachElementSubmit() {
        $submitButton = new Submit('submit');
        $submitButton->setAttribute('id', 'submit');
        $submitButton->setAttribute('name', 'submit');
        $submitButton->setAttribute('value', 'Submit');
        $submitButton->setAttribute('class', 'btn btn-primary');
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
        $this->attachElementSlogan();
        $this->attachElementEstd();
        $this->attachElementLogo();
        $this->attachElementLinksFacebook();
        $this->attachElementLinksTwitter();
        $this->attachElementLinksInstagram();
        $this->attachElementDefaultCommission();
        $this->attachElementAboutUs();
         $this->attachElementAdvanceAboutUs();
        $this->attachAboutUsBanner();
        $this->attachElementIntroduction();
        $this->attachElementPolicy();
        $this->attachElementCancelPolicy();
        $this->attachElementSubmit();
    }

    private function defaultAboutUs()
    {
        return '<p><span style="font-size:20px"><strong>About Guitar Hero</strong></span></p>

<div>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p>
</div>

<p><span style="font-size:20px"><strong>Why explore with Guitar Hero?</strong>&nbsp;</span></p>

<div>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p>
</div>
';
    }

}
