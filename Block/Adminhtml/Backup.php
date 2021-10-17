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

namespace HiveCommerce\ContentFuzzyfyr\Block\Adminhtml;

use HiveCommerce\ContentFuzzyfyr\Model\Backup\Factory;
use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\View\Element\BlockInterface;

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

        $toolbar = $this->getToolbar();
        if ($toolbar instanceof BlockInterface) {
            /** @var \Magento\Framework\View\Element\AbstractBlock $toolbar */
            $toolbar->addChild(
                'createGdprConformDatabaseBackupButton',
                Button::class,
                [
                    'label' => __('GDPR conform Database Backup (Content Fuzzyfyr)'),
                    'onclick' => "return backup.backup('" . Factory::TYPE_GDPR_DB . "')",
                    'class' => 'task primary aid-content-export'
                ]
            );
        }
    }
    //@codeCoverageIgnoreEnd
}
