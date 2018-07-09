<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Observer;

use AllInData\ContentFuzzyfyr\Model\Configuration;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class ProductsObserver implements ObserverInterface
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * ProductsObserver constructor.
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->setup = $setup;
    }


    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        if (!$configuration->isApplyToProducts()) {
            return;
        }

        $db = $this->setup->getConnection()->startSetup();
        /** @var ProductCollection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->load();
        foreach ($productCollection->getItems() as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $this->updateData($db, $configuration, $product);
        }
        $db->endSetup();
    }

    /**
     * @param AdapterInterface $db
     * @param Configuration $configuration
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @throws \Zend_Db_Statement_Exception
     */
    protected function updateData(AdapterInterface $db, Configuration $configuration, \Magento\Catalog\Model\Product $product)
    {
        $this->updateAttributeByQuery($db, $product, 'description', $configuration->getDummyContentText());
        $this->updateAttributeByQuery($db, $product, 'short_description', $configuration->getDummyContentText());
        $this->updateAttributeByQuery($db, $product, 'meta_title', $configuration->getDummyContentText());
        $this->updateAttributeByQuery($db, $product, 'meta_keyword', $configuration->getDummyContentText());
        $this->updateAttributeByQuery($db, $product, 'meta_description', $configuration->getDummyContentText());

        return $product;
    }

    /**
     * @param AdapterInterface $db
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @param string $value
     * @throws \Zend_Db_Statement_Exception
     * @TODO Create entries if they do not exist yet
     * @TODO Respect backend type of attribute (text, varchar, ...)
     */
    protected function updateAttributeByQuery(AdapterInterface $db, \Magento\Catalog\Model\Product $product, $field, $value)
    {
        $query = 'UPDATE
                %1$s AS e
            LEFT JOIN %2$s AS ea ON attribute_code = :field
            LEFT JOIN %3$s AS eet ON entity_type_code = :code
            SET
                e.value = :value
            WHERE
              eet.entity_type_id = ea.entity_type_id AND 
              e.entity_id = :entityid';

        $query = sprintf(
            $query,
            $db->getTableName('catalog_product_entity_text'),
            $db->getTableName('eav_attribute'),
            $db->getTableName('eav_entity_type')
        );
        $stmt = $db->query($query, [
            ':entityid' => $product->getEntityId(),
            ':field' => $field,
            ':value' => $value,
            ':code' => 'catalog_product'
        ]);
        $stmt->execute();
    }
}
