<?php
declare(strict_types=1);

namespace Vendic\RegenUrlKeys\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Store\Model\App\Emulation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vendic\RegenUrlKeys\Model\GenerateUrlKey;

/**
 * @author Tjitse (Vendic)
 * Created on 27-08-18 08:45
 */
class FixUrlKeys extends Command
{
    /**
     * @var int
     */
    protected $errors = 0;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;
    /**
     * @var FilterGroup
     */
    protected $filterGroup;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var GenerateUrlKey
     */
    protected $generateUrlKey;
    /**
     * @var State
     */
    protected $state;
    /**
     * @var Emulation
     */
    protected $emulation;

    public function __construct(
        Emulation $emulation,
        State $state,
        GenerateUrlKey $generateUrlKey,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaInterface $searchCriteria,
        FilterGroup $filterGroup,
        Filter $filter
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filterGroup = $filterGroup;
        $this->filter = $filter;
        $this->generateUrlKey = $generateUrlKey;
        $this->state = $state;
        $this->emulation = $emulation;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('regenerate:product:urlkeys')
            ->setDescription('Regenerate all missing product url keys');
        return parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $this->emulation->startEnvironmentEmulation(0, Area::AREA_ADMINHTML);

        $output->writeln('<info>Regenerating missing product url keys...</info>');
        $collection = $this->searchForProductsWithoutUrlKey();

        $this->noProductsFoundGuard($collection, $output);

        $output->writeln("<info>Found {$collection->getTotalCount()} products</info>");
        $progressBar = new ProgressBar($output, $collection->getTotalCount());
        $progressBar->start();

        foreach ($collection->getItems() as $product) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $newUrlKey = $this->generateUrlKey->generateUrlKey($product);
            $product->setCustomAttribute('url_key', $newUrlKey);

            try {
                $product->save();
            } catch (\Exception $e) {
                $output->writeln(" <error>{$product->getSku()} : {$e->getMessage()}</error>");
                $this->incrementErrors();
            }
            $progressBar->advance();
        }

        $progressBar->finish();

        if ($this->errors >= 1) {
            $output->writeln("\n<info>Failed regenerating url keys for {$this->errors} products</info>");
        }
        $output->writeln('<info>Done!</info>');
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function searchForProductsWithoutUrlKey()
    {
        $filter = $this->filter
            ->setField('url_key')
            ->setValue(null)
            ->setConditionType('like');

        $filterGroup = $this->filterGroup->setFilters([$filter]);
        $searchCriteria = $this->searchCriteria->setFilterGroups([$filterGroup]);

        $searchResult = $this->productRepository->getList($searchCriteria);

        return $searchResult;
    }

    protected function noProductsFoundGuard(\Magento\Framework\Api\SearchResults $collection, OutputInterface $output)
    {
        if ($collection->getTotalCount() === 0) {
            $output->writeln('<error>No producs found</error>');
            exit;
        }
    }

    protected function incrementErrors(): void
    {
        $this->errors = $this->errors + 1;
    }
}
