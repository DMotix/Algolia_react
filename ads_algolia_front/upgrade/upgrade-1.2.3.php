<?php

/*
 * File: /upgrade/upgrade-1.2.3.php
 */


function upgrade_module_1_2_3($module)
{

    if (!$module->installDB())
        return false;
    return true;
}