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

namespace Fusio\Cli\Service;

use Fusio\Model\Backend;

/**
 * Types
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
    public const TYPE_PLAN = 'plan';
    public const TYPE_RATE = 'rate';
    public const TYPE_ROLE = 'role';
    public const TYPE_ROUTE = 'routes';
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
        self::TYPE_ROUTE      => ['path', Backend\Route::class],
    ];

    public static function getTypes(): array
    {
        return self::$types;
    }
}
