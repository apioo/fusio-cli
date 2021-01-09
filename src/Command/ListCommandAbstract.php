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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * ListCommandAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
