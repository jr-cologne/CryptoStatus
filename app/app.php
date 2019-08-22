<?php

/**
 * A simple Twitter bot application which posts hourly status updates for the top 10 cryptocurrencies.
 *
 * PHP version >= 7.1
 *
 * LICENSE: MIT, see LICENSE file for more information
 *
 * @author JR Cologne <kontakt@jr-cologne.de>
 * @copyright 2019 JR Cologne
 * @license https://github.com/jr-cologne/CryptoStatus/blob/master/LICENSE MIT
 * @version v0.8.1-beta
 * @link https://github.com/jr-cologne/CryptoStatus GitHub Repository
 *
 * ________________________________________________________________________________
 *
 * app.php
 *
 * The main application file
 *
 */

require_once __DIR__ . '/../vendor/autoload.php';

use CryptoStatus\Config;
use CryptoStatus\CryptoStatus;
use CryptoStatus\Env;

$env = new Env;

if (!$env->isProduction()) {
    $env->loadEnvVarsFromDotenvFile(__DIR__ . '/../.env');
}

$app = new CryptoStatus(
    (new Config())->load(__DIR__ . '/config/config.php'),
    !$env->isProduction()
);

$app->run();
