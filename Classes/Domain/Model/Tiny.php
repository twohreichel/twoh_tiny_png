<?php

declare(strict_types=1);

namespace TWOH\TwohTinyPng\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Tiny
 * @package TWOH\TwohTinyPng\Domain\Model
 */
class Tiny extends AbstractEntity
{
    /**
     * @var int
     */
    protected $pid = 0;

    /**
     * @var string
     */
    protected string $identifier = '';

    /**
     * @var string
     */
    protected string $dimension = '';

    /**
     * @var string
     */
    protected string $width = '';

    /**
     * @var string
     */
    protected string $height = '';

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getDimension(): string
    {
        return $this->dimension;
    }

    public function setDimension(string $dimension): void
    {
        $this->dimension = $dimension;
    }

    public function getWidth(): string
    {
        return $this->width;
    }

    public function setWidth(string $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): string
    {
        return $this->height;
    }

    public function setHeight(string $height): void
    {
        $this->height = $height;
    }
}
