<?php
	_hw_plugins_server_script( '/templates/server-page' );
	$plugins = hiweb_plugins_server()->plugins();
	$hostedPlugins = hiweb_plugins_server()->host()->plugins();

	$action_buttons = array(
		'put' => '<a href="#" data-click="host" title="Make Archive file and put them on You\'r Repository"><i class="dashicons dashicons-plus-alt"></i> Put On Host</a>',
		'remove' => '<a href="#" data-click="remove" title="Remove Archive file and UnHost this Plugin from You\'r Own Repository"><i class="dashicons dashicons-dismiss"></i> Remove From Host</a>',
		'host' => '<a href="#" data-click="host" title="Place Archive file on You\'r Own Repository"><i class="dashicons dashicons-yes"></i> Host</a>', 'unhost' => '<a href="#" data-click="unhost"><i class="dashicons dashicons-no-alt"></i> UnHost</a>',
		'update' => '<a href="#" data-click="host" title="Update Archive file on you\'r host from WordPress Plugins"><i class="dashicons dashicons-update"></i> Update</a>',
		'install' => '<a href="#" data-click="install"><i class="dashicons dashicons-lightbulb"></i> Install</a>',
		'reinstall' => '<a href="#" data-click="install" title="Re-Install local WordPress plugin from Archive file"><i class="dashicons dashicons-controls-repeat"></i> Re-Install</a>',
		'download' => '<a href="%s" title="Download Archive Plugin File To You\'r PC..." target="_blank"><i class="dashicons dashicons-download"></i> Download To PC</a>'
	);

?>
<div class="wrap">
	<h1>Host plugins on Server <!--<a href="" class="page-title-action">Host Selected Plugins</a>--></h1>


	<h2 class="screen-reader-text">Filter plugins list</h2>
	<ul class="subsubsub">
		<li class="all"><a href="plugins.php?plugin_status=all" class="current">All <span class="count">(<?php echo count( $hostedPlugins ) . ' / ' . count( $plugins ); ?>)</span></a> | ...</li>
		<!--<li class="hosted"><a href="plugins.php?plugin_status=active">Hosted <span class="count">(2)</span></a> |</li>
		<li class="unhosted"><a href="plugins.php?plugin_status=inactive">Unhosted <span class="count">(16)</span></a> |</li>
		<li class="upgrade"><a href="plugins.php?plugin_status=upgrade">Update Available <span class="count">(1)</span></a></li>-->
	</ul>
	<!--<form method="get">
		<p class="search-box">
			<label class="screen-reader-text" for="plugin-search-input">Search Installed Plugins:</label>
			<input type="search" id="plugin-search-input" name="s" value="">
			<input type="submit" id="search-submit" class="button" value="Search Installed Plugins"></p>
	</form>-->

	<form method="post" id="bulk-action-form">

		<!--<input type="hidden" name="plugin_status" value="all">
		<input type="hidden" name="paged" value="1">


		<div class="tablenav top">

			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label><select name="action" id="bulk-action-selector-top">
					<option value="-1">Bulk Actions</option>
					<option value="hosted-selected">Hosted</option>
					<option value="activate-selected">Activate</option>
					<option value="deactivate-selected">Deactivate</option>
					<option value="update-selected">Update</option>
					<option value="delete-selected">Delete</option>
				</select>
				<input type="submit" id="doaction" class="button action" value="Apply">
			</div>
			<div class="tablenav-pages one-page"><span class="displaying-num">18 items</span>
<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Current Page</label><input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"> of <span
			class="total-pages">1</span></span>
<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
			<br class="clear">
		</div>
		<h2 class="screen-reader-text">Plugins list</h2>-->

		<table class="wp-list-table widefat plugins">
			<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><!--<label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox">--></td>
				<th scope="col" id="name" class="manage-column column-name column-primary">Plugin</th>
			</tr>
			</thead>

			<tbody id="the-list">

			<?php if( is_array( $plugins ) ){
				foreach( $plugins as $slug => $plugin ) :
					$hostPlugin = hiweb_plugins_server()->host()->plugin( $slug );
					$localPlugin = hiweb_plugins_server()->local()->plugin( $slug );
					?>
					<tr class="<?php echo $hostPlugin->is_hosted() ? 'active' : 'inactive'; ?>" data-slug="<?php echo dirname( $slug ) ?>" data-plugin="<?php echo $slug ?>">
						<th scope="row" class="check-column"><label class="screen-reader-text" for="checkbox_<?php echo md5( $slug ) ?>">Select <?php echo $hostPlugin->Name; ?></label>
							<!--<input type="checkbox" name="checked[]" value="<?php echo $slug ?>" id="checkbox_<?php echo md5( $slug ) ?>">-->
						</th>
						<td class="column-primary"><strong><?php echo $hostPlugin->Name ?></strong>
							<div class="active second plugin-version-author-uri"><?php echo $plugin->Version ?></div>

							<div class="plugin-description"><p><?php echo $plugin->Description ?></p></div>
							<div class="row-actions visible">
                            <span class="hosted">
	                            <?php
		                            if( $hostPlugin->is_exists() ) :
			                            if( $localPlugin->is_exists() ):
				                            if( $hostPlugin->is_hosted() ):
					                            echo $action_buttons['unhost'] . ' | ' . $action_buttons['remove'] . ' | ';
				                            else:
					                            echo $action_buttons['host'] . ' | ' . $action_buttons['remove'] . ' | ';
				                            endif;
				                            echo ( hiweb_plugins_server()->compare_version_local_host( $slug ) == 0 ) ? $action_buttons['reinstall'] . ' | ' : $action_buttons['install'] . ' | ';
				                            printf( $action_buttons['download'], $hostPlugin->url() );
			                            else:
				                            if( $hostPlugin->is_hosted() ):
					                            echo $action_buttons['remove'];
				                            else:
					                            echo $action_buttons['remove'];
				                            endif;
			                            endif;
		                            else:
			                            if( $localPlugin->is_exists() ):
				                            if( $hostPlugin->is_hosted() ):
					                            echo $action_buttons['unhost'];
				                            else:
					                            echo $action_buttons['put'];
				                            endif;
			                            else:
				                            //
			                            endif;
		                            endif;
	                            ?>
                            </span>
							</div>
							<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
						</td>
					</tr>
				<?php endforeach;
			} ?>
			</tbody>

			<tfoot>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><!--<label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox">--></td>
				<th scope="col" id="name" class="manage-column column-name column-primary">Plugin</th>
			</tr>
			</tfoot>

		</table>

		<!--<div class="tablenav bottom">

			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label><select name="action2" id="bulk-action-selector-bottom">
					<option value="-1">Bulk Actions</option>
					<option value="activate-selected">Activate</option>
					<option value="deactivate-selected">Deactivate</option>
					<option value="update-selected">Update</option>
					<option value="delete-selected">Delete</option>
				</select>
				<input type="submit" id="doaction2" class="button action" value="Apply">
			</div>
			<div class="tablenav-pages one-page"><span class="displaying-num">18 items</span>
<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input">1 of <span class="total-pages">1</span></span>
<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
			<br class="clear">
		</div>-->
	</form>

</div>