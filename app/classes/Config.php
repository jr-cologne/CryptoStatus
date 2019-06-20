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
 * @version v0.7.0-beta
 * @link https://github.com/jr-cologne/CryptoStatus GitHub Repository
 *
 * ________________________________________________________________________________
 *
 * Config.php
 *
 * The Config class for dealing with configurations settings of the application.
 *
 */

namespace CryptoStatus;

use CryptoStatus\Exceptions\ConfigException;

class Config
{

    /**
     * @var array $config
     */
    protected $config = [];

    /**
     * Load settings from config file
     *
     * @param string $file
     * @return Config
     * @throws ConfigException
     */
    public function load(string $file) : self
    {
        $this->config = include($file);

        if (!is_array($this->config)) {
            throw new ConfigException('Loading config failed');
        }

        return $this;
    }

    /**
     * Retrieve configuration setting
     *
     * @param string $path
     * @return array|mixed
     * @throws ConfigException
     */
    public function get(string $path)
    {
        if (!$this->config) {
            throw new ConfigException('No config available');
        }

        $config = $this->config;

        foreach (explode('.', $path) as $node) {
            if (!isset($config[$node])) {
                throw new ConfigException('Invalid or unknown config requested');
            }

            $config = $config[$node];
        }

        return $config;
    }
}
