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

namespace Fusio\Cli\Transport;

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * Http
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://fusio-project.org
 */
class Http implements TransportInterface
{
    public function request(string $baseUri, string $method, string $path, ?array $query = null, ?array $headers = null, $body = null): HttpResponseInterface
    {
        $options = [];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        if ($body instanceof \JsonSerializable) {
            $options['json'] = $body;
        } elseif (is_string($body)) {
            $options['body'] = $body;
        }

        $httpClient = new Client([
            'base_uri' => $baseUri,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'Fusio CLI ' . InstalledVersions::getPrettyVersion('fusio/cli'),
            ],
        ]);

        $response = $httpClient->request($method, $path, $options);

        return new HttpResponse(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody()
        );
    }
}
