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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * ListCommandAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://fusio-project.org
 */
abstract class ListCommandAbstract extends ClientCommandAbstract
{
    protected function configure()
    {
        $type = $this->getType();

        $this
            ->setName($type . ':list')
            ->setDescription('List all available entries of type ' . $type)
            ->addArgument('search', InputArgument::OPTIONAL, 'A search string')
            ->addArgument('startIndex', InputArgument::OPTIONAL, 'The start index of the result set')
            ->addArgument('count', InputArgument::OPTIONAL, 'The count of the result set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $this->getType();

        try {
            $response = $this->client->getAll(
                $type,
                $this->toInt($input->getArgument('startIndex')),
                $this->toInt($input->getArgument('count')),
                $this->toString($input->getArgument('search'))
            );
        } catch (TransportException $e) {
            return ErrorRenderer::render($e, $output);
        }

        $output->writeln('');
        $output->writeln(Yaml::dump($response, 4));
        $output->writeln('');

        return 0;
    }

    abstract protected function getType(): string;
}
