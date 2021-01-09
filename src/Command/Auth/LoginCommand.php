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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
