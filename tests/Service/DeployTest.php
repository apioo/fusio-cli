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

namespace Fusio\Cli\Tests\Service;

use Fusio\Cli\Deploy\EnvReplacer;
use Fusio\Cli\Service\Authenticator;
use Fusio\Cli\Service\Client;
use Fusio\Cli\Service\Deploy;
use Fusio\Cli\Service\Import;
use Fusio\Cli\Transport\Memory;
use Fusio\Model\Backend;
use PHPUnit\Framework\TestCase;
use PSX\Http\Environment\HttpResponse;
use PSX\Schema\SchemaManager;

/**
 * DeployTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class DeployTest extends TestCase
{
    public function testDeployCreate()
    {
        $transport = $this->newTransportCreate();

        $authenticator = new Authenticator($transport);
        $authenticator->requestAccessToken('https://api.acme.com', 'foo', 'bar');

        $client = new Client(new Authenticator($transport), $transport);
        $deploy = new Deploy(new Import($client), new SchemaManager());

        $file = __DIR__ . '/resource/.fusio.yml';
        $yaml = file_get_contents($file);
        $envReplacer = new EnvReplacer();

        $results = iterator_to_array($deploy->deploy($yaml, $envReplacer, dirname($file)), false);
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
            '[CREATED] role Dev-Role',
            '[CREATED] schema Parameters',
            '[CREATED] schema Request-Schema',
            '[CREATED] schema Response-Schema',
            '[CREATED] schema Error-Schema',
            '[CREATED] scope Scope-A',
            '[CREATED] scope Scope-B',
            '[CREATED] operation bar',
        ];
        $this->assertEquals($expect, $results);

        $expect = [
            ['https://api.acme.com', 'POST', 'authorization/token', null, ['Authorization' => 'Basic Zm9vOmJhcg==', 'Content-Type' => 'application/x-www-form-urlencoded'], 'grant_type=client_credentials'],
        ];

        $this->assertEquals(33, count($transport->getRequests()));
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

        $operation = new Backend\Operation();
        $operation->setId(1);

        $schema = new Backend\Schema();
        $schema->setId(1);

        $scope = new Backend\Scope();
        $scope->setId(1);

        $transport = new Memory();
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['access_token' => '2YotnFZFEjr1zCsicMWpAA', 'expires_in' => time() + 60])));
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
        $deploy = new Deploy(new Import($client), new SchemaManager());

        $file = __DIR__ . '/resource/.fusio.yml';
        $yaml = file_get_contents($file);
        $envReplacer = new EnvReplacer();

        $results = iterator_to_array($deploy->deploy($yaml, $envReplacer, dirname($file)), false);
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
            '[UPDATED] role Dev-Role',
            '[UPDATED] schema Parameters',
            '[UPDATED] schema Request-Schema',
            '[UPDATED] schema Response-Schema',
            '[UPDATED] schema Error-Schema',
            '[UPDATED] scope Scope-A',
            '[UPDATED] scope Scope-B',
            '[UPDATED] operation bar',
        ];
        $this->assertEquals($expect, $results);

        $expect = [
            ['https://api.acme.com', 'POST', 'authorization/token', null, ['Authorization' => 'Basic Zm9vOmJhcg==', 'Content-Type' => 'application/x-www-form-urlencoded'], 'grant_type=client_credentials'],
        ];

        $this->assertEquals(33, count($transport->getRequests()));
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

        $route = new Backend\Operation();
        $route->setId(1);

        $schema = new Backend\Schema();
        $schema->setId(1);

        $scope = new Backend\Scope();
        $scope->setId(1);

        $transport = new Memory();
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['access_token' => '2YotnFZFEjr1zCsicMWpAA', 'expires_in' => time() + 60])));
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