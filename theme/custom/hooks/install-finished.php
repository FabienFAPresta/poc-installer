<?php
global $kernel;

if ($kernel) {
    $this->initialized = true;
    $accountsService = null;

    $container = $kernel->getContainer();

    try {
        $accountsFacade = $container->get('ps_accounts.facade');
        $accountsService = $accountsFacade->getPsAccountsService();
    } catch (\PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
        $accountsInstaller =$container->get('ps_accounts.installer');
        $accountsInstaller->install();
        $accountsFacade = $container->get('ps_accounts.facade');
        $accountsService = $accountsFacade->getPsAccountsService();
    }

    dd($accountsService);
}