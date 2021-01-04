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
use Fusio\Cli\Service\Export;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ExportCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
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
