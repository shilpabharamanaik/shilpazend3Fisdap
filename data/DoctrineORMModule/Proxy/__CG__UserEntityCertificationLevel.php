<?php

namespace DoctrineORMModule\Proxy\__CG__\User\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class CertificationLevel extends \User\Entity\CertificationLevel implements \Doctrine\ORM\Proxy\Proxy
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
            return ['__isInitialized__', 'description', 'abbreviation', 'profession', 'configuration_blacklist', 'bit_value', 'display_order', 'default_program_length_days', 'id', 'name', 'useDNADFlag', 'entityRepository'];
        }

        return ['__isInitialized__', 'description', 'abbreviation', 'profession', 'configuration_blacklist', 'bit_value', 'display_order', 'default_program_length_days', 'id', 'name', 'useDNADFlag', 'entityRepository'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (CertificationLevel $proxy) {
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
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
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
    public function getProfession()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getProfession', []);

        return parent::getProfession();
    }

    /**
     * {@inheritDoc}
     */
    public function setProfession(\User\Entity\Profession $profession)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setProfession', [$profession]);

        return parent::setProfession($profession);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultProgramLengthDays()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDefaultProgramLengthDays', []);

        return parent::getDefaultProgramLengthDays();
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultProgramLengthDays($default_program_length_days)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDefaultProgramLengthDays', [$default_program_length_days]);

        return parent::setDefaultProgramLengthDays($default_program_length_days);
    }

    /**
     * {@inheritDoc}
     */
    public function getColumn($column)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getColumn', [$column]);

        return parent::getColumn($column);
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', []);

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setName', [$name]);

        return parent::setName($name);
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