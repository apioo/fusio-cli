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

namespace Fusio\Cli\Tests\Service;

use Fusio\Cli\Deploy\EnvReplacer;
use Fusio\Model\Backend;
use Fusio\Cli\Service\Authenticator;
use Fusio\Cli\Service\Client;
use Fusio\Cli\Service\Deploy;
use Fusio\Cli\Service\Import;
use Fusio\Cli\Transport\Memory;
use PHPUnit\Framework\TestCase;
use PSX\Http\Environment\HttpResponse;
use PSX\Schema\Parser\TypeSchema\ImportResolver;

/**
 * DeployTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DeployTest extends TestCase
{
    public function testDeployCreate()
    {
        $transport = $this->newTransportCreate();

        $authenticator = new Authenticator($transport);
        $authenticator->requestAccessToken('https://api.acme.com', 'foo', 'bar');

        $client = new Client(new Authenticator($transport), $transport);
        $deploy = new Deploy(new Import($client));

        $file = __DIR__ . '/resource/.fusio.yml';
        $yaml = file_get_contents($file);
        $envReplacer = new EnvReplacer();
        $importResolver = ImportResolver::createDefault();

        $results = iterator_to_array($deploy->deploy($yaml, $envReplacer, $importResolver, dirname($file)), false);
        $results = array_map('strval', $results);

        $expect = [
            '[CREATED] action Test-Action',
            '[UPDATED] config mail_register_subject',
            '[UPDATED] config app_approval',
            '[CREATED] connection New-Connection',
            '[CREATED] cronjob Cronjob-A',
            '[CREATED] event New-Event',
            '[CREATED] plan Plan-A',
            '[CREATED] rate New-Rate',
            '[CREATED] schema Parameters',
            '[CREATED] schema Request-Schema',
            '[CREATED] schema Response-Schema',
            '[CREATED] schema Error-Schema',
            '[CREATED] scope Scope-A',
            '[CREATED] scope Scope-B',
            '[CREATED] routes /bar',
        ];
        $this->assertEquals($expect, $results);

        $expect = [
            ['https://api.acme.com', 'POST', 'authorization/token', null, ['Authorization' => 'Basic Zm9vOmJhcg==', 'Content-Type' => 'application/x-www-form-urlencoded'], 'grant_type=client_credentials'],
        ];

        $this->assertEquals(31, count($transport->getRequests()));
        $this->assertEquals($expect[0], $transport->getRequests()[0]);

        $authenticator->removeAccessToken();
    }

    private function newTransportCreate(): Memory
    {
        $action = new Backend\Action();
        $action->setId(1);

        $config = new Backend\Config();
        $config->setId(1);

        $connection = new Backend\Connection();
        $connection->setId(1);

        $cronjob = new Backend\Cronjob();
        $cronjob->setId(1);

        $event = new Backend\Event();
        $event->setId(1);

        $plan = new Backend\Plan();
        $plan->setId(1);

        $rate = new Backend\Rate();
        $rate->setId(1);

        $role = new Backend\Role();
        $role->setId(1);

        $route = new Backend\Route();
        $route->setId(1);

        $schema = new Backend\Schema();
        $schema->setId(1);

        $scope = new Backend\Scope();
        $scope->setId(1);

        $transport = new Memory();
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['access_token' => '2YotnFZFEjr1zCsicMWpAA'])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($config)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($config)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(404, [], \json_encode(['not_found' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));

        return $transport;
    }

    public function testDeployUpdate()
    {
        $transport = $this->newTransportUpdate();

        $authenticator = new Authenticator($transport);
        $authenticator->requestAccessToken('https://api.acme.com', 'foo', 'bar');

        $client = new Client(new Authenticator($transport), $transport);
        $deploy = new Deploy(new Import($client));

        $file = __DIR__ . '/resource/.fusio.yml';
        $yaml = file_get_contents($file);
        $envReplacer = new EnvReplacer();
        $importResolver = ImportResolver::createDefault();

        $results = iterator_to_array($deploy->deploy($yaml, $envReplacer, $importResolver, dirname($file)), false);
        $results = array_map('strval', $results);

        $expect = [
            '[UPDATED] action Test-Action',
            '[UPDATED] config mail_register_subject',
            '[UPDATED] config app_approval',
            '[UPDATED] connection New-Connection',
            '[UPDATED] cronjob Cronjob-A',
            '[UPDATED] event New-Event',
            '[UPDATED] plan Plan-A',
            '[UPDATED] rate New-Rate',
            '[UPDATED] schema Parameters',
            '[UPDATED] schema Request-Schema',
            '[UPDATED] schema Response-Schema',
            '[UPDATED] schema Error-Schema',
            '[UPDATED] scope Scope-A',
            '[UPDATED] scope Scope-B',
            '[UPDATED] routes /bar',
        ];
        $this->assertEquals($expect, $results);

        $expect = [
            ['https://api.acme.com', 'POST', 'authorization/token', null, ['Authorization' => 'Basic Zm9vOmJhcg==', 'Content-Type' => 'application/x-www-form-urlencoded'], 'grant_type=client_credentials'],
        ];

        $this->assertEquals(31, count($transport->getRequests()));
        $this->assertEquals($expect[0], $transport->getRequests()[0]);

        $authenticator->removeAccessToken();
    }

    private function newTransportUpdate(): Memory
    {
        $action = new Backend\Action();
        $action->setId(1);

        $config = new Backend\Config();
        $config->setId(1);

        $connection = new Backend\Connection();
        $connection->setId(1);

        $cronjob = new Backend\Cronjob();
        $cronjob->setId(1);

        $event = new Backend\Event();
        $event->setId(1);

        $plan = new Backend\Plan();
        $plan->setId(1);

        $rate = new Backend\Rate();
        $rate->setId(1);

        $role = new Backend\Role();
        $role->setId(1);

        $route = new Backend\Route();
        $route->setId(1);

        $schema = new Backend\Schema();
        $schema->setId(1);

        $scope = new Backend\Scope();
        $scope->setId(1);

        $transport = new Memory();
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['access_token' => '2YotnFZFEjr1zCsicMWpAA'])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($action)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($config)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($config)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($connection)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($cronjob)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($event)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($plan)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($rate)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($role)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($route)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($schema)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($schema)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($schema)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($schema)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($scope)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode($scope)));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));

        return $transport;
    }
}