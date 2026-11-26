<?php

namespace Aropixel\AdminBundle\Tests\Unit\Form\Type;

use Aropixel\AdminBundle\Form\Type\EditorType;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\Test\TypeTestCase;

// TypeTestCase mocks the event dispatcher without expectations
#[AllowMockObjectsWithoutExpectations]
class EditorTypeTest extends TypeTestCase
{
    #[DataProvider('provideBlankEditorIsSubmittedAsNullCases')]
    public function testBlankEditorIsSubmittedAsNull(string $html): void
    {
        $form = $this->factory->create(EditorType::class);
        $form->submit($html);

        self::assertTrue($form->isSynchronized());
        self::assertNull($form->getData());
    }

    public static function provideBlankEditorIsSubmittedAsNullCases(): iterable
    {
        yield 'empty Quill line' => ['<p><br></p>'];
        yield 'aligned empty line' => ['<p class="ql-align-center"><br></p>'];
        yield 'empty heading' => ['<h2><br></h2>'];
        yield 'several empty lines' => ['<p><br></p><p><br></p>'];
        yield 'non-breaking spaces' => ['<p>&nbsp; &nbsp;</p>'];
        yield 'whitespace only' => [" \n "];
    }

    #[DataProvider('provideContentIsKeptCases')]
    public function testContentIsKept(string $html): void
    {
        $form = $this->factory->create(EditorType::class);
        $form->submit($html);

        self::assertSame($html, $form->getData());
    }

    public static function provideContentIsKeptCases(): iterable
    {
        yield 'text' => ['<p>Jean Dupont</p>'];
        yield 'image only' => ['<p><img src="/a.jpg"></p>'];
        yield 'video only' => ['<iframe class="ql-video" src="https://example.com"></iframe>'];
    }
}
