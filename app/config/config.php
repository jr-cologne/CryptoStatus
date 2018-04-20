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
 * config.php
 *
 * The Crypto Status config file
 * 
 */

const TWITTER_API_CONSUMER_KEY = 'TWITTER_API_CONSUMER_KEY';
const TWITTER_API_CONSUMER_SECRET = 'TWITTER_API_CONSUMER_SECRET';
const TWITTER_API_ACCESS_TOKEN = 'TWITTER_API_ACCESS_TOKEN';
const TWITTER_API_ACCESS_TOKEN_SECRET = 'TWITTER_API_ACCESS_TOKEN_SECRET';

const TWITTER_SCREENNAME = 'status_crypto';

const CRYPTO_API = 'https://api.coinmarketcap.com/v1/';
const CRYPTO_API_ENDPOINT = 'ticker/';
const CRYPTO_API_LIMIT = 10;

const BUGSNAG_API_KEY = 'BUGSNAG_API_KEY';
