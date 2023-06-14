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

namespace Fusio\Cli;

use Fusio\Cli\Deploy\EnvReplacerInterface;
use Fusio\Cli\Service;
use Fusio\Cli\Transport\TransportInterface;
use PSX\Schema\SchemaManagerInterface;
use Symfony\Component\Console\Application;

/**
 * Setup
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://fusio-project.org
 */
class Setup
{
    /**
     * Adds all CLI commands to an application. The transport interface defines whether we send actual HTTP requests or
     * only internal requests. The base path contains the path to the Fusio app. 
     */
    public static function appendCommands(Application $application, TransportInterface $transport, string $basePath, EnvReplacerInterface $envReplacer, SchemaManagerInterface $schemaManager): void
    {
        $authenticator = new Service\Authenticator($transport, $basePath);
        $client = new Service\Client($authenticator, $transport);
        $import = new Service\Import($client);
        $export = new Service\Export($client);
        $deploy = new Service\Deploy($import, $schemaManager);

        $application->add(new Command\Action\ListCommand($client));
        $application->add(new Command\Action\DetailCommand($client));
        $application->add(new Command\Action\CreateCommand($client));
        $application->add(new Command\Action\UpdateCommand($client));
        $application->add(new Command\Action\DeleteCommand($client));

        $application->add(new Command\App\ListCommand($client));
        $application->add(new Command\App\DetailCommand($client));
        $application->add(new Command\App\CreateCommand($client));
        $application->add(new Command\App\UpdateCommand($client));
        $application->add(new Command\App\DeleteCommand($client));

        $application->add(new Command\Auth\LoginCommand($authenticator));
        $application->add(new Command\Auth\LogoutCommand($authenticator));
        $application->add(new Command\Auth\WhoamiCommand($authenticator));

        $application->add(new Command\Category\ListCommand($client));
        $application->add(new Command\Category\DetailCommand($client));
        $application->add(new Command\Category\CreateCommand($client));
        $application->add(new Command\Category\UpdateCommand($client));
        $application->add(new Command\Category\DeleteCommand($client));

        $application->add(new Command\Connection\ListCommand($client));
        $application->add(new Command\Connection\DetailCommand($client));
        $application->add(new Command\Connection\CreateCommand($client));
        $application->add(new Command\Connection\UpdateCommand($client));
        $application->add(new Command\Connection\DeleteCommand($client));

        $application->add(new Command\Cronjob\ListCommand($client));
        $application->add(new Command\Cronjob\DetailCommand($client));
        $application->add(new Command\Cronjob\CreateCommand($client));
        $application->add(new Command\Cronjob\UpdateCommand($client));
        $application->add(new Command\Cronjob\DeleteCommand($client));

        $application->add(new Command\Deploy\DeployCommand($deploy, $basePath, $envReplacer));
        $application->add(new Command\Deploy\ExportCommand($export));
        $application->add(new Command\Deploy\ImportCommand($import));

        $application->add(new Command\Event\ListCommand($client));
        $application->add(new Command\Event\DetailCommand($client));
        $application->add(new Command\Event\CreateCommand($client));
        $application->add(new Command\Event\UpdateCommand($client));
        $application->add(new Command\Event\DeleteCommand($client));

        $application->add(new Command\Log\ListCommand($client));
        $application->add(new Command\Log\DetailCommand($client));

        $application->add(new Command\Plan\ListCommand($client));
        $application->add(new Command\Plan\DetailCommand($client));
        $application->add(new Command\Plan\CreateCommand($client));
        $application->add(new Command\Plan\UpdateCommand($client));
        $application->add(new Command\Plan\DeleteCommand($client));

        $application->add(new Command\Rate\ListCommand($client));
        $application->add(new Command\Rate\DetailCommand($client));
        $application->add(new Command\Rate\CreateCommand($client));
        $application->add(new Command\Rate\UpdateCommand($client));
        $application->add(new Command\Rate\DeleteCommand($client));

        $application->add(new Command\Role\ListCommand($client));
        $application->add(new Command\Role\DetailCommand($client));
        $application->add(new Command\Role\CreateCommand($client));
        $application->add(new Command\Role\UpdateCommand($client));
        $application->add(new Command\Role\DeleteCommand($client));

        $application->add(new Command\Operation\ListCommand($client));
        $application->add(new Command\Operation\DetailCommand($client));
        $application->add(new Command\Operation\CreateCommand($client));
        $application->add(new Command\Operation\UpdateCommand($client));
        $application->add(new Command\Operation\DeleteCommand($client));

        $application->add(new Command\Schema\ListCommand($client));
        $application->add(new Command\Schema\DetailCommand($client));
        $application->add(new Command\Schema\CreateCommand($client));
        $application->add(new Command\Schema\UpdateCommand($client));
        $application->add(new Command\Schema\DeleteCommand($client));

        $application->add(new Command\Scope\ListCommand($client));
        $application->add(new Command\Scope\DetailCommand($client));
        $application->add(new Command\Scope\CreateCommand($client));
        $application->add(new Command\Scope\UpdateCommand($client));
        $application->add(new Command\Scope\DeleteCommand($client));

        $application->add(new Command\User\ListCommand($client));
        $application->add(new Command\User\DetailCommand($client));
        $application->add(new Command\User\CreateCommand($client));
        $application->add(new Command\User\UpdateCommand($client));
        $application->add(new Command\User\DeleteCommand($client));
    }
}
