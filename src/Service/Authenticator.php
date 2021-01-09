<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Cli\Service;

use Fusio\Cli\Exception\TokenException;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Transport\Http;
use Fusio\Cli\Transport\ResponseParser;
use Fusio\Cli\Transport\TransportInterface;

/**
 * Authenticator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Authenticator
{
    /**
     * @var TransportInterface
     */
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param string $baseUri
     * @param string $username
     * @param string $password
     * @return string
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
        if (!isset($data['access_token'])) {
            throw new TransportException($response, 'Could not find access token in body');
        }

        $data['base_uri'] = $baseUri;

        $tokenFile = $this->getTokenFile();
        $bytes = file_put_contents($tokenFile, \json_encode($data));
        if (empty($bytes)) {
            throw new TokenException('Could not write token to file ' . $tokenFile);
        }

        return $data['access_token'];
    }

    public function isRemote(): bool
    {
        return $this->transport instanceof Http;
    }

    /**
     * @return string
     * @throws TokenException
     */
    public function getBaseUri(): string
    {
        return $this->getTokenValue('base_uri');
    }

    /**
     * @return string
     * @throws TokenException
     */
    public function getAccessToken(): string
    {
        return $this->getTokenValue('access_token');
    }

    /**
     * @return bool
     */
    public function hasAccessToken(): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            return !empty($accessToken);
        } catch (TokenException $e) {
            return false;
        }
    }

    public function removeAccessToken(): void
    {
        $tokenFile = $this->getTokenFile();
        if (!is_file($tokenFile)) {
            return;
        }

        // send revoke
        $this->transport->request(
            $this->getBaseUri(),
            'POST',
            'authorization/revoke',
            null,
            ['Authorization' => 'Bearer ' . $this->getAccessToken()]
        );

        // remove file
        unlink($tokenFile);
    }

    /**
     * @return array
     * @throws TokenException
     * @throws TransportException
     */
    public function whoami(): array
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
     * @param string $key
     * @return string
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

    private function getTokenFile(): string
    {
        $homeDir = getenv('HOME');
        if (!is_string($homeDir) || !is_dir($homeDir)) {
            $homeDir = sys_get_temp_dir();
        }

        return $homeDir . '/fusio_token.json';
    }
}
