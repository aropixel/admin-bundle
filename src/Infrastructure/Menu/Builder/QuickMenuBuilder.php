<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Builder;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Aropixel\AdminBundle\Infrastructure\Menu\LinkMatcher;

class QuickMenuBuilder implements QuickMenuBuilderInterface
{
    public ?array $menu = null;

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
