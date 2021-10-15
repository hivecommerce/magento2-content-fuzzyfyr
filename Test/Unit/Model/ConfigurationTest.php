<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Model;

use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;

/**
 * Class ConfigurationTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Model
 */
class ConfigurationTest extends AbstractTest
{
    /**
     * @test
     */
    public function checkGetterAndSetterSuccessfully()
    {
        $configuration = new Configuration();

        // --- Flags
        $configuration->setUseOnlyEmpty(true);
        $this->assertTrue($configuration->isUseOnlyEmpty());
        $configuration->setUseOnlyEmpty(false);
        $this->assertFalse($configuration->isUseOnlyEmpty());
        $configuration->setApplyToCategories(true);
        $this->assertTrue($configuration->isApplyToCategories());
        $configuration->setApplyToCategories(false);
        $this->assertFalse($configuration->isApplyToCategories());
        $configuration->setApplyToCmsBlocks(true);
        $this->assertTrue($configuration->isApplyToCmsBlocks());
        $configuration->setApplyToCmsBlocks(false);
        $this->assertFalse($configuration->isApplyToCmsBlocks());
        $configuration->setApplyToCmsPages(true);
        $this->assertTrue($configuration->isApplyToCmsPages());
        $configuration->setApplyToCmsPages(false);
        $this->assertFalse($configuration->isApplyToCmsPages());
        $configuration->setApplyToCustomers(true);
        $this->assertTrue($configuration->isApplyToCustomers());
        $configuration->setApplyToCustomers(false);
        $this->assertFalse($configuration->isApplyToCustomers());
        $configuration->setApplyToProducts(true);
        $this->assertTrue($configuration->isApplyToProducts());
        $configuration->setApplyToProducts(false);
        $this->assertFalse($configuration->isApplyToProducts());
        $configuration->setApplyToUsers(true);
        $this->assertTrue($configuration->isApplyToUsers());
        $configuration->setApplyToUsers(false);
        $this->assertFalse($configuration->isApplyToUsers());

        // --- Options
        $configuration->setDummyContentText('OPTION_DUMMY_CONTENT_TEXT');
        $this->assertEquals('OPTION_DUMMY_CONTENT_TEXT', $configuration->getDummyContentText());
        $configuration->setDummyPassword('OPTION_DUMMY_PASSWORD');
        $this->assertEquals('OPTION_DUMMY_PASSWORD', $configuration->getDummyPassword());
        $configuration->setDummyContentEmail('OPTION_DUMMY_CONTENT_EMAIL');
        $this->assertEquals('OPTION_DUMMY_CONTENT_EMAIL', $configuration->getDummyContentEmail());
        $configuration->setDummyContentUrl('OPTION_DUMMY_CONTENT_URL');
        $this->assertEquals('OPTION_DUMMY_CONTENT_URL', $configuration->getDummyContentUrl());
        $configuration->setDummyPhoneNumber('OPTION_DUMMY_CONTENT_PHONE');
        $this->assertEquals('OPTION_DUMMY_CONTENT_PHONE', $configuration->getDummyPhoneNumber());
        $configuration->setDummyImagePath('OPTION_DUMMY_CONTENT_IMAGE');
        $this->assertEquals('OPTION_DUMMY_CONTENT_IMAGE', $configuration->getDummyImagePath());

        // --- Extended data
        $extendedData = [
            'foo' => 'bar'
        ];
        $configuration->setExtendedData($extendedData);
        $this->assertEquals($extendedData, $configuration->getExtendedData());
    }
}
