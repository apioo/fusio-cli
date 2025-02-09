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
use Fusio\Cli\Deploy\IncludeDirective;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * IncludeDirectiveTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class IncludeDirectiveTest extends TestCase
{
    public function testResolveTaggedValue()
    {
        $include = $this->newIncludeDirective();
        $data = $include->resolve(new TaggedValue('include', 'Resource/test.yaml'), __DIR__, '');

        $this->assertEquals('my_tag', $data['foo']['bar']->getTag());
        $this->assertEquals('test', $data['foo']['bar']->getValue());
    }

    public function testResolveTaggedValuePointer()
    {
        $include = $this->newIncludeDirective();
        $data = $include->resolve(new TaggedValue('include', 'Resource/test.yaml#/foo'), __DIR__, '');

        $this->assertEquals('my_tag', $data['bar']->getTag());
        $this->assertEquals('test', $data['bar']->getValue());
    }

    public function testResolveTaggedValueInvalidFile()
    {
        $this->expectException(\RuntimeException::class);

        $include = $this->newIncludeDirective();
        $include->resolve(new TaggedValue('include', 'Resource/foo.yaml'), __DIR__, '');
    }

    public function testResolveTaggedValueInvalidTag()
    {
        $this->expectException(\RuntimeException::class);

        $include = $this->newIncludeDirective();
        $include->resolve(new TaggedValue('foo', 'Resource/test.yaml'), __DIR__, '');
    }

    public function testResolveInvalidValue()
    {
        $this->expectException(\RuntimeException::class);

        $include = $this->newIncludeDirective();
        $include->resolve('foo', __DIR__, '');
    }

    public function testResolveArray()
    {
        $include = $this->newIncludeDirective();
        $data = $include->resolve(['foo' => 'bar'], __DIR__, '');

        $this->assertEquals(['foo' => 'bar'], $data);
    }
    
    private function newIncludeDirective()
    {
        return new IncludeDirective(new EnvReplacer());
    }
}
