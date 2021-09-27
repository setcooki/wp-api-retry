<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            function json(str) {
                try {
                    return JSON.parse(str);
                } catch (e) {
                    return false;
                }
            }

            function reveal() {
                var col = null, j = false;
                $.each($('#wpar-monitor').find('td.revealable'), function (i, c) {
                    col = $(c);
                    if (col.hasClass('revealed')) {
                        if (col.find('pre').length) {
                            col.html('<samp>' + JSON.stringify(JSON.parse(col.find('pre').html())) + '</samp>');
                        }
                        col.removeClass('revealed');
                    } else {
                        j = json(col.find('samp').html());
                        if (j) {
                            col.html('<a href="#TB_inline?&width=600&height=500&inlineId=reveal' + i + '" class="thickbox" title="Inspect">Inspect</a><div id="reveal' + i + '"><pre>' + JSON.stringify(j, null, 2) + '</pre></p>');
                        }
                        col.addClass('revealed');
                    }
                });
            }

            $('#wpar-monitor').find('th a.reveal').on('click', function (e) {
                e.preventDefault();
                reveal();
                return false;
            });
            $('#wpar-monitor').find('td.column-action a[data-action="focus"]').on('click', function (e) {
                e.preventDefault();
                if (!$(e.currentTarget).hasClass('active')) {
                    $(e.currentTarget).addClass('active');
                    $('#wpar-monitor').find('tbody').addClass('focus');
                    $(e.currentTarget).closest('tr').addClass('focused');
                } else {
                    $(e.currentTarget).removeClass('active');
                    $('#wpar-monitor').find('tbody').removeClass('focus');
                    $(e.currentTarget).closest('tr').removeClass('focused');
                }
                reveal();
                return false;
            });
            $('#posts-filter').on('submit', function (e) {
                var monitor = $('#wpar-monitor');
                var action = $(e.currentTarget).find('select[name="action1"]').val();
                var checked = monitor.find('th.check-column input[type="checkbox"]:checked');
                if (action === 'focus') {
                    e.preventDefault();
                    if (checked.length) {
                        monitor.find('tbody').addClass('focus');
                        monitor.find('tbody tr').removeClass('focused');
                        checked.closest('tr').addClass('focused');
                    } else {
                        monitor.find('tbody').removeClass('focus');
                        monitor.find('tbody tr').removeClass('focused');
                    }
                    return false;
                }
            });
        })
    })(jQuery.noConflict());
</script>
<style type="text/css">
    #wpar .success {
        font-weight: bold;
        color: rgb(34, 113, 177)
    }

    #wpar .failure {
        color: red
    }

    #wpar samp {
        font-family: inherit
        min-width: 120px;
    }

    #wpar pre {
        font-size: smaller;
        margin: 0;
        white-space: pre-wrap;
    }

    #wpar thead th > span {
        display: block;
    }

    #wpar tbody.focus tr:not(.focused) {
        display: none;
    }

    #wpar td.revealable:not(.revealed) samp {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
    }
</style>
<h2><?php _e('Monitor', API_RETRY_DOMAIN); ?></h2>
<div>
    <form id="posts-filter" method="get">
        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input"><?php _e('Search'); ?></label>
            <input type="search" id="post-search-input" name="s" value="">
            <input type="submit" id="search-submit" class="button" value="<?php _e('Search'); ?>">
        </p>
        <input type="hidden" name="page" value="api-retry">
        <input type="hidden" name="tab" value="monitor">
        <div class="tablenav top" style="margin-bottom: 10px">
            <div class="alignleft actions">
                <?php if (isset($filter) && !empty($filter)) { ?>
                    <select name="provider" id="filter-by-provider">
                        <option selected="selected" value=""><?php _e('All Providers', API_RETRY_DOMAIN); ?></option>
                        <?php foreach ($filter->providers as $provider) { ?>
                            <option value="<?php echo $provider; ?>" <?php echo((isset($_GET['provider']) && $_GET['provider'] === $provider) ? 'selected="selected"' : ''); ?>><?php echo $provider; ?></option>
                        <?php } ?>
                    </select>
                    <select name="method" id="filter-by-method">
                        <option selected="selected" value=""><?php _e('All Methods', API_RETRY_DOMAIN); ?></option>
                        <?php foreach ($filter->methods as $method) { ?>
                            <option value="<?php echo $method; ?>" <?php echo((isset($_GET['method']) && $_GET['method'] === $method) ? 'selected="selected"' : ''); ?>><?php echo $method; ?></option>
                        <?php } ?>
                    </select>
                    <select name="status" id="filter-by-status">
                        <option selected="selected" value=""><?php _e('All Status', API_RETRY_DOMAIN); ?></option>
                        <option value="-1" <?php echo((isset($_GET['status']) && (int)$_GET['status'] === -1) ? 'selected="selected"' : ''); ?>><?php _e('-1 (Failed)', API_RETRY_DOMAIN); ?></option>
                        <option value="0" <?php echo((isset($_GET['status']) && (int)$_GET['status'] === 0) ? 'selected="selected"' : ''); ?>><?php _e('0 (Queued)', API_RETRY_DOMAIN); ?></option>
                        <option value="1" <?php echo((isset($_GET['status']) && (int)$_GET['status'] === 1) ? 'selected="selected"' : ''); ?>><?php _e('1 (Success)', API_RETRY_DOMAIN); ?></option>
                    </select>
                    <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _e('Filter'); ?>">
                <?php } ?>
            </div>
            <div class="tablenav-pages" style="margin-top:10px">
                <span class="pagination-links">
                    <?php
                    echo paginate_links([
                        'base' => add_query_arg('pagenum', '%#%'),
                        'format' => '',
                        'prev_text' => '<span aria-label="' . esc_attr__('Previous page') . '">' . __('&laquo;') . '</span>',
                        'next_text' => '<span aria-label="' . esc_attr__('Next page') . '">' . __('&raquo;') . '</span>',
                        'before_page_number' => '<span class="screen-reader-text">' . __('Page') . '</span> ',
                        'total' => $num_of_pages,
                        'current' => $pagenum
                    ]);
                    ?>
                </span>
            </div>
            <br class="clear">
        </div>
        <table id="wpar-monitor" class="wp-list-table widefat striped">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-1">Alle auswählen</label>
                    <input id="cb-select-all-1" type="checkbox">
                </td>
                <th scope="col" id="id" class="manage-column column-id <?php echo(($this->menu->orderby === 'id') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                    style="">
                    <span style="width: 50px">                    <a
                                href="<?php echo add_query_arg(['orderby' => 'id', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                                            <span><?php _e('ID', API_RETRY_DOMAIN); ?></span>
                                            <span class="sorting-indicator"></span>
                                        </a></span>

                </th>
                <th scope="col" id="provider"
                    class="manage-column column-provider <?php echo(($this->menu->orderby === 'provider') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                    style="">
                    <span style="width:90px">
                        <a href="<?php echo add_query_arg(['orderby' => 'provider', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                            <span><?php _e('Provider', API_RETRY_DOMAIN); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </span>

                </th>
                <th scope="col" id="method"
                    class="manage-column column-method <?php echo(($this->menu->orderby === 'method') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                    style="">
                    <span style="width:90px">
                    <a href="<?php echo add_query_arg(['orderby' => 'method', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                        <span><?php _e('Method', API_RETRY_DOMAIN); ?></span>
                        <span class="sorting-indicator"></span>
                    </a>
                    </span>
                </th>
                <th scope="col" id="service"
                    class="manage-column column-service <?php echo(($this->menu->orderby === 'service') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                    style="">
                    <span style="width: 90px">
                        <a href="<?php echo add_query_arg(['orderby' => 'service', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                            <span><?php _e('Service', API_RETRY_DOMAIN); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </span>
                </th>
                <th scope="col" id="endpoint"
                    class="manage-column column-endpoint <?php echo(($this->menu->orderby === 'endpoint') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                    style="">
                    <span style="width:auto;min-width: 100px">
                        <a href="<?php echo add_query_arg(['orderby' => 'endpoint', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                            <span><?php _e('Endpoint', API_RETRY_DOMAIN); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </span>
                </th>
                <th scope="col" id="request" class="manage-column column-request focus" style="">
                    <span style="width: auto; min-width: 100px">
                    <?php _e('Request', API_RETRY_DOMAIN); ?>
                    <a href="javascript:void(0);" class="reveal" title="<?php _e('Details ...', API_RETRY_DOMAIN); ?>"><span class="dashicons dashicons-editor-code"
                                                                                                                             style="font-size: 20px"></span></a>
                    </span>
                </th>
                <th scope="col" id="status"
                    class="manage-column column-status <?php echo(($this->menu->orderby === 'status') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                    style="">
                    <span style="width:80px">
                    <a href="<?php echo add_query_arg(['orderby' => 'status', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                        <span><?php _e('Status', API_RETRY_DOMAIN); ?></span>
                        <span class="sorting-indicator"></span>
                    </a>
                    </span>
                </th>
                <th scope="col" id="tries"
                    class="manage-column column-tries  <?php echo(($this->menu->orderby === 'tries') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                    style="">
                    <span style="width:70px">
                    <a href="<?php echo add_query_arg(['orderby' => 'tries', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                        <span><?php _e('Tries', API_RETRY_DOMAIN); ?></span>
                        <span class="sorting-indicator"></span>
                    </a>
                    </span>
                </th>
                <th scope="col" id="failure" class="manage-column column-failure focus" style="">
                    <span style="width: auto; min-width: 100px">
                    <?php _e('Failure', API_RETRY_DOMAIN); ?>
                    <a href="javascript:void(0);" class="reveal" title="<?php _e('Details ...', API_RETRY_DOMAIN); ?>"><span class="dashicons dashicons-editor-code"
                                                                                                                             style="font-size: 20px"></span></a>
                    </span>
                </th>
                <th scope="col" id="success" class="manage-column column-success focus" style="">
                    <span style="width: auto; min-width: 100px">
                    <?php _e('Success', API_RETRY_DOMAIN); ?>
                    <a href="javascript:void(0);" class="reveal" title="<?php _e('Details ...', API_RETRY_DOMAIN); ?>"><span class="dashicons dashicons-editor-code"
                                                                                                                             style="font-size: 20px"></span></a>
                    </span>
                </th>
                <th scope="col" id="custom" class="manage-column column-custom focus" style="">
                    <span style="width: auto; min-width: 100px">
                    <?php _e('Custom', API_RETRY_DOMAIN); ?>
                    <a href="javascript:void(0);" class="reveal" title="<?php _e('Details ...', API_RETRY_DOMAIN); ?>"><span class="dashicons dashicons-editor-code"
                                                                                                                             style="font-size: 20px"></span></a>
                    </span>
                </th>
                <th scope="col" id="created"
                    class="manage-column column-created <?php echo(($this->menu->orderby === 'create') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?>"
                    style="">
                    <span style="width: 110px">
                    <a href="<?php echo add_query_arg(['orderby' => 'created', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                        <span><?php _e('Created', API_RETRY_DOMAIN); ?></span>
                        <span class="sorting-indicator"></span>
                    </a>
                    </span>
                </th>
                <th scope="col" id="timestamp"
                    class="manage-column column-timestamp <?php echo(($this->menu->orderby === 'timestamp') ? 'sorted' : 'sortable'); ?> <?php echo $this->menu->order; ?> "
                    style="">
                    <span style="width: 110px">
                    <a href="<?php echo add_query_arg(['orderby' => 'timestamp', 'order' => ($this->menu->order === 'desc') ? 'asc' : 'desc']); ?>">
                        <span><?php _e('Timestamp', API_RETRY_DOMAIN); ?></span>
                        <span class="sorting-indicator"></span>
                    </a>
                    </span>
                </th>
                <th scope="col" id="action" class="manage-column column-action" style=""><span style="width:100px"><?php _e('Actions', API_RETRY_DOMAIN); ?></span></th>
            </tr>
            </thead>
            <tbody id="the-list">
            <?php foreach ((array)$items as $item) { ?>
                <tr id="<?php echo $item->id; ?>" style="<?php echo((isset($_GET['id']) && (int)$_GET['id'] === (int)$item->id) ? 'background-color: #00ff00' : ''); ?>">
                    <th scope="row" class="check-column">
                        <label class="screen-reader-text" for="cb-select-<?php echo $item->id; ?>"><?php _e('Choose', API_RETRY_DOMAIN); ?></label>
                        <input id="cb-select-<?php echo $item->id; ?>" type="checkbox" name="items[]" value="<?php echo $item->id; ?>">
                    </th>
                    <td class="column-id column-primary" data-colname="<?php _e('ID', API_RETRY_DOMAIN); ?>"><?php echo $item->id; ?></td>
                    <td class="column-provider" data-colname="<?php _e('Provider', API_RETRY_DOMAIN); ?>"><?php echo $item->provider; ?></td>
                    <td class="column-method" data-colname="<?php _e('Method', API_RETRY_DOMAIN); ?>"><?php echo $item->method; ?></td>
                    <td class="column-service" data-colname="<?php _e('Service', API_RETRY_DOMAIN); ?>"><?php echo $item->service; ?></td>
                    <td class="column-endpoint revealable" data-colname="<?php _e('Endpoint', API_RETRY_DOMAIN); ?>"><samp><?php echo $item->endpoint; ?></samp></td>
                    <td class="column-request revealable" data-colname="<?php _e('Request', API_RETRY_DOMAIN); ?>"><samp><?php echo $item->request; ?></samp></td>
                    <td class="column-status" data-colname="<?php _e('Status', API_RETRY_DOMAIN); ?>">
                        <div class="reveal">
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
                        </div>
                    </td>
                    <td class="column-tries" data-colname="<?php _e('Tries', API_RETRY_DOMAIN); ?>">
                        <div class="reveal">
                            <?php if ($item->tries > $tries) {
                                echo sprintf('<span class="failure">%s</span>', $item->tries);
                            } else {
                                echo $item->tries;
                            } ?>
                        </div>
                    </td>
                    <td class="column-failure revealable" data-colname="<?php _e('Failure', API_RETRY_DOMAIN); ?>"><samp><?php echo $item->failure; ?></samp></td>
                    <td class="column-success revealable" data-colname="<?php _e('Success', API_RETRY_DOMAIN); ?>"><samp><?php echo $item->success; ?></samp></td>
                    <td class="column-custom revealable" data-colname="<?php _e('Custom', API_RETRY_DOMAIN); ?>"><samp><?php echo $item->custom; ?></samp></td>
                    <td class="column-created" data-colname="<?php _e('Created', API_RETRY_DOMAIN); ?>"><?php echo $item->created; ?></td>
                    <td class="column-timestamp" data-colname="<?php _e('Timestamp', API_RETRY_DOMAIN); ?>"><?php echo $item->timestamp; ?></td>
                    <td class="column-action" data-colname="<?php _e('Action', API_RETRY_DOMAIN); ?>">
                        <?php if ((int)$item->status !== 1) { ?>
                            <a href="<?php echo add_query_arg(array_merge($_GET, ['try' => $item->id]), '/wp-admin/options-general.php'); ?>" data-action="try"
                               data-id="<?php echo $item->id; ?>"
                               title="<?php _e('Try', API_RETRY_DOMAIN); ?>" class="button button-small"><?php _e('Try', API_RETRY_DOMAIN); ?></a>
                            &nbsp;
                            <a href="" data-action="focus"
                               data-id="<?php echo $item->id; ?>"
                               title="<?php _e('Focus', API_RETRY_DOMAIN); ?>" class="button button-small"><?php _e('Focus', API_RETRY_DOMAIN); ?></a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-bottom" class="screen-reader-text"><?php _e('Select bulk action'); ?></label>
                <select name="action1" id="bulk-action-selector-bottom">
                    <option value="-1"><?php _e('Bulk Actions'); ?></option>
                    <option value="focus" class="hide-if-no-js"><?php _e('Focus / Unfocus', API_RETRY_DOMAIN); ?></option>
                </select>
                <input type="submit" id="doaction1" class="button action" value="<?php _e('Apply', API_RETRY_DOMAIN); ?>">
            </div>
            <div class="alignleft actions">
                &nbsp;
            </div>
            <br class="clear">
        </div>
    </form>
</div>