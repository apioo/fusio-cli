<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Cli\Command;

use Fusio\Cli\Exception\TransportException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CreateCommandAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
abstract class CreateCommandAbstract extends ClientCommandAbstract
{
    protected function configure(): void
    {
        $type = $this->getType();

        $this
            ->setName($type . ':create')
            ->setDescription('Creates a new ' . $type)
            ->addArgument('payload', InputArgument::REQUIRED, 'A YAML or JSON file containing the payload of the ' . $type);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $this->getType();
        $modelClass = '\\Fusio\\Model\\Backend\\' . ucfirst($type) . 'Create';

        try {
            $response = $this->client->create(
                $type,
                $this->toString($input->getArgument('payload')) ?? '',
                $modelClass
            );
        } catch (TransportException $e) {
            return ErrorRenderer::render($e, $output);
        }

        return ResponseRenderer::render($response, $output);
    }

    abstract protected function getType(): string;
}
