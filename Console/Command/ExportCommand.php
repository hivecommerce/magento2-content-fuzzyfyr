<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Console\Command;

use HiveCommerce\ContentFuzzyfyr\Handler\BackupHandler;
use Magento\Framework\App\State;
use Magento\Framework\EntityManager\EventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Model\ConfigurationFactory;

/**
 * Class ExportCommand
 * @package HiveCommerce\ContentFuzzyfyr\Console\Command
 */
class ExportCommand extends Command
{
    /**
     * Codes
     */
    const SUCCESS = 0;
    const ERROR_EXPORT_FAILED = 127;
    const EVENT_NAME = 'aid_content_export_event';

    /**
     * Flags
     */
    const FLAG_ONLY_EMPTY = 'only-empty';
    const FLAG_CATEGORIES = 'categories';
    const FLAG_CMS_BLOCKS = 'cms-blocks';
    const FLAG_CMS_PAGES = 'cms-pages';
    const FLAG_CUSTOMERS = 'customers';
    const FLAG_PRODUCTS = 'products';
    const FLAG_USERS = 'users';

    /**
     * Options
     */
    const OPTION_DUMMY_CONTENT_TEXT = 'dummy-content-text';
    const OPTION_DUMMY_CONTENT_PASSWORD = 'dummy-content-password';
    const OPTION_DUMMY_CONTENT_EMAIL = 'dummy-content-email';
    const OPTION_DUMMY_CONTENT_URL = 'dummy-content-url';
    const OPTION_DUMMY_CONTENT_PHONE = 'dummy-content-phone';
    const OPTION_DUMP_OUTPUT = 'backup-output';

    /**
     * Defaults
     */
    const DEFAULT_DUMMY_CONTENT_TEXT = 'Lorem ipsum.';
    const DEFAULT_DUMMY_CONTENT_PASSWORD = 'lorem42';
    const DEFAULT_DUMMY_CONTENT_EMAIL = 'lorem.ipsum.%1$s@test.localhost';
    const DEFAULT_DUMMY_CONTENT_URL = 'https://lor.emips.um/foo/bar/';
    const DEFAULT_DUMMY_CONTENT_PHONE = '+49 (0) 600 987 654 32';
    const DEFAULT_DUMP_OUTPUT = './var/backups/';

    /**
     * @var State
     */
    private $state;
    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;
    /**
     * @var BackupHandler
     */
    private $backupHandler;

    /**
     * ExportCommand constructor.
     * @param State $state
     * @param EventManager $eventManager
     * @param ConfigurationFactory $configurationFactory
     * @param BackupHandler $backupHandler
     */
    public function __construct(
        State $state,
        EventManager $eventManager,
        ConfigurationFactory $configurationFactory,
        BackupHandler $backupHandler
    ) {
        $this->state = $state;
        $this->eventManager = $eventManager;
        $this->configurationFactory = $configurationFactory;
        $this->backupHandler = $backupHandler;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('developer:content:export')
            ->setDescription('Export fuzzyfied content command')
            ->setDefinition([
                new InputOption(
                    self::FLAG_ONLY_EMPTY,
                    null,
                    InputOption::VALUE_NONE,
                    'Use dummy content only if the original data is equal to empty'
                ),
                new InputOption(
                    self::FLAG_CATEGORIES,
                    null,
                    InputOption::VALUE_NONE,
                    'Apply dummy content to categories (content, meta description)'
                ),
                new InputOption(
                    self::FLAG_CMS_BLOCKS,
                    null,
                    InputOption::VALUE_NONE,
                    'Apply dummy content to CMS Blocks (content)'
                ),
                new InputOption(
                    self::FLAG_CMS_PAGES,
                    null,
                    InputOption::VALUE_NONE,
                    'Apply dummy content to CMS Pages (content, meta description)'
                ),
                new InputOption(
                    self::FLAG_CUSTOMERS,
                    null,
                    InputOption::VALUE_NONE,
                    'Apply dummy content to customers (Last name, address, email)'
                ),
                new InputOption(
                    self::FLAG_PRODUCTS,
                    null,
                    InputOption::VALUE_NONE,
                    'Apply dummy content to products (description)'
                ),
                new InputOption(
                    self::FLAG_USERS,
                    null,
                    InputOption::VALUE_NONE,
                    'Apply dummy content to users (Last name, email)'
                ),
                new InputOption(
                    self::OPTION_DUMMY_CONTENT_TEXT,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Used as dummy text content.',
                    self::DEFAULT_DUMMY_CONTENT_TEXT
                ),
                new InputOption(
                    self::OPTION_DUMMY_CONTENT_PASSWORD,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Used as dummy password.',
                    self::DEFAULT_DUMMY_CONTENT_PASSWORD
                ),
                new InputOption(
                    self::OPTION_DUMMY_CONTENT_EMAIL,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Used as dummy email content.',
                    self::DEFAULT_DUMMY_CONTENT_EMAIL
                ),
                new InputOption(
                    self::OPTION_DUMMY_CONTENT_URL,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Used as dummy URL.',
                    self::DEFAULT_DUMMY_CONTENT_URL
                ),
                new InputOption(
                    self::OPTION_DUMMY_CONTENT_PHONE,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Used as dummy phone number.',
                    self::DEFAULT_DUMMY_CONTENT_PHONE
                ),
                new InputOption(
                    self::OPTION_DUMP_OUTPUT,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Path where the database dump is placed.',
                    self::DEFAULT_DUMP_OUTPUT
                )
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set area code
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        /*
         * Configuration
         */
        $configuration = $this->loadConfiguration(
            $this->configurationFactory->create(),
            $input
        );

        /*
         * Processing
         */
        $output->writeln('Start content export, this may take a moment...');
        $this->backupHandler->beginTransaction();

        try {
            $this->eventManager->dispatch(self::EVENT_NAME, [
                'configuration' => $configuration
            ]);

            $this->backupHandler->run(
                $output,
                $input->getOption(self::OPTION_DUMP_OUTPUT)
            );
        } catch (\Exception $e) {
            $this->backupHandler->endTransaction();
            $output->writeln('Failed to export fuzzyfied content. Check your logs for more information.');
            return self::ERROR_EXPORT_FAILED;
        }

        $this->backupHandler->endTransaction();
        $output->writeln('Finished content export');

        return self::SUCCESS;
    }

    /**
     * @param Configuration $configuration
     * @param InputInterface $input
     * @return Configuration
     */
    protected function loadConfiguration(Configuration $configuration, InputInterface $input)
    {
        // --- Flags
        $configuration->setUseOnlyEmpty($input->getOption(self::FLAG_ONLY_EMPTY));
        $configuration->setApplyToCategories($input->getOption(self::FLAG_CATEGORIES));
        $configuration->setApplyToCmsBlocks($input->getOption(self::FLAG_CMS_BLOCKS));
        $configuration->setApplyToCmsPages($input->getOption(self::FLAG_CMS_PAGES));
        $configuration->setApplyToCustomers($input->getOption(self::FLAG_CUSTOMERS));
        $configuration->setApplyToProducts($input->getOption(self::FLAG_PRODUCTS));
        $configuration->setApplyToUsers($input->getOption(self::FLAG_USERS));

        // --- Options
        $configuration->setDummyContentText($input->getOption(self::OPTION_DUMMY_CONTENT_TEXT));
        $configuration->setDummyPassword($input->getOption(self::OPTION_DUMMY_CONTENT_PASSWORD));
        $configuration->setDummyContentEmail($input->getOption(self::OPTION_DUMMY_CONTENT_EMAIL));
        $configuration->setDummyContentUrl($input->getOption(self::OPTION_DUMMY_CONTENT_URL));
        $configuration->setDummyPhoneNumber($input->getOption(self::OPTION_DUMMY_CONTENT_PHONE));

        return $configuration;
    }
}
