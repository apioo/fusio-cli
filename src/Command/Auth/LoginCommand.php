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

namespace Fusio\Cli\Command\Auth;

use Fusio\Cli\Command\ErrorRenderer;
use Fusio\Cli\Exception\TransportException;
use Fusio\Cli\Service\Authenticator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * LoginCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://fusio-project.org
 */
class LoginCommand extends Command
{
    /**
     * @var Authenticator
     */
    private $authenticator;

    public function __construct(Authenticator $authenticator)
    {
        parent::__construct();

        $this->authenticator = $authenticator;
    }

    protected function configure()
    {
        $this
            ->setName('auth:login')
            ->setAliases(['login'])
            ->setDescription('Login at the Fusio instance')
            ->addOption('url', 'r', InputOption::VALUE_OPTIONAL, 'The Fusio URL')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'The username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'The password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        // base uri
        if ($this->authenticator->isRemote()) {
            $baseUri = $input->getOption('url');
            if (empty($baseUri) || !is_string($baseUri)) {
                $question = new Question('Enter the URL: ');
                $baseUri = (string) $helper->ask($input, $output, $question);
            }
        } else {
            $baseUri = '';
        }

        // username
        $username = $input->getOption('username');
        if (empty($username) || !is_string($username)) {
            $question = new Question('Enter the username: ');
            $username = (string) $helper->ask($input, $output, $question);
        }

        // password
        $password = $input->getOption('password');
        if (empty($password) || !is_string($password)) {
            $question = new Question('Enter the password: ');
            $question->setHidden(true);
            $password = (string) $helper->ask($input, $output, $question);
        }

        try {
            $this->authenticator->requestAccessToken($baseUri, $username, $password);
        } catch (TransportException $e) {
            return ErrorRenderer::render($e, $output);
        }

        $output->writeln('');
        $output->writeln('Login successful');
        $output->writeln('');

        return 0;
    }
}
