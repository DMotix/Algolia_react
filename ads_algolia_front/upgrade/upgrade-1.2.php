<?php

/*
 * File: /upgrade/upgrade-1.1.php
 */

function upgrade_module_1_2($module) {
    // Process Module upgrade to 1.2
    if (!$module->installDB())
        return false;
    if (
        !$module->registerHook('displayCustomerAccount') OR
        !$module->registerHook('displayMyAccountBlock'))
        return false;
    return true;
}