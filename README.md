# PrestaShop Elasticseach module

[![Join the chat at https://gitter.im/DonatasL/PrestaShop-ElasticSearch](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/DonatasL/PrestaShop-ElasticSearch?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

> Elasticsearch® module for PrestaShop that makes search and filter significantly faster.

## Requirements

* PHP >= 5.4.0 for Elasticsearch 2 or PHP >= 5.6.6 for Elasticsearch 5
* ext-curl: the Libcurl extension for PHP
* PrestaShop 1.6.1.x
* Composer (if you want to switch to Elasticsearch 5)
* Elasticsearch® service. See installation and configuration for Elasticsearch 2
[here](https://www.elastic.co/guide/en/elasticsearch/reference/2.4/_installation.html) or Elasticsearch 5 [here](https://www.elastic.co/guide/en/elasticsearch/reference/5.0/_installation.html).

## Installation

1. Download module's zip
2. Extract .zip and rename folder name to "brad"
3. Switch to Elasticsearch 5 (OPTIONAL)  
    Module comes with Elasticsearch 2 library ready for use, if you want to switch to Elasticsearch 5, you have to manually     install it. After step *2* `cd` into "brad" directory and run:
    
    ```
    composer require elasticsearch/elasticsearch ~5.0
    ```  
    
    After successfully installing Elasticsearch 5 library continue with module installation.
4. Compress the renamed folder into a zip archive
5. Log into your PrestaShop's back office
6. Navigate to "Modules" tab
7. Click "Add a new module"
8. Upload module zip file
9. Click "Install" when "BRAD" appears in modules list

## Configuration

### Back office

##### Products indexation:
* Reindex all products;
* Reindex missing products;
* Reindex prices;
* See PrestaShop products count and how many of all products are indexed.

##### Change module settings:
* Enable / Disable search;
* Enable / Disable fuzzy search;
* Enable / Disable instant search;
* Enable / Disable dynamic search;
* Set instant search results count;
* Set minimum symbols count in search input from which search is started;
* Set host of Elasticsearch® service;
* Enable / Disable filtering;
* Hide filters with no matching products;
* Display number of matching products.
    
##### Change advanced module settings:
* Set bulk request size;
* Set number of shards;
* Set number of replicas;
* Set refresh interval;

##### Filters
* Filter creation;
* Choose filter type;
* Choose filter style.
 
##### Filter templates
* Create filter templates;
* Assign filters to templates;
* Order filters in template
 
### Front office

* Search for products by typing their names into search input;
* See instant results list;
* Select product from search results list;
* See search results in search page;
* Manage view of search results (list / grid, pagination, sorting);
* Filter category products by chosen criteria;
* Select product from filter results list.

## Support
 
If you have any questions do not hesitate to ask. We can also develop custom modules for you or make some core modifications for your PrestaShop web store if needed.
 
Our company is dedicated to your satisfaction and is always open to your feedback. If you have an issue with our module, please contact us at **[help@invertus.eu](mailto:help@invertus.eu?subject=Color%20Picker%20in%20products%20list%20Simple%20support)** and we'll do our best to help you.
 
We are always happy when we can help our customers!

## About Invertus

Invertus offers flexible and scalable solutions for PrestaShop eCommerce platform. Our PrestaShop solutions are designed to help businesses grow and succeed online. We are proud we delivered top quality PrestaShop addons like Advanced Auction, Advanced Review Premium, Advanced news, Advanced Related Products and others. We also provide complete eCommerce solutions that include analysis, prototyping, design, development and support after the project is finished. Fast and superior support system helps our customers to get fast and professional help. Our motto is "We are always happy if we can help our customers."

