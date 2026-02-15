<?php

namespace Aropixel\AdminBundle\Component\Menu\Builder;

use Aropixel\AdminBundle\Component\Menu\LinkMatcher;
use Symfony\Component\String\Slugger\AsciiSlugger;

class QuickMenuBuilder implements QuickMenuBuilderInterface
{
    public function __construct(
        private readonly LinkMatcher $linkMatcher
    ) {
    }

    public function buildMenu(): array
    {
        $quickMenu = [
            [
                'position' => 1,
                'id' => $this->generateId('Pages'),
            ],
            [
                'position' => 2,
                'id' => $this->generateId('Actualités'),
            ],
            [
                'position' => 3,
                'id' => $this->generateId('Messagerie'),
            ],
            [
                'position' => 4,
                'id' => $this->generateId('Menu'),
            ],
            [
                'position' => 5,
                'id' => $this->generateId('Administrateurs'),
            ],
        ];

        $menu = [];
        foreach ($quickMenu as $link) {
            $id = $link['id'];
            if ($match = $this->linkMatcher->getLink($id)) {
                $menu[$link['position']] = $match;
            }
        }

        return $menu;
    }

    private function generateId(string $label): string
    {
        $slugger = new AsciiSlugger();

        return mb_strtolower($slugger->slug($label));
    }
}
