<script type="text/javascript">
    (function ($) {

    })(jQuery.noConflict());
</script>
<style type="text/css">
    #wpar .success { font-weight: bold; color: rgb(34, 113, 177) }
    #wpar .failure { color: red }
    #wpar pre { font-size: smaller; margin: 0 }
</style>
<div class="wrap" id="wpar">
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo add_query_arg(['page' => 'api-retry', 'tab' => 'settings'], '/wp-admin/options-general.php'); ?>"
           class="nav-tab <?php echo((isset($_GET['tab']) && $_GET['tab'] === 'settings' || empty($_GET['tab'])) ? 'nav-tab-active' : ''); ?>"><?php _e('Settings', API_RETRY_DOMAIN); ?></a>
        <a href="<?php echo add_query_arg(['page' => 'api-retry', 'tab' => 'monitor'], '/wp-admin/options-general.php'); ?>"
           class="nav-tab <?php echo((isset($_GET['tab']) && $_GET['tab'] === 'monitor') ? 'nav-tab-active' : ''); ?>"><?php _e('Monitor', API_RETRY_DOMAIN); ?></a>
    </h2><?php
    if (isset($_GET['tab']) && $_GET['tab'] === 'settings' || !isset($_GET['tab'])) {
        require_once dirname(__FILE__) . '/settings.php';
    } else if (isset($_GET['tab']) && $_GET['tab'] === 'monitor') {
        require_once dirname(__FILE__) . '/monitor.php';
    } ?>
</div>