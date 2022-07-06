<?php

/*
 * File: /upgrade/upgrade-1.2.4.php
 */


function upgrade_module_1_2_4($module)
{

    if (!$module->registerHook('actionAdsImportVehicleFinish'))
        return false;
    return true;
}