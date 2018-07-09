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
    /*
     * Flags
     */
    const ENTITY_TYPE_CODE = 'catalog_product';
    const ENTITY_FIELD_TYPE_TEXT = 'text';
    const ENTITY_FIELD_TYPE_VARCHAR = 'varchar';

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
        $this->pushData($db, $configuration, $product, 'description', $configuration->getDummyContentText());
        $this->pushData($db, $configuration, $product, 'short_description', $configuration->getDummyContentText());
        $this->pushData($db, $configuration, $product, 'meta_title', $configuration->getDummyContentText());
        $this->pushData($db, $configuration, $product, 'meta_keyword', $configuration->getDummyContentText());
        $this->pushData($db, $configuration, $product, 'meta_description', $configuration->getDummyContentText());

        return $product;
    }

    /**
     * @param AdapterInterface $db
     * @param Configuration $configuration
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @param string $value
     * @throws \Zend_Db_Statement_Exception
     */
    protected function pushData(AdapterInterface $db, Configuration $configuration, \Magento\Catalog\Model\Product $product, $field, $value)
    {
        // --- Field type TEXT
        if ($this->hasAttribute($db, $product, self::ENTITY_FIELD_TYPE_TEXT, $field)) {
            $this->updateAttributeByQuery($db, $product, self::ENTITY_FIELD_TYPE_TEXT, $field, $value, $configuration->isUseOnlyEmpty());
        } else {
            $this->insertAttributeByQuery($db, $product, self::ENTITY_FIELD_TYPE_TEXT, $field, $value);
        }

        // --- Field type VARCHAR
        if ($this->hasAttribute($db, $product, self::ENTITY_FIELD_TYPE_VARCHAR, $field)) {
            $this->updateAttributeByQuery($db, $product, self::ENTITY_FIELD_TYPE_VARCHAR, $field, $value, $configuration->isUseOnlyEmpty());
        } else {
            $this->insertAttributeByQuery($db, $product, self::ENTITY_FIELD_TYPE_VARCHAR, $field, $value);
        }
    }

    /**
     * @param AdapterInterface $db
     * @param \Magento\Catalog\Model\Product $product
     * @param string $fieldType
     * @param string $field
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    protected function hasAttribute(AdapterInterface $db, \Magento\Catalog\Model\Product $product, $fieldType, $field)
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
            $db->getTableName('catalog_product_entity_' . $fieldType)
        );
        $stmt = $db->query($query, [
            ':entityid' => $product->getEntityId(),
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
     * @param \Magento\Catalog\Model\Product $product
     * @param string $fieldType
     * @param string $field
     * @param string $value
     * @param boolean $useOnlyEmpty
     * @throws \Zend_Db_Statement_Exception
     */
    protected function updateAttributeByQuery(AdapterInterface $db, \Magento\Catalog\Model\Product $product, $fieldType, $field, $value, $useOnlyEmpty)
    {
        $query = 'UPDATE
                %1$s AS e
            SET
                e.value = :value
            WHERE
              e.attribute_id = :attributeid AND
              e.store_id = :storeid AND
              e.entity_id = :entityid';

        if ($useOnlyEmpty) {
            $query .= ' AND (e.value = "" OR e.value IS NULL)';
        }

        $queryText = sprintf(
            $query,
            $db->getTableName('catalog_product_entity_' . $fieldType)
        );
        $stmt = $db->query($queryText, [
            ':entityid' => $product->getEntityId(),
            ':attributeid' => $this->getAttributeId($db, $field),
            ':storeid' => 0,
            ':value' => $value
        ]);
        $stmt->execute();
    }

    /**
     * @param AdapterInterface $db
     * @param \Magento\Catalog\Model\Product $product
     * @param string $fieldType
     * @param string $field
     * @param string $value
     * @throws \Zend_Db_Statement_Exception
     */
    protected function insertAttributeByQuery(AdapterInterface $db, \Magento\Catalog\Model\Product $product, $fieldType, $field, $value)
    {
        $query = 'INSERT IGNORE INTO
                %1$s (`attribute_id`, `store_id`, `entity_id`, `value`)
            VALUES (:attributeid, :storeid, :entityid, :value)';

        $queryText = sprintf(
            $query,
            $db->getTableName('catalog_product_entity_' . $fieldType)
        );
        $stmt = $db->query($queryText, [
            ':entityid' => $product->getEntityId(),
            ':attributeid' => $this->getAttributeId($db, $field),
            ':storeid' => 0,
            ':value' => $value
        ]);
        $stmt->execute();
    }
}
