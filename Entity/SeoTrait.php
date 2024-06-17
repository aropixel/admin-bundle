<?php

namespace Aropixel\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait SeoTrait
{
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $metaTitle;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $metaDescription;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $metaKeywords;

    /**
     * @return mixed
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }
}
