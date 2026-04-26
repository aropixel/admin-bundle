<?php

namespace Aropixel\AdminBundle\Tests\Unit\Command;

use Aropixel\AdminBundle\Command\MakeCrudCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class TestableMakeCrudCommand extends MakeCrudCommand
{
    public function extractShortNamePublic(string $fqcn): string
    {
        return $this->extractShortName($fqcn);
    }
}

class MakeCrudCommandTest extends TestCase
{
    private TestableMakeCrudCommand $command;

    protected function setUp(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $this->command = new TestableMakeCrudCommand($kernel);
    }

    public function testExtractShortNameFromFqcnWithBackslashes(): void
    {
        $this->assertSame('Project', $this->command->extractShortNamePublic('App\Entity\Project'));
    }

    public function testExtractShortNameFromStrippedBackslashes(): void
    {
        $this->assertSame('Project', $this->command->extractShortNamePublic('AppEntityProject'));
    }

    public function testExtractShortNameFromSlashes(): void
    {
        $this->assertSame('Project', $this->command->extractShortNamePublic('App/Entity/Project'));
    }

    public function testExtractShortNameAlreadyShort(): void
    {
        $this->assertSame('Project', $this->command->extractShortNamePublic('Project'));
    }

    public function testExtractShortNameWithLeadingBackslash(): void
    {
        $this->assertSame('BlogPost', $this->command->extractShortNamePublic('\App\Entity\BlogPost'));
    }
}
