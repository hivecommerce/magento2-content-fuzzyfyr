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
        self::assertTrue($configuration->isUseOnlyEmpty());
        $configuration->setUseOnlyEmpty(false);
        self::assertFalse($configuration->isUseOnlyEmpty());
        $configuration->setApplyToCategories(true);
        self::assertTrue($configuration->isApplyToCategories());
        $configuration->setApplyToCategories(false);
        self::assertFalse($configuration->isApplyToCategories());
        $configuration->setApplyToCmsBlocks(true);
        self::assertTrue($configuration->isApplyToCmsBlocks());
        $configuration->setApplyToCmsBlocks(false);
        self::assertFalse($configuration->isApplyToCmsBlocks());
        $configuration->setApplyToCmsPages(true);
        self::assertTrue($configuration->isApplyToCmsPages());
        $configuration->setApplyToCmsPages(false);
        self::assertFalse($configuration->isApplyToCmsPages());
        $configuration->setApplyToCustomers(true);
        self::assertTrue($configuration->isApplyToCustomers());
        $configuration->setApplyToCustomers(false);
        self::assertFalse($configuration->isApplyToCustomers());
        $configuration->setApplyToProducts(true);
        self::assertTrue($configuration->isApplyToProducts());
        $configuration->setApplyToProducts(false);
        self::assertFalse($configuration->isApplyToProducts());
        $configuration->setApplyToUsers(true);
        self::assertTrue($configuration->isApplyToUsers());
        $configuration->setApplyToUsers(false);
        self::assertFalse($configuration->isApplyToUsers());

        // --- Options
        $configuration->setDummyContentText('OPTION_DUMMY_CONTENT_TEXT');
        self::assertEquals('OPTION_DUMMY_CONTENT_TEXT', $configuration->getDummyContentText());
        $configuration->setDummyPassword('OPTION_DUMMY_PASSWORD');
        self::assertEquals('OPTION_DUMMY_PASSWORD', $configuration->getDummyPassword());
        $configuration->setDummyContentEmail('OPTION_DUMMY_CONTENT_EMAIL');
        self::assertEquals('OPTION_DUMMY_CONTENT_EMAIL', $configuration->getDummyContentEmail());
        $configuration->setDummyContentUrl('OPTION_DUMMY_CONTENT_URL');
        self::assertEquals('OPTION_DUMMY_CONTENT_URL', $configuration->getDummyContentUrl());
        $configuration->setDummyPhoneNumber('OPTION_DUMMY_CONTENT_PHONE');
        self::assertEquals('OPTION_DUMMY_CONTENT_PHONE', $configuration->getDummyPhoneNumber());
        $configuration->setDummyImagePath('OPTION_DUMMY_CONTENT_IMAGE');
        self::assertEquals('OPTION_DUMMY_CONTENT_IMAGE', $configuration->getDummyImagePath());

        // --- Extended data
        $extendedData = [
            'foo' => 'bar'
        ];
        $configuration->setExtendedData($extendedData);
        self::assertEquals($extendedData, $configuration->getExtendedData());
    }
}
