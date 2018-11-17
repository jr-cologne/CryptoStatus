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
 * @version v0.4.1
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
     * The Mail client instance
     *
     * @var MailClient $mail_client
     */
    protected $mail_client;

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
     * Initialize application
     *
     * @throws Exceptions\TwitterClientException
     * @throws Exceptions\CryptoClientException
     * @throws Exceptions\MailClientException
     */
    public function init()
    {
        $this->twitter_client = new TwitterClient(Codebird::getInstance());

        $this->crypto_client = new CryptoClient(new CurlClient(), [
            'api' => CRYPTO_API,
            'endpoint' => CRYPTO_API_ENDPOINT,
            'params' => [
                'vs_currency' => CRYPTO_API_CURRENCY,
                'order' => CRYPTO_API_ORDER,
                'per_page' => CRYPTO_API_LIMIT
            ]
        ]);

        $this->mail_client = new MailClient([
            'smtp_server' => SMTP_SERVER,
            'smtp_port' => SMTP_PORT,
            'smtp_encryption' => SMTP_ENCRYPTION,
            'smtp_username' => getenv(SMTP_USERNAME),
            'smtp_password' => getenv(SMTP_PASSWORD)
        ]);
    }

    /**
     * Run the application
     *
     * @throws CryptoStatusException
     * @throws Exceptions\CryptoClientException
     * @throws Exceptions\CurlClientException
     * @throws Exceptions\MailClientException
     * @throws Exceptions\TwitterClientException
     */
    public function run()
    {
        $this->init();

        $this->dataset = $this->getDataset();

        $this->formatData();

        $tweets = $this->createTweets();

        if (!$this->postTweets($tweets)) {
            $this->deleteTweets($this->failed_tweets);
            $this->sendNotificationMail();
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
    protected function formatData()
    {
        $this->dataset = array_map(function (array $data) {
            if ($this->necessaryFieldsAreSet($data)) {
                $data['id'] = $this->camelCase($data['id']);
                $data['current_price'] = $this->formatNumber($data['current_price']);
                $data['price_change_percentage_24h'] = $this->formatNumber($data['price_change_percentage_24h']);
        
                return "#{$data['market_cap_rank']} "
                    . "#{$data['symbol']} "
                    . "(#{$data['id']}): "
                    . "{$data['current_price']} USD | "
                    . "{$data['price_change_percentage_24h']}% 24h";
            }

            throw new CryptoStatusException('Crypto data is missing', 1);
        }, $this->dataset);
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
            $tweets[$i] .= implode("\n\n", array_slice($this->dataset, $start_rank - 1, $length));

            $start_rank += 3;
            $end_rank += 3;

            if ($i == 1) {
                $end_rank++;
                $length++;
            }
        }

        return $tweets;
    }

    /**
     * Post the specified Tweets
     *
     * @param array $tweets The Tweets to post
     * @return bool
     */
    protected function postTweets(array $tweets) : bool
    {
        $last_tweet_id = null;
        $tweet_ids = [];

        for ($i = 0; $i < 3; $i++) {
            if ($last_tweet_id) {
                $tweet = $this->twitter_client->postTweet([
                    'status' => '@' . TWITTER_SCREENNAME . ' ' . $tweets[$i],
                    'in_reply_to_status_id' => $last_tweet_id
                ], [ 'id' ]);
            } else {
                $tweet = $this->twitter_client->postTweet([
                    'status' => $tweets[$i]
                ], [ 'id' ]);
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
            if ($this->twitter_client->deleteTweet($tweet_id)) {
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
     * Send notification mail
     *
     * @return bool
     * @throws Exceptions\MailClientException
     */
    protected function sendNotificationMail() : bool
    {
        return $this->mail_client->message([
            'from' => getenv(NOTIFICATION_MAIL_FROM),
            'to' => getenv(NOTIFICATION_MAIL_TO),
            'subject' => NOTIFICATION_MAIL_SUBJECT,
            'body' => NOTIFICATION_MAIL_BODY
        ])->send();
    }

    /**
     * Checks whether all necessary fields are set in the given dataset
     *
     * @param array $data
     * @return bool
     */
    private function necessaryFieldsAreSet(array $data) : bool
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
