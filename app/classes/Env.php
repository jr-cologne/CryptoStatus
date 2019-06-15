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
 * @version v0.6.7
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
use Google\Cloud\Datastore\DatastoreClient;

class Env
{

    /**
     * Load environment variables from gcloud datastore
     *
     * @param DatastoreClient $datastore
     * @throws EnvException
     */
    public function loadEnvVarsFromDatastore(DatastoreClient $datastore)
    {
        $data = $datastore->runQuery(
            $datastore->query()
                ->kind('env-vars')
        );

        $env_vars = [];

        foreach ($data as $item) {
            $env_vars[] = [
                'name' => $item->name,
                'value' => $item->value
            ];
        }

        if (!$env_vars) {
            throw new EnvException("Failed to read environment variables from datastore");
        }

        foreach ($env_vars as $env_var) {
            $setting = $env_var['name'] . '=' . $env_var['value'];

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
