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

use Phalcon\DI\FactoryDefault;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Db\Adapter\Pdo\Mysql as DbMysqlAdapter;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Http\Response\Cookies;

// Create a DI
$di = new FactoryDefault();

// Setup the database service
$di->set('db', function() {
    $request = new \Phalcon\Http\Request();
    if ($request->getServer('HTTP_HOST') === 'localhost') {
        return new DbMysqlAdapter(array(
            "host" => "localhost",
            "username" => "root",
            "password" => "",
            "dbname" => "tourpage"
        ));
    } else {
        return new DbMysqlAdapter(array(
            "host" => "localhost",
            "username" => "root",
            "password" => "dev@123!",
            "dbname" => "tourpage"
        ));
    }
});

// Setup a base URI so that all generated URIs include the "tutorial" folder
$di->set('url', function() {
    $url = new UrlProvider();
    //$url->setBasePath(str_replace('/App/Config', '', __DIR__));
	// edited
	$url->setBasePath(str_replace('/App/Config', '', __DIR__));
       $url->setBaseUri(strtolower(dirname($_SERVER['SERVER_PROTOCOL'])) . '://' . $_SERVER['HTTP_HOST'] . str_replace('/public', '', dirname($_SERVER['PHP_SELF'])));
      $url->setStaticBaseUri($url->getBaseUri() . '/public/elements');
    return $url;
});

//$di->set('url', function () {
//        $url = new UrlProvider();
//        $url->setBaseUri('/Tour_Page/');
//        return $url;
//    });

// If the configuration specify the use of metadata adapter use it or use memory otherwise
$di->set('modelsMetadata', function() {
    return new MetaData();
});

// Start the session the first time some component request the session service
$di->set('session', function() {
    $session = new SessionAdapter();
    $session->setOptions(array(
        'uniqueId' => '63ff23acdd38a28de7682ec117c6a402'
    ));
    $session->start();
    return $session;
});

// Register the flash service with custom CSS classes
$di->set('flash', function() {
    return new FlashSession(array(
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice' => 'alert alert-info',
        'warning' => 'alert alert-warning',
    ));
});

// Register cookies service
$di->set('cookies', function() {
    $cookies = new Cookies();
    $cookies->useEncryption(false);
    return $cookies;
});

// Setup Route
$di->set('router', function () {
    require CONFIG_PATH . 'Routes.php';
    return $router;
});
