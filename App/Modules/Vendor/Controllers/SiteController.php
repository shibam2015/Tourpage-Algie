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

/*
 * Site Controller for some common and basic task
 * Like error handling, CMS pages etc.
 */

class SiteController extends VendorController {

    /**
     * 404 Error handler action
     */
    public function error404Action() {
        $this->initializeLayout('error');
        $this->response->setStatusCode(404, 'Page Not Found');
        $this->tag->setTitle('404');
        $this->view->code = 404;
        $this->view->message = 'Page Not Found';
    }

    /**
     * 500 Error handler action
     */
    public function error500Action() {
        $this->initializeLayout('error');
        $this->response->setStatusCode(500, 'Internal Server Error');
        $this->tag->setTitle('500');
        $this->view->code = 500;
        $this->view->message = 'Internal Server Error';
    }

}
