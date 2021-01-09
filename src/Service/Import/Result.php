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

namespace Fusio\Cli\Service\Import;

/**
 * Result
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Result
{
    public const ACTION_FAILED  = 'FAILED';
    public const ACTION_UPDATED = 'UPDATED';
    public const ACTION_CREATED = 'CREATED';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $message;

    public function __construct(string $type, string $action, string $message)
    {
        $this->type = $type;
        $this->action = $action;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public function toString(): string
    {
        return '[' . $this->getAction() . '] ' . $this->getType() . ' ' . $this->getMessage();
    }

    public function __toString()
    {
        return $this->toString();
    }
}
