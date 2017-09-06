<?php

namespace PHPAnt\Setup;

class SetupConfigsFactory {

    public static function getSetupConfigs($interactive) {
        switch ($interactive) {
            case true:
                return new InteractiveConfigs();
                break;
            case false:
                return new JSONConfigs();
                break;
        }
    }
}