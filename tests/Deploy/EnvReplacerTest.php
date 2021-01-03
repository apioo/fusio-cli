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
 * @link    http://phpsx.org
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
