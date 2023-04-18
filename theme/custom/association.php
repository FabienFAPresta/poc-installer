<div id="association">
    <prestashop-accounts></prestashop-accounts>
</div>

<script>
    window.contextPsAccounts = <?php echo json_encode($this->accountContext) ?>;
    /*********************
     * PrestaShop Account *
     * *******************/
    window.psaccountsVue.init();
</script>