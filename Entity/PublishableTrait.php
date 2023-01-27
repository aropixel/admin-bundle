<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 17/01/2020 à 17:42
 */

namespace Aropixel\AdminBundle\Entity;


trait PublishableTrait
{

    function isPublished() : bool {

        if (property_exists($this, 'status') && $this->status == Publishable::STATUS_OFFLINE) {
            return false;
        }

        //
        $now = new \DateTime();


        //
        if (property_exists($this, 'publishAt') && !is_null($this->publishAt) && ($this->publishAt > $now)) {
            return false;
        }

        //
        if (property_exists($this, 'publishUntil') && !is_null($this->publishUntil) && ($now > $this->publishUntil)) {
            return false;
        }

        return true;
    }

    function isScheduled() : bool {

        $now = new \DateTime();

        if (
            property_exists($this, 'publishAt') && !is_null($this->publishAt) && ($this->publishAt > $now) &&
            property_exists($this, 'status') && $this->status == Publishable::STATUS_ONLINE
        ) {
            return true;
        }


        return false;
    }

}
