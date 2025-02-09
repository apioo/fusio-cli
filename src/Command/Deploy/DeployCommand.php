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
use Fusio\Cli\Config\ConfigInterface;
use Fusio\Cli\Deploy\EnvReplacerInterface;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Deploy;
use Fusio\Cli\Service\Import\Result;
use PSX\Http\Environment\HttpResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DeployCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class DeployCommand extends Command
{
    private Deploy $deploy;
    private ConfigInterface $config;
    private EnvReplacerInterface $envReplacer;

    public function __construct(Deploy $deploy, ConfigInterface $config, EnvReplacerInterface $envReplacer)
    {
        parent::__construct();

        $this->deploy = $deploy;
        $this->config = $config;
        $this->envReplacer = $envReplacer;
    }

    protected function configure(): void
    {
        $this
            ->setName('deploy:deploy')
            ->setAliases(['deploy'])
            ->setDescription('Deploys a Fusio YAML definition')
            ->addArgument('file', InputArgument::OPTIONAL, 'Optional the definition file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        if (empty($file) || !is_string($file)) {
            $file = $this->config->getBaseDir() . '/.fusio.yml';
        }

        if (!is_file($file)) {
            throw new \RuntimeException('File does not exists');
        }

        try {
            $results = $this->deploy->deploy(file_get_contents($file), $this->envReplacer, dirname($file));
            $count = 0;
            foreach ($results as $result) {
                if ($result->getType() === Result::ACTION_FAILED) {
                    $count++;
                }

                $output->writeln('- ' . $result->toString());

                $response = $result->getResponse();
                if ($response instanceof HttpResponseInterface && $output->isVerbose()) {
                    $output->writeln((string) $response->getBody());
                    $output->writeln('');
                }
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

        return self::SUCCESS;
    }
}
