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

use Fusio\Cli\Exception\TransportException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ErrorRenderer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ErrorRenderer
{
    public static function render(TransportException $exception, OutputInterface $output): int
    {
        $response = $exception->getResponse();

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $output->writeln('');

        if (isset($data['success'])) {
            $output->writeln($data['message']);
            $output->writeln('');
            $output->writeln($data['trace'] ?? '');
        } else {
            $output->writeln('The server returned a non successful status code: ' . $response->getStatusCode());
            $output->writeln('');
            $output->writeln($body);
        }

        $output->writeln('');

        return 1;
    }
}