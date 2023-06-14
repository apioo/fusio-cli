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

namespace Fusio\Cli\Command;

use Fusio\Cli\Exception\TransportException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ErrorRenderer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

        return Command::FAILURE;
    }
}