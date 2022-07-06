<?php

/*
 * File: /upgrade/upgrade-1.2.2.php
 */


function upgrade_module_1_2_2($module)
{

    if (!$module->registerHook('GSitemapAppendUrls'))
        return false;
    return true;
}