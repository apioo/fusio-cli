<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Cli\Service;

use Fusio\Cli\Config\ConfigInterface;
use Fusio\Cli\Exception\TokenException;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Transport\Http;
use Fusio\Cli\Transport\ResponseParser;
use Fusio\Cli\Transport\TransportInterface;

/**
 * Authenticator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Authenticator implements AuthenticatorInterface
{
    private TransportInterface $transport;
    private ConfigInterface $config;

    public function __construct(TransportInterface $transport, ConfigInterface $config)
    {
        $this->transport = $transport;
        $this->config = $config;
    }

    /**
     * @throws TokenException
     * @throws TransportException
     */
    public function requestAccessToken(string $baseUri, string $username, string $password): string
    {
        $response = $this->transport->request(
            $baseUri,
            'POST',
            'authorization/token',
            null,
            [
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'grant_type=client_credentials'
        );

        $data = ResponseParser::parse($response);
        if (!isset($data->access_token)) {
            throw new TransportException($response, 'Could not find access token in body');
        }

        $data->base_uri = $baseUri;

        if (isset($data->expires_in) && $data->expires_in < 529196400) {
            // in case the expires in is lower than 1986-10-09 we assume that the field represents the duration in seconds
            // otherwise it is probably a timestamp
            $data->expires_in = time() + $data->expires_in;
        }

        $tokenFile = $this->getTokenFile();
        $bytes = file_put_contents($tokenFile, \json_encode($data));
        if (empty($bytes)) {
            throw new TokenException('Could not write token to file ' . $tokenFile);
        }

        return $data->access_token;
    }

    public function isRemote(): bool
    {
        return $this->transport instanceof Http;
    }

    /**
     * @throws TokenException
     */
    public function getBaseUri(): string
    {
        return $this->getTokenValue('base_uri');
    }

    /**
     * @throws TokenException
     */
    public function getAccessToken(): string
    {
        if ($this->isExpired()) {
            $this->removeTokenFile();

            throw new TokenException('Existing token is expired, please request a new token through the login command');
        }

        return $this->getTokenValue('access_token');
    }

    public function hasAccessToken(): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            return !empty($accessToken);
        } catch (TokenException $e) {
            return false;
        }
    }

    /**
     * @throws TokenException
     */
    public function removeAccessToken(): void
    {
        $tokenFile = $this->getTokenFile();
        if (!is_file($tokenFile)) {
            return;
        }

        // send revoke
        if ($this->hasAccessToken()) {
            $this->transport->request(
                $this->getBaseUri(),
                'POST',
                'authorization/revoke',
                null,
                ['Authorization' => 'Bearer ' . $this->getAccessToken()]
            );
        }

        // remove file
        $this->removeTokenFile();
    }

    /**
     * @throws TokenException
     * @throws TransportException
     * @throws \JsonException
     */
    public function whoami(): object
    {
        $response = $this->transport->request(
            $this->getBaseUri(),
            'GET',
            'authorization/whoami',
            null,
            ['Authorization' => 'Bearer ' . $this->getAccessToken()]
        );

        return ResponseParser::parse($response);
    }

    /**
     * @throws TokenException
     */
    public function getTokenValue(string $key): string
    {
        $tokenFile = $this->getTokenFile();
        if (!is_file($tokenFile)) {
            throw new TokenException('Found no existing token, please request a token through the login command');
        }

        $data = \json_decode(file_get_contents($tokenFile), true);
        if (!isset($data[$key])) {
            throw new TokenException('Could not find ' . $key . ' in token');
        }

        return $data[$key];
    }

    /**
     * @throws TokenException
     */
    private function isExpired(): bool
    {
        return time() > $this->getTokenValue('expires_in');
    }

    private function getTokenFile(): string
    {
        return $this->getHomeDir() . '/fusio_token.json';
    }

    private function removeTokenFile(): void
    {
        $tokenFile = $this->getTokenFile();
        if (!is_file($tokenFile)) {
            return;
        }

        unlink($tokenFile);
    }

    private function getHomeDir(): string
    {
        $baseDir = $this->config->getBaseDir();
        if (!empty($baseDir)) {
            return $baseDir;
        }

        $homeDir = getenv('HOME');
        if (!empty($homeDir) && is_dir($homeDir)) {
            return $homeDir;
        }

        return sys_get_temp_dir();
    }
}
