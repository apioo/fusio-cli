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
use Fusio\Cli\Deploy\EnvReplacerInterface;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Deploy;
use Fusio\Cli\Service\Import\Result;
use PSX\Schema\Parser\TypeSchema\ImportResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DeployCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DeployCommand extends Command
{
    /**
     * @var Deploy
     */
    private $deploy;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var EnvReplacerInterface
     */
    private $envReplacer;

    /**
     * @var ImportResolver
     */
    private $importResolver;

    public function __construct(Deploy $authenticator, string $basePath, EnvReplacerInterface $envReplacer, ImportResolver $importResolver)
    {
        parent::__construct();

        $this->deploy = $authenticator;
        $this->basePath = $basePath;
        $this->envReplacer = $envReplacer;
        $this->importResolver = $importResolver;
    }

    protected function configure()
    {
        $this
            ->setName('deploy:deploy')
            ->setAliases(['deploy'])
            ->setDescription('Deploys a Fusio YAML definition')
            ->addArgument('file', InputArgument::OPTIONAL, 'Optional the definition file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (empty($file) || !is_string($file)) {
            $file = $this->basePath . '/.fusio.yml';
        }

        if (!is_file($file)) {
            throw new \RuntimeException('File does not exists');
        }

        try {
            $results = $this->deploy->deploy(file_get_contents($file), $this->envReplacer, $this->importResolver, dirname($file));
            $count = 0;
            foreach ($results as $result) {
                if ($result->getType() === Result::ACTION_FAILED) {
                    $count++;
                }

                $output->writeln('- [' . $result->getType() . '] ' . $result->getAction() . ' ' . $result->getMessage());
            }

            if ($count > 0) {
                $output->writeln('');
                $output->writeln('Deploy contained ' . $count . ' errors!');
                $output->writeln('');
            } else {
                $output->writeln('');
                $output->writeln('Deploy successful!');
                $output->writeln('');
            }
        } catch (TransportException $e) {
            return ErrorRenderer::render($e, $output);
        }

        return 0;
    }

}
