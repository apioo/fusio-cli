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

use Fusio\Cli\Model;

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

    public const TYPE_SCOPE = 'scope';
    public const TYPE_USER = 'user';
    public const TYPE_APP = 'app';
    public const TYPE_CONFIG = 'config';
    public const TYPE_CONNECTION = 'connection';
    public const TYPE_SCHEMA = 'schema';
    public const TYPE_ACTION = 'action';
    public const TYPE_ROUTE = 'routes';
    public const TYPE_CRONJOB = 'cronjob';
    public const TYPE_RATE = 'rate';
    public const TYPE_EVENT = 'event';
    public const TYPE_PLAN = 'plan';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_ROLE = 'role';

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_CATEGORY   => ['name', Model\Category::class],
        self::TYPE_SCOPE      => ['name', Model\Scope::class],
        self::TYPE_EVENT      => ['name', Model\Event::class],
        self::TYPE_PLAN       => ['name', Model\Plan::class],
        self::TYPE_CONNECTION => ['name', Model\Connection::class],
        self::TYPE_SCHEMA     => ['name', Model\Schema::class],
        self::TYPE_ACTION     => ['name', Model\Action::class],
        self::TYPE_ROUTE      => ['path', Model\Route::class],
        self::TYPE_CRONJOB    => ['name', Model\Cronjob::class],
        self::TYPE_CONFIG     => ['name', Model\Config::class],
        self::TYPE_RATE       => ['name', Model\Rate::class],
        self::TYPE_USER       => ['name', Model\User::class],
        self::TYPE_APP        => ['name', Model\App::class],
        self::TYPE_ROLE       => ['name', Model\Role::class],
    ];

    public static function getTypes(): array
    {
        return self::$types;
    }
}
