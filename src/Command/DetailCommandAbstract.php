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
 * DetailCommandAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class DetailCommandAbstract extends ClientCommandAbstract
{
    protected function configure()
    {
        $type = $this->getType();

        $this
            ->setName($type . ':detail')
            ->setDescription('Shows details of a specific ' . $type)
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the existing ' . $type);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $this->getType();

        try {
            $rawId = $input->getArgument('id');
            $actualId = (int) $rawId;

            if ($actualId === 0) {
                $response = $this->client->getByName($type, $rawId);
            } else {
                $response = $this->client->get($type, $actualId);
            }
        } catch (TransportException $e) {
            return ErrorRenderer::render($e, $output);
        }

        $output->writeln('');
        $output->writeln(Yaml::dump($response));
        $output->writeln('');

        return 0;
    }

    abstract protected function getType(): string;
}
