<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jul 15 Modified in v1.5.8-alpha $
 */

namespace Zencart\PluginSupport;

class Installer
{
    protected $errors = [];

    public function __construct($patchInstaller, $scriptedInstallerFactory, $errorContainer)
    {
        $this->patchInstaller = $patchInstaller;
        $this->scriptedInstallerFactory = $scriptedInstallerFactory;
        $this->errorContainer = $errorContainer;
    }

    public function executeInstallers($pluginDir)
    {
        $this->executePatchInstaller($pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return;
        }
        $this->executeScriptedInstaller($pluginDir);
    }

    public function executeUninstallers($pluginDir)
    {
        $this->executePatchUninstaller($pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return;
        }
        $this->executeScriptedUninstaller($pluginDir);
    }

    public function executeUpgraders($pluginDir, $oldVersion)
    {
        $this->executeScriptedUpgrader($pluginDir, $oldVersion);
    }

    protected function executePatchInstaller($pluginDir)
    {
        $patchFile = 'install.sql';
        $this->executePatchFile($pluginDir, $patchFile);
   }

    protected function executePatchUninstaller($pluginDir)
    {
        $patchFile = 'uninstall.sql';
        $this->executePatchFile($pluginDir, $patchFile);
    }

    protected function executePatchFile($pluginDir, $patchFile)
    {
        if (!file_exists($pluginDir . '/Installer/' . $patchFile)) {
            return;
        }
        $lines = file($pluginDir . '/Installer/' . $patchFile);
        $paramLines = $this->patchInstaller->parse($lines);
        if ($this->errorContainer->hasErrors()) {
            return;
        }
        $this->patchInstaller->executePatchSql($paramLines);

    }

    protected function executeScriptedInstaller($pluginDir)
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->doInstall();
    }

    protected function executeScriptedUninstaller($pluginDir)
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->doUninstall();
    }

    protected function executeScriptedUpgrader($pluginDir, $oldVersion)
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->doUpgrade($oldVersion);
    }
}
