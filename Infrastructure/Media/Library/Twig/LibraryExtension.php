<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 13/02/2023 à 10:37
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Library\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LibraryExtension extends AbstractExtension
{

    private bool $loadLibrary;
    private bool $loadFilesLibrary;


    public function getFunctions() : array
    {
        return array(
            'load_library' => new TwigFunction('load_library', array($this, 'setLoadLibrary')),
            'load_files_library' => new TwigFunction('load_files_library', array($this, 'setLoadFilesLibrary')),
        );
    }


    public function setLoadLibrary($load=null)
    {
        if (!is_null($load)) {
            $this->loadLibrary = $load;
        }

        return $this->loadLibrary;
    }


    public function setLoadFilesLibrary($load=null)
    {
        if (!is_null($load)) {
            $this->loadFilesLibrary = $load;
        }
        else {
            return $this->loadFilesLibrary;
        }
    }

}
