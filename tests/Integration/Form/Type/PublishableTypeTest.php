<?php

namespace Aropixel\AdminBundle\Tests\Integration\Form\Type;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use Aropixel\AdminBundle\Form\Type\DateTimeType;
use Aropixel\AdminBundle\Form\Type\Page\PublishableType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as SymfonyDateTimeType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class PublishableEntity
{
    use PublishableTrait;

    public string $status = Publishable::STATUS_OFFLINE;
    public ?\DateTime $publishAt = null;
    public ?\DateTime $publishUntil = null;
}

class PublishableTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $dateTimeType = new DateTimeType();

        return [
            new PreloadedExtension([$dateTimeType], []),
        ];
    }

    public function testSubmitOnlineStatus(): void
    {
        $entity = new PublishableEntity();
        $form = $this->factory->create(PublishableType::class, $entity);

        $form->submit(['status' => 'online']);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame(Publishable::STATUS_ONLINE, $entity->status);
    }

    public function testSubmitOfflineStatus(): void
    {
        $entity = new PublishableEntity();
        $form = $this->factory->create(PublishableType::class, $entity);

        $form->submit(['status' => 'offline']);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame(Publishable::STATUS_OFFLINE, $entity->status);
    }
}
