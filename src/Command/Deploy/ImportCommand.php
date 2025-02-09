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

namespace Fusio\Cli\Command\Deploy;

use Fusio\Cli\Command\ErrorRenderer;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Import;
use Fusio\Cli\Service\Import\Result;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ImportCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class ImportCommand extends Command
{
    private Import $import;

    public function __construct(Import $import)
    {
        parent::__construct();

        $this->import = $import;
    }

    protected function configure(): void
    {
        $this
            ->setName('deploy:import')
            ->setAliases(['import'])
            ->setDescription('Imports the complete Fusio configuration')
            ->addArgument('file', InputArgument::REQUIRED, 'Exports a Fusio configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        if (!is_string($file) || !is_file($file)) {
            throw new \RuntimeException('Provided file does not exist');
        }

        try {
            $results = $this->import->import(file_get_contents($file));
            $count = 0;
            foreach ($results as $result) {
                if ($result->getType() === Result::ACTION_FAILED) {
                    $count++;
                }

                $output->writeln('- ' . $result->toString());
            }

            if ($count > 0) {
                $output->writeln('');
                $output->writeln('Import contained ' . $count . ' errors!');
                $output->writeln('');
            } else {
                $output->writeln('');
                $output->writeln('Import successful!');
                $output->writeln('');
            }
        } catch (TransportException $e) {
            return ErrorRenderer::render($e, $output);
        }

        return self::SUCCESS;
    }
}
