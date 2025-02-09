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

namespace Fusio\Cli\Tests\Deploy;

use Fusio\Cli\Deploy\EnvReplacer;
use Fusio\Cli\Deploy\EnvReplacerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * EnvReplacerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class EnvReplacerTest extends TestCase
{
    public function testReplace()
    {
        $envReplacer = $this->newEnvReplacer([
            'FOO' => 'bar'
        ]);

        $data   = 'dbname: "${env.FOO}"';
        $actual = $envReplacer->replace($data);
        $expect = 'dbname: "bar"';

        $this->assertEquals($expect, $actual, $actual);
    }

    public function testReplaceMultiple()
    {
        $envReplacer = $this->newEnvReplacer([
            'APIOO_DB_NAME' => 'db_name',
            'APIOO_DB_USER' => 'db_user',
            'APIOO_DB_PW'   => 'db_pw',
            'MYSQL_HOST'    => 'host',
        ]);

        $data = <<<'YAML'
Default-Connection:
  class: Fusio\Adapter\Sql\Connection\Sql
  config:
    dbname: "${env.APIOO_DB_NAME}"
    user: "${env.APIOO_DB_USER}"
    password: "${env.APIOO_DB_PW}"
    host: "${env.MYSQL_HOST}"
    driver: "pdo_mysql"

YAML;

        $actual = $envReplacer->replace($data);
        $data   = Yaml::parse($actual);
        $config = $data['Default-Connection']['config'];

        $this->assertEquals('db_name', $config['dbname']);
        $this->assertEquals('db_user', $config['user']);
        $this->assertEquals('db_pw', $config['password']);
        $this->assertEquals('host', $config['host']);
    }

    public function testReplaceCase()
    {
        $envReplacer = $this->newEnvReplacer([
            'foo' => 'bar'
        ]);

        $data   = 'dbname: "${env.FOO}"';
        $actual = $envReplacer->replace($data);
        $expect = 'dbname: "bar"';

        $this->assertEquals($expect, $actual, $actual);
    }

    public function testReplaceEscape()
    {
        $envReplacer = $this->newEnvReplacer([
            'foo' => 'foo' . "\n" . 'bar"test'
        ]);

        $data   = 'dbname: "${env.FOO}"';
        $actual = $envReplacer->replace($data);
        $expect = 'dbname: "foo\nbar\"test"';

        $this->assertEquals($expect, $actual, $actual);
    }

    public function testReplaceUnknownType()
    {
        $this->expectException(\RuntimeException::class);

        $envReplacer = $this->newEnvReplacer();
        $envReplacer->replace('dbname: "${foo.FOO}"');
    }

    public function testReplaceUnknownKey()
    {
        $this->expectException(\RuntimeException::class);

        $envReplacer = $this->newEnvReplacer([
            'baz' => 'bar'
        ]);
        $envReplacer->replace('dbname: "${env.FOO}"');
    }

    private function newEnvReplacer(?array $env = null): EnvReplacerInterface
    {
        return new EnvReplacer($env);
    }
}
