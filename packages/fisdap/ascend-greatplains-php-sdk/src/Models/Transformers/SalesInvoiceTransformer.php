<?php namespace Fisdap\Ascend\Greatplains\Models\Transformers;

use Fisdap\Ascend\Greatplains\Contracts\Models\Transformers\SalesInvoiceTransformer as SalesInvoiceTransformerInterface;
use Fisdap\Ascend\Greatplains\Contracts\SalesInvoice;
use Fisdap\Ascend\Greatplains\Contracts\Support\Arrayable;
use Fisdap\Ascend\Greatplains\Contracts\Support\JsonSerializable;

/**
 * Class SalesInvoiceTransformer
 *
 * Transformation class for sales invoice
 *
 * @package Fisdap\Ascend\Greatplains\Models\Transformers
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class SalesInvoiceTransformer implements SalesInvoiceTransformerInterface, JsonSerializable, Arrayable
{
    /**
     * @var SalesInvoice
     */
    protected $salesInvoice;

    /**
     * Response back from the api
     *
     * @var mixed
     */
    protected $response;

    /**
     * Get the entity
     *
     * @return SalesInvoice
     */
    public function getEntity()
    {
        return $this->salesInvoice;
    }

    /**
     * Get the persistence location to store entity
     *
     * @return string
     */
    public function getPersistentLocation()
    {
        return "api/sales-invoices";
    }

    /**
     * Get persistent data string
     *
     * @return string
     */
    public function getPersistentData()
    {
        return $this->toJson();
    }

    /**
     * Set the response back from the api
     *
     * @param $data
     * @return $this
     */
    public function setResponse($data)
    {
        $this->response = $data;
        return $this;
    }

    /**
     * Set the sales invoice
     *
     * @param SalesInvoice $salesInvoice
     * @return $this
     */
    public function setSalesInvoice(SalesInvoice $salesInvoice)
    {
        $this->salesInvoice = $salesInvoice;
        return $this;
    }

    /**
     * Return object as array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            SalesInvoice::ID_FIELD          => $this->getEntity()->getId(),
            SalesInvoice::CUSTOMER_ID_FIELD => $this->getEntity()->getCustomerId(),
            SalesInvoice::BATCH_ID_FIELD    => $this->getEntity()->getBatchId(),
            SalesInvoice::DATE_FIELD        => $this->getEntity()->getDate(),
            SalesInvoice::LINES_FIELD       => $this->getEntity()->getLines()->toArray(),
            SalesInvoice::PAYMENTS_FIELD    => $this->getEntity()->getPayments()->toArray()
        ];
    }

    /**
     * Return json representation
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
