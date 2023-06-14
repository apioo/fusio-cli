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

namespace Fusio\Cli\Command\Deploy;

use Fusio\Cli\Command\ErrorRenderer;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Export;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ExportCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class ExportCommand extends Command
{
    /**
     * @var Export
     */
    private $export;

    public function __construct(Export $import)
    {
        parent::__construct();

        $this->export = $import;
    }

    protected function configure()
    {
        $this
            ->setName('deploy:export')
            ->setAliases(['export'])
            ->setDescription('Exports the complete Fusio configuration to stdout');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $export = $this->export->export();
        } catch (TransportException $e) {
            return ErrorRenderer::render($e, $output);
        }

        $output->writeln('');
        $output->writeln($export);
        $output->writeln('');

        return 0;
    }
}
