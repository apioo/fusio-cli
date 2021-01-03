<?php

declare(strict_types = 1);

namespace Fusio\Cli\Model;

/**
 * @Required({"name", "price"})
 */
class Plan_Create extends Plan implements \JsonSerializable
{
}
