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
 * Env.php
 *
 * The Env class for dealing with environment variables of the application.
 *
 */

namespace CryptoStatus;

use CryptoStatus\Exceptions\EnvException;

class Env
{

    /**
     * Load environment variables from .env file
     *
     * @param string $dotenv_file
     * @throws EnvException
     */
    public function loadEnvVars(string $dotenv_file = '.env')
    {
        if (!file_exists($dotenv_file)) {
            throw new EnvException("The file ({$dotenv_file}) to load the environment variables from does not exist");
        }

        $dotenv = file($dotenv_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$dotenv) {
            throw new EnvException("Failed to read environment variables file ({$dotenv_file})");
        }

        foreach ($dotenv as $setting) {
            if (!self::put($setting)) {
                throw new EnvException('Failed to load environment variables');
            }
        }
    }

    /**
     * Set an environment variable
     *
     * @param string $setting
     * @return bool
     */
    public function put(string $setting) : bool
    {
        return putenv($setting);
    }

    /**
     * Get an environment variable
     *
     * @param string $varname
     * @return array|false|string
     * @throws EnvException
     */
    public function get(string $varname)
    {
        $env = getenv($varname);

        if (!$env) {
            throw new EnvException('Invalid or unknown environment variable');
        }
        
        return $env;
    }
}
