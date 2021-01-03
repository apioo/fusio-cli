<?php

declare(strict_types = 1);

namespace Fusio\Cli\Model;

/**
 * @Required({"eventId", "userId", "endpoint"})
 */
class Event_Subscription_Create extends Event_Subscription implements \JsonSerializable
{
}
