<?php

require_once __DIR__ . '/Interface.php';

abstract class FbPack_Plugin_Abstract implements FbPack_Plugin_Interface
{
	/**
     * @var integer
     */
    protected $enabled = 0;
	
	/**
     * @var Module
     */
    protected $module = null;
	
    /**
     * @var array
     */
    protected $errors = array();

	/**
     * @var Module $prestaModule
     */
    public function __construct($prestaModule)
    {
        if ($this->module === null) {
            $this->module = $prestaModule;
        }
    }
	
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return (Configuration::get(static::ENABLED) == 1) ? true : false;
    }

    public function getContentForHook()
    {
        return array();
    }
}
