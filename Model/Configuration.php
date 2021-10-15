<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    public function isUseOnlyEmpty()
    {
        return $this->useOnlyEmpty;
    }

    /**
     * @param bool $useOnlyEmpty
     * @return Configuration
     */
    public function setUseOnlyEmpty($useOnlyEmpty)
    {
        $this->useOnlyEmpty = $useOnlyEmpty;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToCategories()
    {
        return $this->applyToCategories;
    }

    /**
     * @param bool $applyToCategories
     * @return Configuration
     */
    public function setApplyToCategories($applyToCategories)
    {
        $this->applyToCategories = $applyToCategories;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToCmsBlocks()
    {
        return $this->applyToCmsBlocks;
    }

    /**
     * @param bool $applyToCmsBlocks
     * @return Configuration
     */
    public function setApplyToCmsBlocks($applyToCmsBlocks)
    {
        $this->applyToCmsBlocks = $applyToCmsBlocks;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToCmsPages()
    {
        return $this->applyToCmsPages;
    }

    /**
     * @param bool $applyToCmsPages
     * @return Configuration
     */
    public function setApplyToCmsPages($applyToCmsPages)
    {
        $this->applyToCmsPages = $applyToCmsPages;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToCustomers()
    {
        return $this->applyToCustomers;
    }

    /**
     * @param bool $applyToCustomers
     * @return Configuration
     */
    public function setApplyToCustomers($applyToCustomers)
    {
        $this->applyToCustomers = $applyToCustomers;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToProducts()
    {
        return $this->applyToProducts;
    }

    /**
     * @param bool $applyToProducts
     * @return Configuration
     */
    public function setApplyToProducts($applyToProducts)
    {
        $this->applyToProducts = $applyToProducts;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApplyToUsers()
    {
        return $this->applyToUsers;
    }

    /**
     * @param bool $applyToUsers
     * @return Configuration
     */
    public function setApplyToUsers($applyToUsers)
    {
        $this->applyToUsers = $applyToUsers;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyContentText()
    {
        return $this->dummyContentText;
    }

    /**
     * @param string $dummyContentText
     * @return Configuration
     */
    public function setDummyContentText($dummyContentText)
    {
        $this->dummyContentText = $dummyContentText;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyPassword()
    {
        return $this->dummyPassword;
    }

    /**
     * @param string $dummyPassword
     * @return Configuration
     */
    public function setDummyPassword($dummyPassword)
    {
        $this->dummyPassword = $dummyPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyContentEmail()
    {
        return $this->dummyContentEmail;
    }

    /**
     * @param string $dummyContentEmail
     * @return Configuration
     */
    public function setDummyContentEmail($dummyContentEmail)
    {
        $this->dummyContentEmail = $dummyContentEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyContentUrl()
    {
        return $this->dummyContentUrl;
    }

    /**
     * @param string $dummyContentUrl
     * @return Configuration
     */
    public function setDummyContentUrl($dummyContentUrl)
    {
        $this->dummyContentUrl = $dummyContentUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyPhoneNumber()
    {
        return $this->dummyPhoneNumber;
    }

    /**
     * @param string $dummyPhoneNumber
     * @return Configuration
     */
    public function setDummyPhoneNumber($dummyPhoneNumber)
    {
        $this->dummyPhoneNumber = $dummyPhoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getDummyImagePath()
    {
        return $this->dummyImagePath;
    }

    /**
     * @param string $dummyImagePath
     * @return Configuration
     */
    public function setDummyImagePath($dummyImagePath)
    {
        $this->dummyImagePath = $dummyImagePath;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtendedData()
    {
        return $this->extendedData;
    }

    /**
     * @param array $extendedData
     * @return Configuration
     */
    public function setExtendedData($extendedData)
    {
        $this->extendedData = $extendedData;
        return $this;
    }
}
