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
 * @version v0.6.8-beta
 * @link https://github.com/jr-cologne/CryptoStatus GitHub Repository
 *
 * ________________________________________________________________________________
 *
 * TwitterClient.php
 *
 * The client for interacting with the Twitter API.
 *
 */

namespace CryptoStatus;

use CryptoStatus\Exceptions\TwitterClientException;

use Codebird\Codebird;

class TwitterClient
{

    /**
     * A Codebird instance (a Twitter client library for PHP)
     *
     * @var Codebird $client
     */
    protected $client;

    /**
     * The Twitter API keys
     *
     * @var array $api_keys
     */
    protected $api_keys;

    /**
     * Constructor, initialization and authentication with Twitter API
     *
     * @param Codebird $twitter_client A Codebird instance
     * @param array $api_keys
     * @throws TwitterClientException if authentication with Twitter API failed
     */
    public function __construct(Codebird $twitter_client, array $api_keys)
    {
        $this->client = $twitter_client;

        $this->setApiKeys($api_keys);

        if (!$this->authenticate()) {
            throw new TwitterClientException("Authentication with Twitter API failed", 2);
        }
    }

    /**
     * Post a Tweet
     *
     * @param  array $params Parameters for Twitter API method statuses/update
     * @param  array $return Data to return from Twitter API reply
     * @return mixed boolean (default) or array (when $return is specified)
     */
    public function postTweet(array $params, array $return = [])
    {
        $reply = $this->client->statuses_update($params);
    
        if ($reply->httpstatus == 200) {
            if (!empty($return)) {
                return $this->getReturnData($return, $reply);
            }

            return true;
        }

        return false;
    }

    /**
     * Get return data from twitter's api reply
     *
     * @param array $return
     * @param $reply
     * @return array
     */
    protected function getReturnData(array $return, $reply) : array
    {
        $return_data = [];

        foreach ($return as $value) {
            $return_data[$value] = $reply->{$value};
        }

        return $return_data;
    }

    /**
     * Delete a Tweet
     *
     * @param string $id ID of the Tweet to delete
     * @return bool
     */
    public function deleteTweet(string $id) : bool
    {
        $reply = $this->client->statuses_destroy_ID([ 'id' => $id ]);

        if ($reply->httpstatus == 200) {
            return true;
        }

        return false;
    }

    /**
     * Set Twitter API keys
     *
     * @param array $api_keys
     * @throws TwitterClientException if Twitter API keys could not be retrieved
     */
    protected function setApiKeys(array $api_keys)
    {
        if (empty($api_keys['consumer_key']) ||
            empty($api_keys['consumer_secret']) ||
            empty($api_keys['access_token']) ||
            empty($api_keys['access_token_secret'])
        ) {
            throw new TwitterClientException("Could not get Twitter API Keys", 1);
        }

        $this->api_keys = $api_keys;
    }

    /**
     * Authenticate with Twitter API
     *
     * @return bool
     */
    protected function authenticate() : bool
    {
        $this->client::setConsumerKey($this->api_keys['consumer_key'], $this->api_keys['consumer_secret']);

        $this->client->setToken($this->api_keys['access_token'], $this->api_keys['access_token_secret']);

        return true;
    }
}
