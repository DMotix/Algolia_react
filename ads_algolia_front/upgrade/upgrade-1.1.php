<?php

/*
 * File: /upgrade/upgrade-1.1.php
 */

function upgrade_module_1_1($module) {
    // Process Module upgrade to 1.1
    if (Module::isInstalled('jscomposer') && Module::isEnabled('jscomposer'))
        if(!$module->registerHook('vcBeforeInit'))
            return false;
    return true;
}