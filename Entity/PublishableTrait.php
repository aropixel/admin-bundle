<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 17/01/2020 à 17:42
 */

namespace Aropixel\AdminBundle\Entity;


use Aropixel\AdminBundle\Entity\Publishable;

trait PublishableTrait
{

    function isPublished() : bool {

        if (property_exists($this, 'status') && $this->status == Publishable::STATUS_OFFLINE) {
            return false;
        }

        return !$this->isScheduled() || !$this->isScheduleOutdated() && !$this->isScheduleIncoming();
    }

    function isScheduled() : bool {
        return (
            (
                (property_exists($this, 'publishAt') && !is_null($this->publishAt)) ||
                (property_exists($this, 'publishUntil') && !is_null($this->publishUntil))
            ) &&
            (property_exists($this, 'status') && $this->status == Publishable::STATUS_ONLINE)

        );
    }

    function isScheduleIncoming() : bool {

        $now = new \DateTime();
        return (property_exists($this, 'publishAt') && !is_null($this->publishAt) && ($this->publishAt > $now));

    }

    function isScheduleOutdated() : bool {

        $now = new \DateTime();
        return (property_exists($this, 'publishUntil') && !is_null($this->publishUntil) && ($now > $this->publishUntil));
    }

}
