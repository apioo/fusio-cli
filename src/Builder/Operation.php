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

namespace Fusio\Cli\Builder;

use Fusio\Cli\Builder\Operation\HttpMethod;
use Fusio\Cli\Builder\Operation\Stability;
use PSX\Schema\Type\PropertyTypeAbstract;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Operation implements BuilderInterface
{
    private ?array $scopes = null;
    private ?bool $public = null;
    private ?int $stability = null;
    private ?string $description = null;
    private ?string $httpMethod = null;
    private ?string $httpPath = null;
    private ?int $httpCode = null;
    private array $parameters = [];
    private ?string $incoming = null;
    private ?string $outgoing = null;
    private array $throws = [];
    private ?string $action = null;

    public function setScopes(array $scopes): void
    {
        $this->scopes = $scopes;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function setStability(Stability $stability): void
    {
        $this->stability = $stability->value;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setHttpMethod(HttpMethod $httpMethod): void
    {
        $this->httpMethod = $httpMethod->value;
    }

    public function setHttpPath(string $httpPath): void
    {
        $this->httpPath = $httpPath;
    }

    public function setHttpCode(int $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    public function addParameter(string $name, PropertyTypeAbstract $type): void
    {
        $this->parameters[$name] = $type->toArray();
    }

    public function setIncoming(string $incoming): void
    {
        $this->incoming = $incoming;
    }

    public function setOutgoing(string $outgoing): void
    {
        $this->outgoing = $outgoing;
    }

    public function addThrow(int $code, string $throw): void
    {
        $this->throws[$code] = $throw;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function toArray(): array
    {
        return array_filter([
            'scopes' => $this->scopes,
            'public' => $this->public,
            'stability' => $this->stability,
            'description' => $this->description,
            'httpMethod' => $this->httpMethod,
            'httpPath' => $this->httpPath,
            'httpCode' => $this->httpCode,
            'parameters' => $this->parameters,
            'incoming' => $this->incoming,
            'outgoing' => $this->outgoing,
            'throws' => $this->throws,
            'action' => $this->action,
        ], fn ($value) => $value !== null);
    }
}
