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
     * @var array After parsing url selected filters are stored here
     */
    private $selectedFilters = [];

    /**
     * @var string Selected filters query string
     */
    private $queryString = '';

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
     */
    public function parse()
    {
        foreach ($_GET as $filterName => $filterValue) {
            if (!$this->checkIfFilter($filterName)) {
                continue;
            }

            $this->addQueryStringParam($filterName, $filterValue);
            $values = explode('-', $filterValue);

            foreach ($values as $value) {

                if (false !== strpos($value, ':')) {

                    list($min, $max) = explode(':', $value);

                    $this->selectedFilters[$filterName][] = [
                        'min_value' => $min,
                        'max_value' => $max,
                    ];

                    continue;
                }

                $this->selectedFilters[$filterName][] = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function getSelectedFilters()
    {
        return $this->selectedFilters;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Get available filters
     *
     * @param string $filterName
     *
     * @return bool
     */
    protected function checkIfFilter($filterName)
    {
        $staticFilterNames = [
            'category',
            'price',
            'quantity',
            'manufacturer',
            'weight',
        ];

        if (in_array($filterName, $staticFilterNames) ||
            0 === strpos($filterName, 'feature_') ||
            0 === strpos($filterName, 'attribute_group_')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param string $value
     */
    protected function addQueryStringParam($key, $value)
    {
        if (empty($this->queryString)) {
            $this->queryString = sprintf('%s=%s', $key, $value);
            return;
        }

        $this->queryString .= sprintf('&%s=%s', $key, $value);
    }
}
