<?php

/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace HiveCommerce\ContentFuzzyfyr\Model;

/**
 * Class Configuration
 * @package HiveCommerce\ContentFuzzyfyr\Model
 */
class Configuration
{
    /**
     * @var bool
     */
    private $useOnlyEmpty;
    /**
     * @var bool
     */
    private $applyToCategories;
    /**
     * @var bool
     */
    private $applyToCmsBlocks;
    /**
     * @var bool
     */
    private $applyToCmsPages;
    /**
     * @var bool
     */
    private $applyToCustomers;
    /**
     * @var bool
     */
    private $applyToProducts;
    /**
     * @var bool
     */
    private $applyToUsers;
    /**
     * @var string
     */
    private $dummyContentText;
    /**
     * @var string
     */
    private $dummyPassword;
    /**
     * @var string
     */
    private $dummyContentEmail;
    /**
     * @var string
     */
    private $dummyContentUrl;
    /**
     * @var string
     */
    private $dummyPhoneNumber;
    /**
     * @var string
     */
    private $dummyImagePath;
    /**
     * @var array
     */
    private $extendedData;

    /**
     * @return bool
     */
    public function isUseOnlyEmpty(): bool
    {
        return $this->useOnlyEmpty;
    }

    /**
     * @param bool $useOnlyEmpty
     * @return Configuration
     */
    public function setUseOnlyEmpty(bool $useOnlyEmpty): Configuration
    {
        $this->useOnlyEmpty = $useOnlyEmpty;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToCategories(): bool
    {
        return $this->applyToCategories;
    }

    /**
     * @param bool $applyToCategories
     * @return Configuration
     */
    public function setApplyToCategories(bool $applyToCategories): Configuration
    {
        $this->applyToCategories = $applyToCategories;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToCmsBlocks(): bool
    {
        return $this->applyToCmsBlocks;
    }

    /**
     * @param bool $applyToCmsBlocks
     * @return Configuration
     */
    public function setApplyToCmsBlocks(bool $applyToCmsBlocks): Configuration
    {
        $this->applyToCmsBlocks = $applyToCmsBlocks;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToCmsPages(): bool
    {
        return $this->applyToCmsPages;
    }

    /**
     * @param bool $applyToCmsPages
     * @return Configuration
     */
    public function setApplyToCmsPages(bool $applyToCmsPages): Configuration
    {
        $this->applyToCmsPages = $applyToCmsPages;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToCustomers(): bool
    {
        return $this->applyToCustomers;
    }

    /**
     * @param bool $applyToCustomers
     * @return Configuration
     */
    public function setApplyToCustomers(bool $applyToCustomers): Configuration
    {
        $this->applyToCustomers = $applyToCustomers;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToProducts(): bool
    {
        return $this->applyToProducts;
    }

    /**
     * @param bool $applyToProducts
     * @return Configuration
     */
    public function setApplyToProducts(bool $applyToProducts): Configuration
    {
        $this->applyToProducts = $applyToProducts;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToUsers(): bool
    {
        return $this->applyToUsers;
    }

    /**
     * @param bool $applyToUsers
     * @return Configuration
     */
    public function setApplyToUsers(bool $applyToUsers): Configuration
    {
        $this->applyToUsers = $applyToUsers;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyContentText(): string
    {
        return $this->dummyContentText;
    }

    /**
     * @param string $dummyContentText
     * @return Configuration
     */
    public function setDummyContentText(string $dummyContentText): Configuration
    {
        $this->dummyContentText = $dummyContentText;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyPassword(): string
    {
        return $this->dummyPassword;
    }

    /**
     * @param string $dummyPassword
     * @return Configuration
     */
    public function setDummyPassword(string $dummyPassword): Configuration
    {
        $this->dummyPassword = $dummyPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyContentEmail(): string
    {
        return $this->dummyContentEmail;
    }

    /**
     * @param string $dummyContentEmail
     * @return Configuration
     */
    public function setDummyContentEmail(string $dummyContentEmail): Configuration
    {
        $this->dummyContentEmail = $dummyContentEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyContentUrl(): string
    {
        return $this->dummyContentUrl;
    }

    /**
     * @param string $dummyContentUrl
     * @return Configuration
     */
    public function setDummyContentUrl(string $dummyContentUrl): Configuration
    {
        $this->dummyContentUrl = $dummyContentUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyPhoneNumber(): string
    {
        return $this->dummyPhoneNumber;
    }

    /**
     * @param string $dummyPhoneNumber
     * @return Configuration
     */
    public function setDummyPhoneNumber(string $dummyPhoneNumber): Configuration
    {
        $this->dummyPhoneNumber = $dummyPhoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyImagePath(): string
    {
        return $this->dummyImagePath;
    }

    /**
     * @param string $dummyImagePath
     * @return Configuration
     */
    public function setDummyImagePath(string $dummyImagePath): Configuration
    {
        $this->dummyImagePath = $dummyImagePath;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtendedData(): array
    {
        return $this->extendedData;
    }

    /**
     * @param array $extendedData
     * @return Configuration
     */
    public function setExtendedData(array $extendedData): Configuration
    {
        $this->extendedData = $extendedData;
        return $this;
    }
}
