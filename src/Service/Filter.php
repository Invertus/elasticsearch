<?php

namespace Invertus\Brad\Service;

use Context;
use Core_Foundation_Database_EntityManager;

/**
 * Class Filter
 *
 * @package Invertus\Brad\Service
 */
class Filter
{
    /**
     * @var Core_Foundation_Database_EntityManager
     */
    private $em;

    /**
     * @var Context
     */
    private $context;

    /**
     * Filter constructor.
     *
     * @param Context $context
     * @param Core_Foundation_Database_EntityManager $em
     */
    public function __construct(Context $context, Core_Foundation_Database_EntityManager $em)
    {
        $this->em = $em;
        $this->context = $context;
    }

    /**
     * Perform filtering
     *
     * @param array $filters
     */
    public function process(array $filters)
    {

    }
}
