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

namespace Fusio\Cli;

use Fusio\Cli\Deploy\EnvReplacerInterface;
use Fusio\Cli\Transport\TransportInterface;
use Fusio\Cli\Service;
use PSX\Schema\Parser\TypeSchema\ImportResolver;
use Symfony\Component\Console\Application;

/**
 * Setup
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Setup
{
    /**
     * Adds all CLI commands to an application. The transport interface defines whether we send actual HTTP requests or
     * only internal requests. The base path contains the path to the Fusio app. 
     * 
     * @param Application $application
     * @param TransportInterface $transport
     * @param string $basePath
     * @param EnvReplacerInterface $envReplacer
     * @param ImportResolver $importResolver
     */
    public static function appendCommands(Application $application, TransportInterface $transport, string $basePath, EnvReplacerInterface $envReplacer, ImportResolver $importResolver)
    {
        $authenticator = new Service\Authenticator($transport);
        $client = new Service\Client($authenticator, $transport);
        $import = new Service\Import($client);
        $export = new Service\Export($client);
        $deploy = new Service\Deploy($import);

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

        $application->add(new Command\Authentication\LoginCommand($authenticator));
        $application->add(new Command\Authentication\LogoutCommand($authenticator));
        $application->add(new Command\Authentication\WhoamiCommand($authenticator));

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

        $application->add(new Command\Deploy\DeployCommand($deploy, $basePath, $envReplacer, $importResolver));
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

        $application->add(new Command\Route\ListCommand($client));
        $application->add(new Command\Route\DetailCommand($client));
        $application->add(new Command\Route\CreateCommand($client));
        $application->add(new Command\Route\UpdateCommand($client));
        $application->add(new Command\Route\DeleteCommand($client));

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
