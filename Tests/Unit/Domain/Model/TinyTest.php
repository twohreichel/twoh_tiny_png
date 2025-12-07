<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TWOH\TwohTinyPng\Domain\Model\Tiny;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Test case for Tiny model
 */
#[CoversClass(Tiny::class)]
final class TinyTest extends TestCase
{
    private Tiny $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Tiny();
    }

    #[Test]
    public function getIdentifierReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getIdentifier());
    }

    #[Test]
    public function setIdentifierSetsIdentifier(): void
    {
        $this->subject->setIdentifier('fileadmin/images/test.jpg');

        self::assertSame('fileadmin/images/test.jpg', $this->subject->getIdentifier());
    }

    #[Test]
    #[DataProvider('identifierDataProvider')]
    public function setIdentifierWithVariousValues(string $identifier): void
    {
        $this->subject->setIdentifier($identifier);

        self::assertSame($identifier, $this->subject->getIdentifier());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function identifierDataProvider(): array
    {
        return [
            'simple filename' => ['test.jpg'],
            'path with filename' => ['fileadmin/images/test.jpg'],
            'deep nested path' => ['fileadmin/user_upload/gallery/2024/01/photo.png'],
            'empty string' => [''],
            'filename with spaces' => ['my image file.jpg'],
            'filename with special chars' => ['bild-übersicht_2024.png'],
        ];
    }

    #[Test]
    public function getDimensionReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getDimension());
    }

    #[Test]
    public function setDimensionSetsDimension(): void
    {
        $this->subject->setDimension('2560');

        self::assertSame('2560', $this->subject->getDimension());
    }

    #[Test]
    #[DataProvider('dimensionDataProvider')]
    public function setDimensionWithVariousValues(string $dimension): void
    {
        $this->subject->setDimension($dimension);

        self::assertSame($dimension, $this->subject->getDimension());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function dimensionDataProvider(): array
    {
        return [
            'small dimension' => ['800'],
            'medium dimension' => ['1920'],
            'large dimension' => ['2560'],
            'extra large dimension' => ['4096'],
            'empty string' => [''],
        ];
    }

    #[Test]
    public function getWidthReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getWidth());
    }

    #[Test]
    public function setWidthSetsWidth(): void
    {
        $this->subject->setWidth('1920');

        self::assertSame('1920', $this->subject->getWidth());
    }

    #[Test]
    #[DataProvider('widthHeightDataProvider')]
    public function setWidthWithVariousValues(string $value): void
    {
        $this->subject->setWidth($value);

        self::assertSame($value, $this->subject->getWidth());
    }

    #[Test]
    public function getHeightReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getHeight());
    }

    #[Test]
    public function setHeightSetsHeight(): void
    {
        $this->subject->setHeight('1080');

        self::assertSame('1080', $this->subject->getHeight());
    }

    #[Test]
    #[DataProvider('widthHeightDataProvider')]
    public function setHeightWithVariousValues(string $value): void
    {
        $this->subject->setHeight($value);

        self::assertSame($value, $this->subject->getHeight());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function widthHeightDataProvider(): array
    {
        return [
            'small value' => ['100'],
            'medium value' => ['1080'],
            'large value' => ['2160'],
            'zero' => ['0'],
            'empty string' => [''],
        ];
    }

    #[Test]
    public function getPidReturnsInitialValue(): void
    {
        self::assertSame(0, $this->subject->getPid());
    }

    #[Test]
    public function modelCanBeInstantiated(): void
    {
        self::assertInstanceOf(Tiny::class, $this->subject);
    }

    #[Test]
    public function allPropertiesCanBeSetAndRetrieved(): void
    {
        $identifier = 'fileadmin/images/photo.jpg';
        $dimension = '2560';
        $width = '1920';
        $height = '1080';

        $this->subject->setIdentifier($identifier);
        $this->subject->setDimension($dimension);
        $this->subject->setWidth($width);
        $this->subject->setHeight($height);

        self::assertSame($identifier, $this->subject->getIdentifier());
        self::assertSame($dimension, $this->subject->getDimension());
        self::assertSame($width, $this->subject->getWidth());
        self::assertSame($height, $this->subject->getHeight());
    }

    #[Test]
    public function modelExtendsAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }
}
