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

namespace Tourpage\Helpers;

use Phalcon\DI;

/**
 * Description of Captcha
 * @author amit
 */
class Captcha {

    public static function RenderImage() {
        $imageId = 'captcha-img-' . uniqid();
        $captchaHtml = <<< EOD
<div class="captcha-wrapper">
    <img src="{url}" id="{imageId}" class="captcha-image" /><br/>
    <a href="#" onclick="document.getElementById('{imageId}').src='{url}?'+Math.random(); document.getElementById('{inputId}').focus(); return false;" id="{change-captcha}" class="captcha-change">
        <i class="fa fa-refresh"></i> {refreshMessage}
    </a>
</div>
EOD;
        $captchaHtml = strtr($captchaHtml, array(
            '{imageId}' => $imageId,
            '{inputId}' => 'captcha',
            '{url}' => DI::getDefault()->get('url')->get('/captcha'),
            '{change-captcha}' => $imageId . '-change',
            '{refreshMessage}' => 'Not readable? Change text.',
        ));

        echo $captchaHtml . '';
    }

}
