<?php
declare(strict_types=1);

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
 * @version v0.3.0
 * @link https://github.com/jr-cologne/CryptoStatus GitHub Repository
 *
 * ________________________________________________________________________________
 *
 * CryptoStatus.php
 *
 * The main class of the application.
 *
 */

namespace CryptoStatus;

/**
 * abstract class for all CryptoStatus relevant exceptions so they can be caught separately from system exceptions
 * @package CryptoStatus
 */
abstract class Exception extends \Exception
{

}