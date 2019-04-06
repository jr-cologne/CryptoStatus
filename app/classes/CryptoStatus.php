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
 * @version v0.6.0
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

use CryptoStatus\Exceptions\CryptoStatusException;

use Codebird\Codebird;

class CryptoStatus
{

    /**
     * The Config class instance
     *
     * @var Config $config
     */
    protected $config;

    /**
     * The Twitter client instance
     *
     * @var TwitterClient $twitter_client
     */
    protected $twitter_client;

    /**
     * The Crypto client instance
     *
     * @var CryptoClient $crypto_client
     */
    protected $crypto_client;

    /**
     * The Crypto data
     *
     * @var array $dataset
     */
    protected $dataset;

    /**
     * The IDs of the Tweets which need to be deleted because of an error
     *
     * @var array $failed_tweets
     */
    protected $failed_tweets = [];

    /**
     * CryptoStatus constructor.
     *
     * @param Config $config
     * @throws Exceptions\CryptoClientException
     * @throws Exceptions\TwitterClientException
     * @throws Exceptions\ConfigException
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->init();
    }

    /**
     * Initialize application
     *
     * @throws Exceptions\TwitterClientException
     * @throws Exceptions\CryptoClientException
     * @throws Exceptions\ConfigException
     */
    public function init()
    {
        $this->twitter_client = new TwitterClient(Codebird::getInstance(), $this->config->get('twitter.api'));

        $this->crypto_client = new CryptoClient(new CurlClient(), $this->getCryptoClientOptions());
    }

    /**
     * Run the application
     *
     * @throws CryptoStatusException
     * @throws Exceptions\CryptoClientException
     * @throws Exceptions\CurlClientException
     * @throws Exceptions\ConfigException
     */
    public function run()
    {
        $this->dataset = $this->getDataset();

        $this->formatTweetData();

        $tweets = $this->createTweets();

        if (!$this->postTweets($tweets)) {
            $this->deleteTweets($this->failed_tweets);

            throw new CryptoStatusException('Posting HourlyCryptoStatus failed');
        }
    }

    /**
     * Get the Crypto data
     *
     * @return array
     * @throws Exceptions\CryptoClientException
     * @throws Exceptions\CurlClientException
     */
    protected function getDataset() : array
    {
        return $this->crypto_client->getData();
    }

    /**
     * Format the Crypto data to an array of strings
     *
     * @throws CryptoStatusException if Crypto data is missing
     */
    protected function formatTweetData()
    {
        $this->dataset = array_map(function (array $data) {
            if ($this->necessaryFieldsAreSet($data)) {
                $data = $this->getBeautifiedData($data);
        
                return $this->getTweetDataString($data);
            }

            throw new CryptoStatusException('Crypto data is missing', 1);
        }, $this->dataset);
    }

    /**
     * Get beautified data for tweet
     *
     * @param array $data
     * @return array
     */
    protected function getBeautifiedData(array $data) : array
    {
        $data['id'] = $this->camelCase($data['id']);
        $data['current_price'] = $this->formatNumber($data['current_price']);
        $data['price_change_percentage_24h'] = $this->formatNumber($data['price_change_percentage_24h']);

        return $data;
    }
  
    /**
     * Convert string to camel case notation.
     *
     * @param string $str
     * @return string
     */
    protected function camelCase(string $str) : string
    {
        $camel_case_str = '';
        $capitalize = false;
    
        foreach (str_split($str) as $char) {
            if (ctype_space($char) || $char == '-') {
                $capitalize = true;
                continue;
            } elseif ($capitalize) {
                $char = strtoupper($char);
                $capitalize = false;
            }

            $camel_case_str .= $char;
        }
    
        return $camel_case_str;
    }

    /**
     * Format number
     *
     * @param string $number
     * @return string
     */
    protected function formatNumber(string $number) : string
    {
        return $this->removeTrailingZeros($this->twoDecimals($number));
    }

    /**
     * Format number to use only two decimals
     *
     * @param string $number
     * @return string
     */
    protected function twoDecimals(string $number) : string
    {
        return number_format($number, 2);
    }
  
    /**
     * Remove trailing zeros after decimal point from number
     *
     * @param string $number
     * @return string
     */
    protected function removeTrailingZeros(string $number) : string
    {
        $number_arr = array_reverse(str_split($number));
    
        foreach ($number_arr as $key => $value) {
            if (is_numeric($value) && $value == 0) {
                unset($number_arr[$key]);
            } else {
                if (!is_numeric($value)) {
                    unset($number_arr[$key]);
                }

                break;
            }
        }
    
        $number = implode(array_reverse($number_arr));
    
        return $number;
    }

    /**
     * Get formatted tweet data string
     *
     * @param array $data
     * @return string
     */
    protected function getTweetDataString(array $data) : string
    {
        return "#{$data['market_cap_rank']} "
            . "#{$data['symbol']} "
            . "(#{$data['id']}): "
            . "{$data['current_price']} USD | "
            . "{$data['price_change_percentage_24h']}% 24h";
    }

    /**
     * Create the Tweets with Crypto data and return them as an array
     *
     * @return array
     */
    protected function createTweets() : array
    {
        $tweets = [];
        $start_rank = 1;
        $end_rank = 3;
        $length = 3;

        for ($i = 0; $i < 3; $i++) {
            $tweets[$i] = "#HourlyCryptoStatus (#{$start_rank} to #{$end_rank}):\n\n";
            $tweets[$i] .= implode("\n\n", $this->getDataForSingleTweet($start_rank, $length));

            $start_rank += 3;
            $end_rank += 3;

            // last tweet consists of one cryptocurrency more
            if ($i == 1) {
                $end_rank++;
                $length++;
            }
        }

        return $tweets;
    }

    /**
     * Get data for a single tweet
     *
     * @param int $start_rank
     * @param int $length
     * @return array
     */
    protected function getDataForSingleTweet(int $start_rank, int $length)
    {
        return array_slice($this->dataset, $start_rank - 1, $length);
    }

    /**
     * Post the specified Tweets
     *
     * @param array $tweets The Tweets to post
     * @return bool
     * @throws Exceptions\ConfigException
     */
    protected function postTweets(array $tweets) : bool
    {
        $last_tweet_id = null;
        $tweet_ids = [];

        for ($i = 0; $i < 3; $i++) {
            if ($last_tweet_id) {   // not first tweet
                $tweet = $this->postTweet($tweets[$i], $last_tweet_id);
            } else {    // first tweet
                $tweet = $this->postTweet($tweets[$i]);
            }

            if (isset($tweet['id'])) {
                $tweet_ids[] = $last_tweet_id = $tweet['id'];
            } else {
                break;
            }
        }

        if (!empty($tweet_ids) && count($tweet_ids) == 3) {
            return true;
        } else {
            $this->failed_tweets = $tweet_ids;

            return false;
        }
    }

    /**
     * Post a single tweet
     *
     * @param string $status
     * @param int $reply_to
     * @return mixed
     * @throws Exceptions\ConfigException
     */
    protected function postTweet(string $status, $reply_to = 0)
    {
        $tweet = [
            'status' => '@' . $this->config->get('twitter.screen_name') . ' ' . $status,
        ];

        if ($reply_to === 0) {
            $tweet = array_merge($tweet, [
                'in_reply_to_status_id' => $reply_to,
            ]);
        }

        return $this->twitter_client->postTweet($tweet, [ 'id' ]);
    }

    /**
     * Delete the specified Tweets
     *
     * @param array $tweet_ids The IDs of the Tweets to delete
     * @throws CryptoStatusException if Tweets could not be deleted
     */
    protected function deleteTweets(array $tweet_ids)
    {
        $deleted_counter = 0;
        $failed_deletes = [];

        foreach ($tweet_ids as $tweet_id) {
            if ($this->deleteTweet($tweet_id)) {
                $deleted_counter++;
            } else {
                $failed_deletes[] = $tweet_id;
            }
        }

        if ($deleted_counter != count($tweet_ids)) {
            throw new CryptoStatusException('Failed to delete ' . implode(', ', $failed_deletes), 2);
        }
    }

    /**
     * Delete a single tweet
     *
     * @param $tweet_id
     * @return bool
     */
    protected function deleteTweet($tweet_id) : bool
    {
        return $this->twitter_client->deleteTweet($tweet_id);
    }

    /**
     * Get Crypto client options
     *
     * @return array
     * @throws Exceptions\ConfigException
     */
    protected function getCryptoClientOptions() : array
    {
        return [
            'api' => $this->config->get('crypto_api.url'),
            'endpoint' => $this->config->get('crypto_api.endpoint'),
            'params' => [
                'vs_currency' => $this->config->get('crypto_api.currency'),
                'order' => $this->config->get('crypto_api.order'),
                'per_page' => $this->config->get('crypto_api.limit'),
            ],
        ];
    }

    /**
     * Checks whether all necessary fields are set in the given dataset
     *
     * @param array $data
     * @return bool
     */
    protected function necessaryFieldsAreSet(array $data) : bool
    {
        return isset(
            $data['market_cap_rank'],
            $data['symbol'],
            $data['id'],
            $data['current_price'],
            $data['price_change_percentage_24h']
        );
    }
}
