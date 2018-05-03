<?php namespace Fisdap\Ascend\Greatplains;

use Fisdap\Ascend\Greatplains\Contracts\Factories\CreateSalesInvoiceBuilder;
use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\SalesInvoiceTransformer;
use Fisdap\Ascend\Greatplains\Contracts\Repositories\SalesInvoiceRepository;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoice;

/**
 * Class CreateSalesInvoiceCommand
 *
 * Create and sales invoice, persist, and return
 *
 * @package Fisdap\Ascend\Greatplains
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class CreateSalesInvoiceCommand
{
    /**
     * @var SalesInvoiceRepository
     */
    private $salesInvoiceRepository;

    /**
     * @var SalesInvoiceTransformer
     */
    private $salesInvoiceTransformer;

    /**
     * CreateSalesInvoiceCommand constructor.
     *
     * @param SalesInvoiceRepository $salesInvoiceRepository
     * @param SalesInvoiceTransformer $salesInvoiceTransformer
     */
    public function __construct(
        SalesInvoiceRepository $salesInvoiceRepository,
        SalesInvoiceTransformer $salesInvoiceTransformer
    ) {
        $this->salesInvoiceRepository = $salesInvoiceRepository;
        $this->salesInvoiceTransformer = $salesInvoiceTransformer;
    }

    /**
     * Create a new sales invoice and save entity to data abstraction layer
     *
     * @param CreateSalesInvoiceBuilder $salesInvoiceBuilder
     * @return SalesInvoice
     */
    public function handle(CreateSalesInvoiceBuilder $salesInvoiceBuilder)
    {
        $salesInvoice = $salesInvoiceBuilder->buildSalesInvoiceEntity();

        $salesInvoiceTransformer = $this->salesInvoiceRepository->store(
            $this->salesInvoiceTransformer->setSalesInvoice($salesInvoice)
        );

        return $salesInvoice;
    }
}
