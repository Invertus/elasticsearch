<?php

/**
 * Class BradShop
 */
class BradShop extends Shop
{
    /**
     * Check if current shop is in single shop context
     *
     * @return bool
     */
    public static function isSingleShopContext()
    {
        if (in_array(Shop::getContext(), [Shop::CONTEXT_ALL, Shop::CONTEXT_GROUP])) {
            return false;
        }

        return true;
    }
}
