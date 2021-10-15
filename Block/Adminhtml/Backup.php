<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Block\Adminhtml;

use Magento\Backend\Block\AbstractBlock;

/**
 * Class Backup
 * @package HiveCommerce\ContentFuzzyfyr\Block\Adminhtml
 */
class Backup extends \Magento\Backup\Block\Adminhtml\Backup
{
    /**
     * @return AbstractBlock|void
     * @codeCoverageIgnoreStart
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getToolbar()->addChild(
            'createGdprConformDatabaseBackupButton',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('GDPR conform Database Backup (Content Fuzzyfyr)'),
                'onclick' => "return backup.backup('" . \HiveCommerce\ContentFuzzyfyr\Model\Backup\Factory::TYPE_GDPR_DB . "')",
                'class' => 'task primary aid-content-export'
            ]
        );
    }
    //@codeCoverageIgnoreEnd
}
