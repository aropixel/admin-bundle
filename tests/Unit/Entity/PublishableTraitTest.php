<?php

namespace Aropixel\AdminBundle\Tests\Unit\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use PHPUnit\Framework\TestCase;

class ConcretePublishable
{
    use PublishableTrait;

    public string $status = Publishable::STATUS_ONLINE;
    public ?\DateTime $publishAt = null;
    public ?\DateTime $publishUntil = null;
}

class PublishableTraitTest extends TestCase
{
    private function make(string $status = Publishable::STATUS_ONLINE, ?\DateTime $publishAt = null, ?\DateTime $publishUntil = null): ConcretePublishable
    {
        $obj = new ConcretePublishable();
        $obj->status = $status;
        $obj->publishAt = $publishAt;
        $obj->publishUntil = $publishUntil;
        return $obj;
    }

    public function testOfflineIsNotPublished(): void
    {
        $obj = $this->make(Publishable::STATUS_OFFLINE);
        $this->assertFalse($obj->isPublished());
    }

    public function testOnlineWithoutDatesIsPublished(): void
    {
        $obj = $this->make(Publishable::STATUS_ONLINE);
        $this->assertTrue($obj->isPublished());
    }

    public function testPublishAtInFutureIsNotPublished(): void
    {
        $obj = $this->make(Publishable::STATUS_ONLINE, new \DateTime('+1 hour'));
        $this->assertFalse($obj->isPublished());
    }

    public function testPublishAtInFutureIsScheduleIncoming(): void
    {
        $obj = $this->make(Publishable::STATUS_ONLINE, new \DateTime('+1 hour'));
        $this->assertTrue($obj->isScheduleIncoming());
    }

    public function testPublishUntilInPastIsNotPublished(): void
    {
        $obj = $this->make(Publishable::STATUS_ONLINE, null, new \DateTime('-1 hour'));
        $this->assertFalse($obj->isPublished());
    }

    public function testPublishUntilInPastIsScheduleOutdated(): void
    {
        $obj = $this->make(Publishable::STATUS_ONLINE, null, new \DateTime('-1 hour'));
        $this->assertTrue($obj->isScheduleOutdated());
    }

    public function testActiveWindowIsPublished(): void
    {
        $obj = $this->make(Publishable::STATUS_ONLINE, new \DateTime('-1 hour'), new \DateTime('+1 hour'));
        $this->assertTrue($obj->isPublished());
    }

    public function testOfflineWithPublishAtIsNotPublished(): void
    {
        $obj = $this->make(Publishable::STATUS_OFFLINE, new \DateTime('-1 hour'));
        $this->assertFalse($obj->isPublished());
    }
}
