<?php
namespace KmbPuppetTest;

use KmbCoreTest\AbstractBootstrap;
use Zend\Stdlib\ArrayUtils;

define('BASE_PATH', dirname(dirname(__DIR__)));
$kmbCoreModulePath = BASE_PATH . '/vendor/kambalabs/kmb-core';
if (!is_dir($kmbCoreModulePath)) {
    $kmbCoreModulePath = dirname(BASE_PATH) . '/KmbCore';
}
require $kmbCoreModulePath . '/test/KmbCoreTest/AbstractBootstrap.php';

class Bootstrap extends AbstractBootstrap
{
    /**
     * Get the root path of the module.
     * Usually : dirname(dirname(__DIR__))
     *
     * @return string
     */
    public static function rootPath()
    {
        return BASE_PATH;
    }

    public static function getApplicationConfig()
    {
        return ArrayUtils::merge(
            parent::getApplicationConfig(),
            array(
                'module_listener_options' => array(
                    'config_glob_paths' => array(
                        dirname(__DIR__) . '/{,*.}{global,local}.php',
                    ),
                ),
                'modules' => array(
                    'ZfcRbac',
                    'GtnDataTables',
                    'KmbAuthentication',
                    'KmbFakeAuthentication',
                    'KmbPermission',
                    'KmbPuppetDb',
                    'KmbDashboard',
                    'KmbServers',
                    'KmbPuppet',
                )
            )
        );
    }

    public static function getNamespacePaths()
    {
        return ArrayUtils::merge(
            parent::getNamespacePaths(),
            array(
                'KmbPuppetDbTest' => static::findParentPath('kambalabs') . '/KmbPuppetDb/test/KmbPuppetDbTest',
                __NAMESPACE__ => __DIR__,
            )
        );
    }
}

Bootstrap::init();
Bootstrap::chroot();
