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
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CategoriesObserver implements ObserverInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * CategoriesObserver constructor.
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->setup = $setup;
    }


    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        if (!$configuration->isApplyToCategories()) {
            return;
        }

        $db = $this->setup->getConnection()->startSetup();
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->load();
        foreach ($categoryCollection->getItems() as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $this->updateData($db, $configuration, $category);
        }
        $db->endSetup();
    }

    /**
     * @param AdapterInterface $db
     * @param Configuration $configuration
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category
     * @throws \Zend_Db_Statement_Exception
     */
    protected function updateData(AdapterInterface $db, Configuration $configuration, \Magento\Catalog\Model\Category $category)
    {
        $this->updateAttributeByQuery($db, $category, 'description', $configuration->getDummyContentText());
        $this->updateAttributeByQuery($db, $category, 'meta_title', $configuration->getDummyContentText());
        $this->updateAttributeByQuery($db, $category, 'meta_keyword', $configuration->getDummyContentText());
        $this->updateAttributeByQuery($db, $category, 'meta_description', $configuration->getDummyContentText());

        return $category;
    }

    /**
     * @param AdapterInterface $db
     * @param \Magento\Catalog\Model\Category $category
     * @param string $field
     * @param string $value
     * @throws \Zend_Db_Statement_Exception
     * @TODO Create entries if they do not exist yet
     * @TODO Respect backend type of attribute (text, varchar, ...)
     */
    protected function updateAttributeByQuery(AdapterInterface $db, \Magento\Catalog\Model\Category $category, $field, $value)
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
            $db->getTableName('catalog_category_entity_text'),
            $db->getTableName('eav_attribute'),
            $db->getTableName('eav_entity_type')
        );
        $stmt = $db->query($query, [
            ':entityid' => $category->getEntityId(),
            ':field' => $field,
            ':value' => $value,
            ':code' => 'catalog_category'
        ]);
        $stmt->execute();
    }
}
