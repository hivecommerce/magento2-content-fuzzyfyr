<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\EntityManager\EventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AllInData\ContentFuzzyfyr\Model\Configuration;
use AllInData\ContentFuzzyfyr\Model\ConfigurationFactory;

/**
 * Class FuzzyfyrCommand
 * @package AllInData\ContentFuzzyfyr\Console\Command
 */
class FuzzyfyrCommand extends Command
{
    /**
     * Codes
     */
    const SUCCESS = 0;
    const ERROR_PRODUCTION_MODE = 127;
    const EVENT_NAME = 'aid_content_fuzzyfyr_event';

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
    const OPTION_DUMMY_CONTENT_EMAIL = 'dummy-content-email';
    const OPTION_DUMMY_CONTENT_URL = 'dummy-content-url';
    const OPTION_DUMMY_CONTENT_PHONE = 'dummy-content-phone';

    /**
     * Defaults
     */
    const DEFAULT_DUMMY_CONTENT_TEXT = 'Lorem ipsum.';
    const DEFAULT_DUMMY_CONTENT_EMAIL = 'lorem.ipsum.%1$s@test.localhost';
    const DEFAULT_DUMMY_CONTENT_URL = 'https://lor.emips.um/foo/bar/';
    const DEFAULT_DUMMY_CONTENT_PHONE = '+49 (0) 600 987 654 32';

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
     * FuzzyfyrCommand constructor.
     * @param State $state
     * @param EventManager $eventManager
     * @param ConfigurationFactory $configurationFactory
     */
    public function __construct(State $state, EventManager $eventManager, ConfigurationFactory $configurationFactory)
    {
        $this->state = $state;
        $this->eventManager = $eventManager;
        $this->configurationFactory = $configurationFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('aid:content:fuzzyfyr')
            ->setDescription('Content fuzzyfyr command by All.In Data GmbH')
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
                )
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check on environment
        $output->writeln(sprintf('You are running in %s mode', $this->state->getMode()));
        if (\Magento\Framework\App\State::MODE_PRODUCTION === $this->state->getMode()) {
            // prevent from being executed in production mode
            // this may mess up to much if it would run without any safety checks
            $output->writeln('You really should not use this command in production mode.');
            $output->writeln('If it is really what you want, switch to default or developer mode first.');
            return self::ERROR_PRODUCTION_MODE;
        }

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
        $this->eventManager->dispatch(self::EVENT_NAME, ['configuration' => $configuration]);

        $output->writeln('Finished content fuzzyfy');

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
        $configuration->setDummyContentEmail($input->getOption(self::OPTION_DUMMY_CONTENT_EMAIL));
        $configuration->setDummyContentUrl($input->getOption(self::OPTION_DUMMY_CONTENT_URL));
        $configuration->setDummyPhoneNumber($input->getOption(self::OPTION_DUMMY_CONTENT_PHONE));

        return $configuration;
    }
}
