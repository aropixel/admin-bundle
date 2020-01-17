<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 17/01/2020 à 17:42
 */

namespace Aropixel\AdminBundle\Entity;


trait PublishableTrait
{

    function isPublished() {

        if ($this->status == Publishable::STATUS_OFFLINE) {
            return false;
        }

        //
        $now = new \DateTime();

        //
        if (property_exists($this, 'publishAt') && ($this->publishAt > $now)) {
            return false;
        }

        //
        if (property_exists($this, 'publishUntil') && ($now > $this->publishUntil)) {
            return false;
        }

        return true;
    }

}
