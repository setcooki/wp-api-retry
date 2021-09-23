<h2><?php _e('Settings', API_RETRY_DOMAIN); ?></h2>
<div id="poststuff">
    <form id="wpar-settings" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <div class="postbox">
            <h3 class="hndle">
                <label for="title"><?php _e('General', API_RETRY_DOMAIN); ?></label>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e('Enable retry cron', API_RETRY_DOMAIN); ?></th>
                        <td data-scope="enabled">
                            <input id="wpar-enabled" name="enabled" type="checkbox"
                                   value="1" <?php echo((isset($options) && isset($options->enabled) && (int)$options->enabled === 1) ? 'checked="checked"' : ''); ?> />
                            <p class="description">
                                <?php _e('Enable or disable retry cron', API_RETRY_DOMAIN); ?>
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Purge retry queue items after days', API_RETRY_DOMAIN); ?></th>
                        <td data-scope="purge-interval">
                            <input id="wpar-purge-interval" name="purge_interval" type="number"
                                   value="<?php echo((isset($options) && isset($options->purge_interval)) ? (int)$options->purge_interval : ''); ?>" min="0" style="width:100px;">
                            <p class="description">
                                <?php _e('Set to 0 to disable purge', API_RETRY_DOMAIN); ?>
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Maximum API (re)tries', API_RETRY_DOMAIN); ?></th>
                        <td data-scope="max-tries">
                            <input id="wpar-max-tries" name="max_tries" type="number"
                                   value="<?php echo((isset($options) && isset($options->max_tries)) ? (int)$options->max_tries : ''); ?>" min="1" max="100" style="width:100px;">
                            <p class="description">
                                <?php _e('Limit the number of tries for each API call', API_RETRY_DOMAIN); ?>
                            </p>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php echo __('Save', API_RETRY_DOMAIN); ?>"/>
                </p>
            </div>
        </div>
    </form>
</div>