<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            $('#wpar-monitor').find('th#request a').on('click', function (e) {
                var column = $('#wpar-monitor').find('td.column-request');
                if ($(e.currentTarget).hasClass('on')) {
                    $(e.currentTarget).removeClass('on').addClass('off');
                    column.html(JSON.stringify(JSON.parse(column.find('pre').html())));
                } else {
                    $(e.currentTarget).removeClass('off').addClass('on');
                    column.html('<pre>' + JSON.stringify(JSON.parse(column.html()), null, 2) + '</pre>');
                }
            });
        })
    })(jQuery.noConflict());
</script>
<h2><?php _e('Monitor', API_RETRY_DOMAIN); ?></h2>
<div>
    <table id="wpar-monitor" class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th scope="col" id="id" class="manage-column column-id <?php echo(($this->menu->orderby === 'id') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                style="width:50px;">
                <a href="<?php echo add_query_arg(['orderby' => 'id', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('ID', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="provider"
                class="manage-column column-provider <?php echo(($this->menu->orderby === 'provider') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                style="width:120px;">
                <a href="<?php echo add_query_arg(['orderby' => 'provider', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Provider', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="method"
                class="manage-column column-method <?php echo(($this->menu->orderby === 'method') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                style="width:auto">
                <a href="<?php echo add_query_arg(['orderby' => 'method', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Method', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="service"
                class="manage-column column-service <?php echo(($this->menu->orderby === 'service') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                style="width:auto">
                <a href="<?php echo add_query_arg(['orderby' => 'service', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Service', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="endpoint" class="manage-column column-endpoint <?php echo(($this->menu->orderby === 'endpoint') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                style="width:auto">
                <a href="<?php echo add_query_arg(['orderby' => 'endpoint', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Endpoint', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="request" class="manage-column column-request" style="width:auto">
                <?php _e('Request', API_RETRY_DOMAIN); ?>
                <a href="javascript:void(0);" title="<?php _e('Decode', API_RETRY_DOMAIN); ?>"><span class="dashicons dashicons-editor-code" style="font-size: 20px"></span></a>
            </th>
            <th scope="col" id="status"
                class="manage-column column-status <?php echo(($this->menu->orderby === 'status') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                style="width:100px;">
                <a href="<?php echo add_query_arg(['orderby' => 'status', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Status', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="tries"
                class="manage-column column-tries <?php echo(($this->menu->orderby === 'tries') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>" style="width:70px;">
                <a href="<?php echo add_query_arg(['orderby' => 'tries', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Tries', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="message"
                class="manage-column column-message <?php echo(($this->menu->orderby === 'tries') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                style="width:auto">
                <a href="<?php echo add_query_arg(['orderby' => 'message', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Message', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="timestamp"
                class="manage-column column-timestamp <?php echo(($this->menu->orderby === 'timestamp') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?> "
                style="width:120px;">
                <a href="<?php echo add_query_arg(['orderby' => 'timestamp', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Timestamp', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="created"
                class="manage-column column-created <?php echo(($this->menu->orderby === 'create') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                style="width:120px;">
                <a href="<?php echo add_query_arg(['orderby' => 'created', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                    <span><?php _e('Created', API_RETRY_DOMAIN); ?></span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th scope="col" id="action" class="manage-column column-action" style="width:50px"><?php _e('Actions', API_RETRY_DOMAIN); ?></th>
        </tr>
        </thead>
        <tbody id="the-list">
        <?php foreach ((array)$items as $item) { ?>
            <tr id="<?php echo $item->id; ?>" style="<?php echo((isset($_GET['id']) && (int)$_GET['id'] === (int)$item->id) ? 'background-color: #00ff00' : ''); ?>">
                <td class="column-id column-primary" data-colname="<?php _e('ID', API_RETRY_DOMAIN); ?>"><?php echo $item->id; ?></td>
                <td class="column-provider" data-colname="<?php _e('Provider', API_RETRY_DOMAIN); ?>"><?php echo $item->provider; ?></td>
                <td class="column-method" data-colname="<?php _e('Method', API_RETRY_DOMAIN); ?>"><?php echo $item->method; ?></td>
                <td class="column-service" data-colname="<?php _e('Service', API_RETRY_DOMAIN); ?>"><?php echo $item->service; ?></td>
                <td class="column-endpoint" data-colname="<?php _e('Endpoint', API_RETRY_DOMAIN); ?>"><?php echo $item->endpoint; ?></td>
                <td class="column-request" data-colname="<?php _e('Request', API_RETRY_DOMAIN); ?>"><?php echo $item->request; ?></td>
                <td class="column-status" data-colname="<?php _e('Status', API_RETRY_DOMAIN); ?>">
                    <?php switch ($item->status) {
                        case 1:
                            echo sprintf('<span class="success">1 (%s)</span>', __('Success', API_RETRY_DOMAIN));
                            break;
                        case -1:
                            echo sprintf('<span class="failure">-1 (%s)</span>', __('Failure', API_RETRY_DOMAIN));
                            break;
                        default:
                            echo sprintf('<span class="queued">0 (%s)</span>', __('Queued', API_RETRY_DOMAIN));

                    } ?>
                </td>
                <td class="column-tries" data-colname="<?php _e('Tries', API_RETRY_DOMAIN); ?>">
                    <?php if ($item->tries > $tries) {
                        echo sprintf('<span class="failure">%s</span>', $item->tries);
                    } else {
                        echo $item->tries;
                    } ?>
                </td>
                <td class="column-message" data-colname="<?php _e('Message', API_RETRY_DOMAIN); ?>"><?php echo $item->message; ?></td>
                <td class="column-timestamp" data-colname="<?php _e('Timestamp', API_RETRY_DOMAIN); ?>"><?php echo $item->timestamp; ?></td>
                <td class="column-created" data-colname="<?php _e('Created', API_RETRY_DOMAIN); ?>"><?php echo $item->created; ?></td>
                <td class="column-action" data-colname="<?php _e('Action', API_RETRY_DOMAIN); ?>">
                    <?php if ((int)$item->status !== 1) { ?>
                        <a href="<?php echo add_query_arg(array_merge($_GET, ['try' => $item->id]), '/wp-admin/options-general.php'); ?>" data-action="try"
                           data-id="<?php echo $item->id; ?>"
                           title="<?php _e('Try', API_RETRY_DOMAIN); ?>" class="button"><?php _e('Try', API_RETRY_DOMAIN); ?></a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>