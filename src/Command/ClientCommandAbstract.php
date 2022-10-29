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

namespace Fusio\Cli\Command;

use Fusio\Cli\Service\Client;
use Symfony\Component\Console\Command\Command;

/**
 * HttpCommandAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class ClientCommandAbstract extends Command
{
    protected Client $client;

    public function __construct(Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    protected function toInt($value): ?int
    {
        if (is_array($value)) {
            throw new \InvalidArgumentException('Provided an invalid value');
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function toString($value): ?string
    {
        if (is_array($value)) {
            throw new \InvalidArgumentException('Provided an invalid value');
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
