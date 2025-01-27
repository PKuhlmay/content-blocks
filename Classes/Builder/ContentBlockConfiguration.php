<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\ContentBlocks\Builder;

/**
 * @internal Not part of TYPO3's public API.
 */
final class ContentBlockConfiguration
{
    public function __construct(
        private readonly array $yamlConfig,
        private readonly string $basePath,
    ) {
    }

    public function getYamlConfig(): array
    {
        return $this->yamlConfig;
    }

    public function getVendor(): string
    {
        return explode('/', $this->yamlConfig['name'])[0];
    }

    public function getPackage(): string
    {
        return explode('/', $this->yamlConfig['name'])[1];
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
