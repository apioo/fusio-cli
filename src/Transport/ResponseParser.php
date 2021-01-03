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

use Fusio\Cli\Exception\TransportException;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * ResponseParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ResponseParser
{
    public static function parse(HttpResponseInterface $response): array
    {
        if ($response->getStatusCode() >= 400) {
            throw new TransportException($response, 'API returned an invalid status code');
        }

        $data = \json_decode((string) $response->getBody(), true);
        if (!is_array($data)) {
            throw new TransportException($response, 'API returned an invalid response body');
        }

        return $data;
    }
}
