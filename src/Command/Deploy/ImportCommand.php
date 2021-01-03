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

namespace Fusio\Cli\Command\Deploy;

use Fusio\Cli\Command\ErrorRenderer;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Import;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ImportCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ImportCommand extends Command
{
    /**
     * @var Import
     */
    private $import;

    public function __construct(Import $import)
    {
        parent::__construct();

        $this->import = $import;
    }

    protected function configure()
    {
        $this
            ->setName('import')
            ->setDescription('Imports the complete Fusio configuration')
            ->addArgument('file', InputArgument::OPTIONAL, 'Optional the definition file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (!is_file($file)) {
            throw new \RuntimeException('Provided file does not exist');
        }

        try {
            $result = $this->import->import(file_get_contents($file));
        } catch (TransportException $e) {
            return ErrorRenderer::render($e, $output);
        }

        $logs = $result->getLogs();
        foreach ($logs as $log) {
            $output->writeln('- ' . $log);
        }

        if ($result->hasError()) {
            $errors = $result->getErrors();

            $output->writeln('');
            $output->writeln('Import contained ' . count($errors) . ' errors!');
            $output->writeln('');

            foreach ($errors as $error) {
                $output->writeln('- ' . $error);
            }
        } else {
            $output->writeln('');
            $output->writeln('Import successful!');
            $output->writeln('');
        }

        return 0;
    }
}
