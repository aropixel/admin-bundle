<?php

namespace Aropixel\AdminBundle\Entity;

interface Publishable
{
    /** @var string */
    public const STATUS_ONLINE = 'online';

    /** @var string */
    public const STATUS_OFFLINE = 'offline';
}
