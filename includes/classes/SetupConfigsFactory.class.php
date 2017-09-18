<?php

namespace PHPAnt\Setup;

class SetupConfigsFactory {

    public static function getSetupConfigs($interactive, $baseDir) {
        switch ($interactive) {
            case true:
                return new InteractiveConfigs($baseDir);
                break;
            case false:
                return new JSONConfigs($baseDir);
                break;
        }
    }
}