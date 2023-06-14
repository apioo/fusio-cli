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

namespace Fusio\Cli\Service\Import;

use Fusio\Model\Backend;

/**
 * Types
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://fusio-project.org
 */
class Types
{
    public const COLLECTION_SIZE = 64;

    public const TYPE_ACTION = 'action';
    public const TYPE_CONFIG = 'config';
    public const TYPE_CONNECTION = 'connection';
    public const TYPE_CRONJOB = 'cronjob';
    public const TYPE_EVENT = 'event';
    public const TYPE_OPERATION = 'operation';
    public const TYPE_PLAN = 'plan';
    public const TYPE_RATE = 'rate';
    public const TYPE_ROLE = 'role';
    public const TYPE_SCHEMA = 'schema';
    public const TYPE_SCOPE = 'scope';

    private static array $types = [
        self::TYPE_ACTION     => ['name', Backend\Action::class],
        self::TYPE_CONFIG     => ['name', Backend\Config::class],
        self::TYPE_CONNECTION => ['name', Backend\Connection::class],
        self::TYPE_CRONJOB    => ['name', Backend\Cronjob::class],
        self::TYPE_EVENT      => ['name', Backend\Event::class],
        self::TYPE_PLAN       => ['name', Backend\Plan::class],
        self::TYPE_RATE       => ['name', Backend\Rate::class],
        self::TYPE_ROLE       => ['name', Backend\Role::class],
        self::TYPE_SCHEMA     => ['name', Backend\Schema::class],
        self::TYPE_SCOPE      => ['name', Backend\Scope::class],
        self::TYPE_OPERATION  => ['name', Backend\Operation::class],
    ];

    public static function getTypes(): array
    {
        return self::$types;
    }
}
