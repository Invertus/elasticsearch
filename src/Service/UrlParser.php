<?php

namespace Invertus\Brad\Service;
use Context;
use Core_Business_ConfigurationInterface;

/**
 * Class UrlParser
 *
 * @package Invertus\Brad\Service
 */
class UrlParser
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Core_Business_ConfigurationInterface
     */
    private $configuration;

    /**
     * UrlParser constructor.
     *
     * @param Context $context
     * @param Core_Business_ConfigurationInterface $configuration
     */
    public function __construct(Context $context, Core_Business_ConfigurationInterface $configuration)
    {
        $this->context = $context;
        $this->configuration = $configuration;
    }

    /**
     * Parse url
     *
     * @return array
     */
    public function parse()
    {
        //@todo: implement url parser
    }
}
