<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Cli\Exception\TransportException;
use JsonException;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Json\Parser;

/**
 * ResponseParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class ResponseParser
{
    /**
     * @throws TransportException
     * @throws JsonException
     */
    public static function parse(HttpResponseInterface $response): object
    {
        if ($response->getStatusCode() >= 400) {
            throw new TransportException($response, 'API returned a non successful status code ' . $response->getStatusCode());
        }

        $data = Parser::decode((string) $response->getBody());
        if (!$data instanceof \stdClass) {
            throw new TransportException($response, 'API returned an invalid response body');
        }

        return $data;
    }
}
