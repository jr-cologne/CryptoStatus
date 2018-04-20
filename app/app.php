<?php

/**
 * A simple Twitter bot application which posts hourly status updates for the top 10 cryptocurrencies.
 *
 * PHP version >= 7.0
 *
 * LICENSE: MIT, see LICENSE file for more information
 *
 * @author JR Cologne <kontakt@jr-cologne.de>
 * @copyright 2018 JR Cologne
 * @license https://github.com/jr-cologne/CryptoStatus/blob/master/LICENSE MIT
 * @version v0.2.2
 * @link https://github.com/jr-cologne/CryptoStatus GitHub Repository
 *
 * ________________________________________________________________________________
 *
 * app.php
 *
 * The main application file
 * 
 */

require_once 'vendor/autoload.php';

use CryptoStatus\BugsnagClient;
use CryptoStatus\CryptoStatus;

// initialize error handling
$bugsnag_client = new BugsnagClient;

$app = new CryptoStatus;

// initialize app
$app->init();

// run app
$app->run();
