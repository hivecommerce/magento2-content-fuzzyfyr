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
    /*
     * Flags
     */
    const ENTITY_TYPE_CODE = 'catalog_category';
    const ENTITY_FIELD_TYPE_TEXT = 'text';
    const ENTITY_FIELD_TYPE_VARCHAR = 'varchar';

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
        $this->pushData($db, $category, 'description', $configuration->getDummyContentText());
        $this->pushData($db, $category, 'meta_title', $configuration->getDummyContentText());
        $this->pushData($db, $category, 'meta_keywords', $configuration->getDummyContentText());
        $this->pushData($db, $category, 'meta_description', $configuration->getDummyContentText());

        return $category;
    }

    /**
     * @param AdapterInterface $db
     * @param \Magento\Catalog\Model\Category $category
     * @param string $field
     * @param string $value
     * @throws \Zend_Db_Statement_Exception
     */
    protected function pushData(AdapterInterface $db, \Magento\Catalog\Model\Category $category, $field, $value)
    {
        // --- Field type TEXT
        if ($this->hasAttribute($db, $category, self::ENTITY_FIELD_TYPE_TEXT, $field)) {
            $this->updateAttributeByQuery($db, $category, self::ENTITY_FIELD_TYPE_TEXT, $field, $value);
        } else {
            $this->insertAttributeByQuery($db, $category, self::ENTITY_FIELD_TYPE_TEXT, $field, $value);
        }

        // --- Field type VARCHAR
        if ($this->hasAttribute($db, $category, self::ENTITY_FIELD_TYPE_VARCHAR, $field)) {
            $this->updateAttributeByQuery($db, $category, self::ENTITY_FIELD_TYPE_VARCHAR, $field, $value);
        } else {
            $this->insertAttributeByQuery($db, $category, self::ENTITY_FIELD_TYPE_VARCHAR, $field, $value);
        }
    }

    /**
     * @param AdapterInterface $db
     * @param \Magento\Catalog\Model\Category $category
     * @param string $fieldType
     * @param string $field
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    protected function hasAttribute(AdapterInterface $db, \Magento\Catalog\Model\Category $category, $fieldType, $field)
    {
        $query = 'SELECT e.value
            FROM
                %1$s AS e
            WHERE
              e.attribute_id = :attributeid AND
              e.store_id = :storeid AND
              e.entity_id = :entityid';

        $query = sprintf(
            $query,
            $db->getTableName('catalog_category_entity_' . $fieldType)
        );
        $stmt = $db->query($query, [
            ':entityid' => $category->getEntityId(),
            ':attributeid' => $this->getAttributeId($db, $field),
            ':storeid' => 0
        ]);

        if (!$stmt->execute()) {
            return false;
        }

        return !!$stmt->rowCount();
    }

    /**
     * @param AdapterInterface $db
     * @param string $field
     * @return string|bool FALSE if query fails
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getAttributeId(AdapterInterface $db, $field)
    {
        $query = 'SELECT ea.attribute_id
            FROM
                %1$s AS ea
            LEFT JOIN %2$s AS eet ON entity_type_code = :code
            WHERE
              eet.entity_type_id = ea.entity_type_id AND 
              ea.attribute_code = :field';

        $query = sprintf(
            $query,
            $db->getTableName('eav_attribute'),
            $db->getTableName('eav_entity_type')
        );
        $stmt = $db->query($query, [
            ':field' => $field,
            ':code' => self::ENTITY_TYPE_CODE
        ]);

        if (!$stmt->execute()) {
            return false;
        }

        $result = $stmt->fetch(\Zend_Db::FETCH_ASSOC);
        if (empty($result)) {
            return false;
        }

        return $result['attribute_id'] ?: false;
    }

    /**
     * @param AdapterInterface $db
     * @param \Magento\Catalog\Model\Category $category
     * @param string $fieldType
     * @param string $field
     * @param string $value
     * @throws \Zend_Db_Statement_Exception
     */
    protected function updateAttributeByQuery(AdapterInterface $db, \Magento\Catalog\Model\Category $category, $fieldType, $field, $value)
    {
        $query = 'UPDATE
                %1$s AS e
            SET
                e.value = :value
            WHERE
              e.attribute_id = :attributeid AND
              e.store_id = :storeid AND
              e.entity_id = :entityid';

        $queryText = sprintf(
            $query,
            $db->getTableName('catalog_category_entity_' . $fieldType)
        );
        $stmt = $db->query($queryText, [
            ':entityid' => $category->getEntityId(),
            ':attributeid' => $this->getAttributeId($db, $field),
            ':storeid' => 0,
            ':value' => $value
        ]);
        $stmt->execute();
    }

    /**
     * @param AdapterInterface $db
     * @param \Magento\Catalog\Model\Category $category
     * @param string $fieldType
     * @param string $field
     * @param string $value
     * @throws \Zend_Db_Statement_Exception
     */
    protected function insertAttributeByQuery(AdapterInterface $db, \Magento\Catalog\Model\Category $category, $fieldType, $field, $value)
    {
        $query = 'INSERT IGNORE INTO
                %1$s (`attribute_id`, `store_id`, `entity_id`, `value`)
            VALUES (:attributeid, :storeid, :entityid, :value)';

        $queryText = sprintf(
            $query,
            $db->getTableName('catalog_category_entity_' . $fieldType)
        );
        $stmt = $db->query($queryText, [
            ':entityid' => $category->getEntityId(),
            ':attributeid' => $this->getAttributeId($db, $field),
            ':storeid' => 0,
            ':value' => $value
        ]);
        $stmt->execute();
    }
}
