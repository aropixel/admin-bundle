<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\Publishable;

trait PublishableTrait
{
    public function isPublished(): bool
    {
        if (property_exists($this, 'status') && Publishable::STATUS_OFFLINE == $this->status) {
            return false;
        }

        return !$this->isScheduled() || !$this->isScheduleOutdated() && !$this->isScheduleIncoming();
    }

    public function isScheduled(): bool
    {
        return
            (
                (property_exists($this, 'publishAt') && null !== $this->publishAt)
                || (property_exists($this, 'publishUntil') && null !== $this->publishUntil)
            )
            && (property_exists($this, 'status') && Publishable::STATUS_ONLINE == $this->status);
    }

    public function isScheduleIncoming(): bool
    {
        $now = new \DateTime();

        return property_exists($this, 'publishAt') && null !== $this->publishAt && ($this->publishAt > $now);
    }

    public function isScheduleOutdated(): bool
    {
        $now = new \DateTime();

        return property_exists($this, 'publishUntil') && null !== $this->publishUntil && ($now > $this->publishUntil);
    }
}
