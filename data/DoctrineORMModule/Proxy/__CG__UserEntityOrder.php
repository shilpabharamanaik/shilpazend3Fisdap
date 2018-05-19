<?php

namespace DoctrineORMModule\Proxy\__CG__\User\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Order extends \User\Entity\Order implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = [];



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     */
    public function __get($name)
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__get', [$name]);

        return parent::__get($name);
    }

    /**
     * {@inheritDoc}
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__set', [$name, $value]);

        return parent::__set($name, $value);
    }



    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', 'id', 'user', 'program', 'name', 'program_name', 'email', 'phone', 'fax', 'address1', 'address2', 'address3', 'city', 'state', 'zip', 'country', 'invoice_delivery_method', 'payment_method', 'order_type', 'po_number', 'paypal_transaction_id', 'completed', 'total_cost', 'individual_purchase', 'upgrade_purchase', 'coupon', 'order_date', 'staff_free_order', 'accounting_processed', 'invoice_number', 'order_configurations', 'serial_numbers', 'order_transactions', 'useDNADFlag', 'entityRepository'];
        }

        return ['__isInitialized__', 'id', 'user', 'program', 'name', 'program_name', 'email', 'phone', 'fax', 'address1', 'address2', 'address3', 'city', 'state', 'zip', 'country', 'invoice_delivery_method', 'payment_method', 'order_type', 'po_number', 'paypal_transaction_id', 'completed', 'total_cost', 'individual_purchase', 'upgrade_purchase', 'coupon', 'order_date', 'staff_free_order', 'accounting_processed', 'invoice_number', 'order_configurations', 'serial_numbers', 'order_transactions', 'useDNADFlag', 'entityRepository'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Order $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);

        parent::__clone();
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function init()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'init', []);

        return parent::init();
    }

    /**
     * {@inheritDoc}
     */
    public function set_order_type($value)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'set_order_type', [$value]);

        return parent::set_order_type($value);
    }

    /**
     * {@inheritDoc}
     */
    public function set_payment_method($value)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'set_payment_method', [$value]);

        return parent::set_payment_method($value);
    }

    /**
     * {@inheritDoc}
     */
    public function set_invoice_delivery_method($value)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'set_invoice_delivery_method', [$value]);

        return parent::set_invoice_delivery_method($value);
    }

    /**
     * {@inheritDoc}
     */
    public function set_user($value)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'set_user', [$value]);

        return parent::set_user($value);
    }

    /**
     * {@inheritDoc}
     */
    public function set_program($value)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'set_program', [$value]);

        return parent::set_program($value);
    }

    /**
     * {@inheritDoc}
     */
    public function set_staff_free_order($value)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'set_staff_free_order', [$value]);

        return parent::set_staff_free_order($value);
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountingProcessed()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isAccountingProcessed', []);

        return parent::isAccountingProcessed();
    }

    /**
     * {@inheritDoc}
     */
    public function setAccountingProcessed($accounting_processed)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAccountingProcessed', [$accounting_processed]);

        return parent::setAccountingProcessed($accounting_processed);
    }

    /**
     * {@inheritDoc}
     */
    public function set_order_configurations($configs)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'set_order_configurations', [$configs]);

        return parent::set_order_configurations($configs);
    }

    /**
     * {@inheritDoc}
     */
    public function addOrderConfiguration(\User\Entity\OrderConfiguration $config)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addOrderConfiguration', [$config]);

        return parent::addOrderConfiguration($config);
    }

    /**
     * {@inheritDoc}
     */
    public function removeOrderConfiguration(\User\Entity\OrderConfiguration $config)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeOrderConfiguration', [$config]);

        return parent::removeOrderConfiguration($config);
    }

    /**
     * {@inheritDoc}
     */
    public function applyCoupon($couponId)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'applyCoupon', [$couponId]);

        return parent::applyCoupon($couponId);
    }

    /**
     * {@inheritDoc}
     */
    public function removeCoupon()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeCoupon', []);

        return parent::removeCoupon();
    }

    /**
     * {@inheritDoc}
     */
    public function getFullCost()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFullCost', []);

        return parent::getFullCost();
    }

    /**
     * {@inheritDoc}
     */
    public function getSubtotal()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSubtotal', []);

        return parent::getSubtotal();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalCost()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalCost', []);

        return parent::getTotalCost();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalQuantity()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalQuantity', []);

        return parent::getTotalQuantity();
    }

    /**
     * {@inheritDoc}
     */
    public function hasUnactivatedAccounts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasUnactivatedAccounts', []);

        return parent::hasUnactivatedAccounts();
    }

    /**
     * {@inheritDoc}
     */
    public function calculateTotal()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'calculateTotal', []);

        return parent::calculateTotal();
    }

    /**
     * {@inheritDoc}
     */
    public function addProductCode($code)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addProductCode', [$code]);

        return parent::addProductCode($code);
    }

    /**
     * {@inheritDoc}
     */
    public function getBillingAddress($includeOrdererDetails = false)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBillingAddress', [$includeOrdererDetails]);

        return parent::getBillingAddress($includeOrdererDetails);
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFirstName', []);

        return parent::getFirstName();
    }

    /**
     * {@inheritDoc}
     */
    public function getMiddleName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMiddleName', []);

        return parent::getMiddleName();
    }

    /**
     * {@inheritDoc}
     */
    public function getLastName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLastName', []);

        return parent::getLastName();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrdererName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOrdererName', []);

        return parent::getOrdererName();
    }

    /**
     * {@inheritDoc}
     */
    public function isViewable($user = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isViewable', [$user]);

        return parent::isViewable($user);
    }

    /**
     * {@inheritDoc}
     */
    public function isEditable($user = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isEditable', [$user]);

        return parent::isEditable($user);
    }

    /**
     * {@inheritDoc}
     */
    public function hasPilotTestingAccounts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasPilotTestingAccounts', []);

        return parent::hasPilotTestingAccounts();
    }

    /**
     * {@inheritDoc}
     */
    public function addPreceptorTrainingConfig($quantity)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addPreceptorTrainingConfig', [$quantity]);

        return parent::addPreceptorTrainingConfig($quantity);
    }

    /**
     * {@inheritDoc}
     */
    public function getBillingEmailArray()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBillingEmailArray', []);

        return parent::getBillingEmailArray();
    }

    /**
     * {@inheritDoc}
     */
    public function isFreeOrder()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isFreeOrder', []);

        return parent::isFreeOrder();
    }

    /**
     * {@inheritDoc}
     */
    public function process()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'process', []);

        return parent::process();
    }

    /**
     * {@inheritDoc}
     */
    public function emailInvoicePdf()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'emailInvoicePdf', []);

        return parent::emailInvoicePdf();
    }

    /**
     * {@inheritDoc}
     */
    public function processProductCodes()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'processProductCodes', []);

        return parent::processProductCodes();
    }

    /**
     * {@inheritDoc}
     */
    public function getTransactionErrors()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTransactionErrors', []);

        return parent::getTransactionErrors();
    }

    /**
     * {@inheritDoc}
     */
    public function generateInvoiceNumber()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'generateInvoiceNumber', []);

        return parent::generateInvoiceNumber();
    }

    /**
     * {@inheritDoc}
     */
    public function getInvoiceDueDate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getInvoiceDueDate', []);

        return parent::getInvoiceDueDate();
    }

    /**
     * {@inheritDoc}
     */
    public function getStaffOrderDetails($lineBreak = '<br>')
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStaffOrderDetails', [$lineBreak]);

        return parent::getStaffOrderDetails($lineBreak);
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociatedDiscounts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAssociatedDiscounts', []);

        return parent::getAssociatedDiscounts();
    }

    /**
     * {@inheritDoc}
     */
    public function isCreditCardPurchase()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isCreditCardPurchase', []);

        return parent::isCreditCardPurchase();
    }

    /**
     * {@inheritDoc}
     */
    public function setUUID($uuid = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUUID', [$uuid]);

        return parent::setUUID($uuid);
    }

    /**
     * {@inheritDoc}
     */
    public function getUUID()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUUID', []);

        return parent::getUUID();
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityRepository()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEntityRepository', []);

        return parent::getEntityRepository();
    }

    /**
     * {@inheritDoc}
     */
    public function save($flush = true)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'save', [$flush]);

        return parent::save($flush);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($flush = true)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'delete', [$flush]);

        return parent::delete($flush);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'flush', []);

        return parent::flush();
    }

    /**
     * {@inheritDoc}
     */
    public function isDatabaseField($field)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isDatabaseField', [$field]);

        return parent::isDatabaseField($field);
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldmap()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFieldmap', []);

        return parent::getFieldmap();
    }

    /**
     * {@inheritDoc}
     */
    public function isUsingDNAD()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isUsingDNAD', []);

        return parent::isUsingDNAD();
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toArray', []);

        return parent::toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryBuilder()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQueryBuilder', []);

        return parent::getQueryBuilder();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteGroup($group)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'deleteGroup', [$group]);

        return parent::deleteGroup($group);
    }

    /**
     * {@inheritDoc}
     */
    public function getShortName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShortName', []);

        return parent::getShortName();
    }

}
