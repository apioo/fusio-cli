<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Cli\Service\Import;

use PSX\Http\Environment\HttpResponseInterface;

/**
 * Result
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class Result
{
    public const ACTION_FAILED  = 'FAILED';
    public const ACTION_UPDATED = 'UPDATED';
    public const ACTION_CREATED = 'CREATED';

    private string $type;
    private string $action;
    private string $message;
    private ?HttpResponseInterface $response;

    public function __construct(string $type, string $action, string $message, ?HttpResponseInterface $response = null)
    {
        $this->type = $type;
        $this->action = $action;
        $this->message = $message;
        $this->response = $response;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getResponse(): ?HttpResponseInterface
    {
        return $this->response;
    }

    public function toString(): string
    {
        return '[' . $this->getAction() . '] ' . $this->getType() . ' ' . $this->getMessage();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
