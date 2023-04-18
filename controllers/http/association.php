<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * Step 1 : display language form
 */
class InstallControllerHttpAssociation extends InstallControllerHttp implements HttpConfigureInterface
{
    private bool $initialized = false;
    public array $accountContext = [];

    /**
     * Get the name of the admin directory.
     *
     * @return string
     */
    private function getAdminDir(): string
    {
        chdir(_PS_CORE_DIR_);
        $directories = glob('admin*', GLOB_ONLYDIR);
        return is_array($directories) ? $directories[0] : "";
    }

    /**
     * Initialize context inside the installer
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function initContext(): void
    {
        global $smarty;

        Context::getContext()->shop = new Shop(1);
        Shop::setContext(Shop::CONTEXT_SHOP, 1);
        Configuration::loadConfiguration();
        Context::getContext()->language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        Context::getContext()->country = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT'));
        Context::getContext()->currency = new Currency((int) Configuration::get('PS_CURRENCY_DEFAULT'));
        Context::getContext()->cart = new Cart();
        Context::getContext()->employee = new Employee(1);
        define('_PS_SMARTY_FAST_LOAD_', true);
        require_once _PS_ROOT_DIR_ . '/config/smarty.config.inc.php';

        Context::getContext()->smarty = $smarty;

        if (!defined('_PS_ADMIN_DIR_')) define('_PS_ADMIN_DIR_', $this->getAdminDir());
    }

    /**
     * {@inheritdoc}
     */
    public function process(): void
    {
        global $kernel;

        $this->initContext();

        if ($kernel) {
            $this->initialized = true;
            $accountsService = null;

            $container = $kernel->getContainer();

            try {
                $accountsFacade = $container->get('ps_accounts.facade');
                //$accountsService = $accountsFacade->getPsAccountsService();
            } catch (\PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
                $accountsInstaller = $container->get('ps_accounts.installer');
                $accountsInstaller->install();
                $accountsFacade = $container->get('ps_accounts.facade');
                //$accountsService = $accountsFacade->getPsAccountsService();
            }

            // Avoid ps account presenter error while redefining a session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            $this->accountContext = $accountsFacade->getPsAccountsPresenter()->present();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function display(): void
    {
        if ($this->initialized) {
            $this->displayContent('association');
        } else {
            $this->displayContent('association-uninitialized');
        }
    }
}
