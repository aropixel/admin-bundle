<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 09/04/2019 à 16:54
 */

namespace Aropixel\AdminBundle\Entity;


interface Publishable
{

    /** @var string  */
    const STATUS_ONLINE = 'online';


    /** @var string  */
    const STATUS_OFFLINE = 'offline';

}
