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
    public function testDeploy()
    {
        $transport = $this->newTransport();

        $authenticator = new Authenticator($transport);
        $authenticator->requestAccessToken('https://api.acme.com', 'foo', 'bar');

        $client = new Client(new Authenticator($transport), $transport);
        $deploy = new Deploy(new Import($client));

        $file = __DIR__ . '/resource/.fusio.yml';
        $yaml = file_get_contents($file);
        $envReplacer = new EnvReplacer();
        $importResolver = ImportResolver::createDefault();

        $result = $deploy->deploy($yaml, $envReplacer, $importResolver, dirname($file));

        $expect = [
            '[UPDATED] event New-Event',
            '[UPDATED] connection New-Connection',
            '[UPDATED] schema Parameters',
            '[UPDATED] schema Request-Schema',
            '[UPDATED] schema Response-Schema',
            '[UPDATED] schema Error-Schema',
            '[UPDATED] action Test-Action',
            '[UPDATED] routes /bar',
        ];
        $this->assertEquals($expect, $result->getLogs());

        $expect = [
            ['https://api.acme.com', 'POST', 'authorization/token', null, ['Authorization' => 'Basic Zm9vOmJhcg==', 'Content-Type' => 'application/x-www-form-urlencoded'], 'grant_type=client_credentials'],
        ];

        $this->assertEquals(25, count($transport->getRequests()));
        $this->assertEquals($expect[0], $transport->getRequests()[0]);
    }

    private function newTransport(): Memory
    {
        $transport = new Memory();
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['access_token' => '2YotnFZFEjr1zCsicMWpAA'])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));
        $transport->addResponse(new HttpResponse(200, [], \json_encode(['success' => true])));

        return $transport;
    }
}