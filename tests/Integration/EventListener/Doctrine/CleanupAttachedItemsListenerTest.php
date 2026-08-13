<?php

namespace Aropixel\AdminBundle\Tests\Integration\EventListener\Doctrine;

use App\Entity\ProjectImage;
use Aropixel\AdminBundle\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CleanupAttachedItemsListenerTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    public function testDeletingAnImageRemovesItsAttachments(): void
    {
        $image = $this->createImage();
        $imageId = $image->getId();

        $attached = new ProjectImage();
        $attached->setImage($image);
        $this->em->persist($attached);
        $this->em->flush();

        $attachedId = $attached->getId();
        self::assertNotNull($attachedId, 'L\'attachement doit être enregistré avant le scénario.');

        $this->em->remove($image);
        $this->em->flush();

        self::assertNull($this->em->find(Image::class, $imageId), 'L\'image doit être supprimée.');
        self::assertNull($this->em->find(ProjectImage::class, $attachedId), 'L\'attachement doit avoir suivi.');
    }

    public function testAttachmentsOfOtherImagesAreLeftAlone(): void
    {
        $deleted = $this->createImage();
        $kept = $this->createImage();

        $attachedToDeleted = new ProjectImage();
        $attachedToDeleted->setImage($deleted);

        $attachedToKept = new ProjectImage();
        $attachedToKept->setImage($kept);

        $this->em->persist($attachedToDeleted);
        $this->em->persist($attachedToKept);
        $this->em->flush();

        $keptAttachmentId = $attachedToKept->getId();

        $this->em->remove($deleted);
        $this->em->flush();

        self::assertNotNull(
            $this->em->find(ProjectImage::class, $keptAttachmentId),
            'L\'attachement d\'une autre image ne doit pas être touché.'
        );
    }

    protected function setUp(): void
    {
        if (!class_exists(ProjectImage::class)) {
            self::markTestSkipped('Requiert une classe d\'attachement concrète (App\Entity\ProjectImage).');
        }

        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;

        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->em) && $this->em->getConnection()->isTransactionActive()) {
            $this->em->getConnection()->rollBack();
        }

        parent::tearDown();
    }

    private function createImage(): Image
    {
        $image = new Image();
        $image->setTitle('cleanup-listener');
        $image->setCategory(ProjectImage::class);
        $image->setFilename(uniqid('cleanup-', true) . '.png');
        $image->setExtension('png');

        $this->em->persist($image);
        $this->em->flush();

        return $image;
    }
}
