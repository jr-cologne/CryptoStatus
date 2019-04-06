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
 * @version v0.5.0
 * @link https://github.com/jr-cologne/CryptoStatus GitHub Repository
 *
 * ________________________________________________________________________________
 *
 * config.php
 *
 * The Crypto Status config file
 *
 */

return [
    'twitter' => [
        'api' => [
            'consumer_key' => getenv('TWITTER_API_CONSUMER_KEY'),
            'consumer_secret' => getenv('TWITTER_API_CONSUMER_SECRET'),
            'access_token' => getenv('TWITTER_API_ACCESS_TOKEN'),
            'access_token_secret' => getenv('TWITTER_API_ACCESS_TOKEN_SECRET'),
        ],
        'screen_name' => 'status_crypto',
    ],
    'crypto_api' => [
        'url' => 'https://api.coingecko.com/api/v3/',
        'endpoint' => 'coins/markets',
        'currency' => 'usd',
        'order' => 'market_cap_desc',
        'limit' => 10,
    ],
];
