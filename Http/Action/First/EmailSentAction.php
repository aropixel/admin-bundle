<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 10/10/2023 à 11:41
 */

namespace Aropixel\AdminBundle\Http\Action\First;

use Aropixel\AdminBundle\Http\Form\Reset\FirstLoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailSentAction extends AbstractController
{

    public function __invoke()
    {
        return $this->render('@AropixelAdmin/First/email_sent.html.twig');
    }

}
