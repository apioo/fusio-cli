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

namespace Fusio\Cli\Command\Auth;

use Fusio\Cli\Service\Authenticator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * LogoutCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class LogoutCommand extends Command
{
    private Authenticator $authenticator;

    public function __construct(Authenticator $authenticator)
    {
        parent::__construct();

        $this->authenticator = $authenticator;
    }

    protected function configure(): void
    {
        $this
            ->setName('auth:logout')
            ->setAliases(['logout'])
            ->setDescription('Logout from the remote instance');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->authenticator->removeAccessToken();

        $output->writeln('');
        $output->writeln('Logout successful');
        $output->writeln('');

        return self::SUCCESS;
    }
}
