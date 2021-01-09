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

namespace Fusio\Cli\Transport;

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * Http
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Http implements TransportInterface
{
    /**
     * @inheritDoc
     */
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
