<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Builder;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Aropixel\AdminBundle\Infrastructure\Menu\LinkMatcher;

class QuickMenuBuilder implements QuickMenuBuilderInterface
{
    public ?array $menu = null;

    public function __construct(
        private readonly Security $security,
        private readonly LinkMatcher $linkMatcher
    ) {
    }

    public function buildMenu(): array
    {
        $isSuperAdmin = $this->security->isGranted('ROLE_SUPER_ADMIN');

        $quickMenu = [
            [
                'position' => 1,
                'id' => $this->generateId('Lien 1'),
            ],
            [
                'position' => 2,
                'id' => $this->generateId('Lien 2'),
            ],
            [
                'position' => 3,
                'id' => $this->generateId('Lien 3'),
            ],
            [
                'position' => 4,
                'id' => $this->generateId('Lien 4'),
            ],
            [
                'position' => 5,
                'id' => $this->generateId('Lien 5'),
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
