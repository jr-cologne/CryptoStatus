<?php

/**
 * A simple Twitter bot application which posts hourly status updates for the top 10 cryptocurrencies.
 *
 * PHP version >= 7.4
 *
 * LICENSE: MIT, see LICENSE file for more information
 *
 * @author JR Cologne <kontakt@jr-cologne.de>
 * @copyright 2021 JR Cologne
 * @license https://github.com/jr-cologne/CryptoStatus/blob/master/LICENSE MIT
 * @version v0.9.1-beta
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
     * Check if the current environment is the production environment
     *
     * @return bool
     */
    public function isProduction() : bool
    {
        return !empty(getenv('GOOGLE_CLOUD_PROJECT'));
    }

    /**
     * Load environment variables from .env file
     *
     * @param string $dotenv_file
     * @throws EnvException
     */
    public function loadEnvVarsFromDotenvFile(string $dotenv_file)
    {
        if (!file_exists($dotenv_file)) {
            throw new EnvException("The file ({$dotenv_file}) to load the environment variables from does not exist");
        }

        $env_vars = file($dotenv_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$env_vars) {
            throw new EnvException("Failed to read environment variables file ({$dotenv_file})");
        }

        $this->putEnvVars($env_vars);
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

    /**
     * Set an array of environment variables
     *
     * @param array $env_vars
     * @throws EnvException
     */
    protected function putEnvVars(array $env_vars) : void
    {
        foreach ($env_vars as $env_var) {
            $setting = $env_var;

            if (is_array($env_var)) {
                $setting = $env_var['name'] . '=' . $env_var['value'];
            }

            if (!$this->put($setting)) {
                throw new EnvException('Failed to load environment variables');
            }
        }
    }
}
