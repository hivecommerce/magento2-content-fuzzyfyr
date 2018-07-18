<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Handler;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class CategoryImageHandler
 * @package AllInData\ContentFuzzyfyr\Handler
 */
class CategoryImageHandler extends MediaFileHandler
{
    /*
     * Path
     */
    const MEDIA_MODULE_BASE_PATH = 'catalog/category';
}
