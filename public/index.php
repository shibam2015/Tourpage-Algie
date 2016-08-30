<?php

namespace Tourpage;

/*
 * Bootstrap File
 */
try {
    define('APP_PATH', realpath('..') . '/App');
    define('MODULES_DIR', '/Modules');
    define('CONFIG_DIR', '/Config/');
    define('COMMON_DIR', '/common/');
    
    define('FRONT_END_DIR', '/frontend/');
    define('BACK_END_DIR', '/backend/');
    define('VENDOR_DIR', '/vendor/');
    define('SUPPLIER_DIR', '/supplier/');
    
    define('RESOURCES_DIR', '/Resources/');
    define('APP_FRONT_END_DIR', '/Frontend/');
    define('APP_BACK_END_DIR', '/Backend/');
    define('APP_VENDOR_DIR', '/Vendor/');
    define('APP_SUPPLIER_DIR', '/Supplier/');

    define('CONFIG_PATH', APP_PATH . CONFIG_DIR);
    define('RESOURCES_PATH', APP_PATH . RESOURCES_DIR);
    define('FRONT_END_PATH', APP_PATH . MODULES_DIR . APP_FRONT_END_DIR);
    define('BACK_END_PATH', APP_PATH . MODULES_DIR . APP_BACK_END_DIR);
    define('VENDOR_PATH', APP_PATH . MODULES_DIR . APP_VENDOR_DIR);
    define('SUPPLIER_PATH', APP_PATH . MODULES_DIR . APP_SUPPLIER_DIR);

    require CONFIG_PATH . 'Loader.php';
    require CONFIG_PATH . 'Services.php';
    require CONFIG_PATH . 'Application.php';
} catch (\Phalcon\Exception $e) {
    echo "Exception: ", $e->getMessage();
}

