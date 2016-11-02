<div class="panel " id="elasticsearch_actions">

    <div class="panel-heading">
        <i class="icon-cogs"></i>
        {l s='Elasticsearch actions' mod='brad'}
    </div>

    <div class="panel-body" style="padding: 0;">
        {if isset($elasticsearch_connection_ok)}
            <div class="alert alert-info">
                {l s='Elasticsearch version' mod='brad'}: <strong>{$elasticsearch_version|escape:'htmlall':'UTF-8'}</strong>
            </div>
            <div class="alert alert-info">
                {l s='Indexed products' mod='brad'}:
                <strong>{$indexed_products_count|escape:'htmlall':'UTF-8'} / {$products_count|escape:'htmlall':'UTF-8'}</strong>
            </div>
            <div class="alert alert-info" style="margin: 0;">
                {l s='Indexing products can take a while, so it is recommended to use cron jobs.' mod='brad'} <br>
                <strong>{l s='Reindex all products cron' mod='brad'}: </strong> <em>{$index_all_products_task_url|escape:'htmlall':'UTF-8'}</em> <br>
                <strong>{l s='Reindex prices cron' mod='brad'}: </strong> <em>{$index_prices_task_url|escape:'htmlall':'UTF-8'}</em>
            </div>
        {else}
            <div class="alert alert-danger" style="margin: 0;">
                <strong>{l s='Cannot establish Elasticsearch connection.' mod='brad'}</strong>
                {l s='Please check your server host settings below.' mod='brad'}
            </div>
        {/if}
    </div>

    <div class="panel-footer">
        <form method="post" action="{$current_url}">
            <button type="submit" class="btn btn-default pull-right" name="brad_reindex_prices">
                <i class="process-icon-payment"></i> {l s='Reindex prices' mod='brad'}
            </button>
            <button type="submit" class="btn btn-default pull-right" name="brad_reindex_missing_products">
                <i class="process-icon-refresh"></i> {l s='Reindex missing products' mod='brad'}
            </button>
            <button type="submit" class="btn btn-default pull-right" name="brad_reindex_all_products">
                <i class="process-icon-reset"></i> {l s='Reindex all products' mod='brad'}
            </button>
        </form>
    </div>

</div>