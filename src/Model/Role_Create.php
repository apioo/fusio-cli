<?php

declare(strict_types = 1);

namespace Fusio\Cli\Model;

/**
 * @Required({"categoryId", "name"})
 */
class Role_Create extends Role implements \JsonSerializable
{
}
