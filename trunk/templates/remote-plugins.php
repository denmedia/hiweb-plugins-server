<?php _hw_plugins_server_script( '/templates/remote-plugins' ); ?>
<div class="wrap">
	<h1>REMOTE PLUGINS</h1>
	
	
	<?php

		$plugins = hiweb_plugins_server()->remote()->plugins();

		if( count( $plugins ) > 0 ) : ?>
			
			<p>Download, Install and Activate You'r Plugins...</p>
			
			<table class="wp-list-table widefat plugins">
			<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><!--<label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox">--></td>
				<th scope="col" id="name" class="manage-column column-name column-primary">Plugin</th>
				<th scope="col" id="description" class="manage-column column-description">Description</th>
			</tr>
			</thead>
			
			<tbody id="the-list">
			
			<?php

				foreach( $plugins as $slug => $plugin ){
					$isActive = hiweb_plugins_server()->local()->plugin( $slug )->is_active();
					$isExists = hiweb_plugins_server()->local()->plugin( $slug )->is_exists();
					$currentVersion = hiweb_plugins_server()->local()->plugin( $slug )->Version;
					$isUpdate = ( hiweb_plugins_server()->compare_version_local_host( $slug ) === 1 );
					$trClass = array();
					$trClass[] = $isExists ? 'active' : 'inactive';
					if( $isUpdate ){
						$trClass[] = 'update';
					}
					?>
					<tr class="<?php echo implode( ' ', $trClass ); ?>" data-slug="<?php echo dirname( $slug ) ?>" data-plugin="<?php echo $slug ?>">
						<th scope="row" class="check-column"><label class="screen-reader-text" for="checkbox_<?php echo md5( $slug ) ?>">Select <?php echo $plugin->Name; ?></label>
							<!--<input type="checkbox" name="checked[]" value="<?php echo $slug ?>" id="checkbox_<?php echo md5( $slug ) ?>">-->
						</th>
						<td class="plugin-title column-primary"><strong><?php echo $plugin->Name; ?></strong>
							<div class="active second plugin-version-author-uri"><?php echo $plugin->Version; ?></div>
							<div class="row-actions visible">
                            <span class="hosted">
                                <?php if( $isExists ) : ?>
	                                <?php if( $isActive ): ?>
		                                <?php if( $isUpdate ): ?>
			                                <a href="#" data-click="download"><i class="dashicons dashicons-update"></i> Update</a> |
                                            <a href="#" data-click="deactivate"><i class="dashicons dashicons-marker"></i> Deactiate</a> |
                                            <a href="#" data-click="remove"><i class="dashicons dashicons-no"></i> Remove From Site</a>
		                                <?php else : ?>
			                                <a href="#" data-click="deactivate"><i class="dashicons dashicons-marker"></i> Deactiate</a> |
                                            <a href="#" data-click="remove"><i class="dashicons dashicons-no"></i> Remove From Site</a>
		                                <?php endif; ?>
	                                <?php else : ?>
		                                <?php if( $isUpdate ): ?>
			                                <a href="#" data-click="download"><i class="dashicons dashicons-update"></i> Update</a> |
                                            <a href="#" data-click="remove"><i class="dashicons dashicons-no"></i> Remove From Site</a>
		                                <?php else : ?>
			                                <a href="#" data-click="activate"><i class="dashicons dashicons-yes"></i> Activate</a> |
                                            <a href="#" data-click="remove"><i class="dashicons dashicons-no"></i> Remove From Site</a>
		                                <?php endif; ?>
	                                <?php endif; ?>
                                <?php else : ?>
	                                <a href="#" data-click="activate"><i class="dashicons dashicons-admin-plugins"></i> Download & Activate</a> |
                                    <a href="#" data-click="download"><i class="dashicons dashicons-download"></i> Download To Site</a>
                                <?php endif ?>
                            </span>
							</div>
							<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
						</td>
						<td class="column-description desc">
							<div class="plugin-description"><p><?php echo $plugin->Description; ?></p></div>
						</td>
					</tr>
					<?php if( $isUpdate ): ?>
						<tr class="plugin-update-tr <?php echo implode( ' ', $trClass ); ?>">
							<td colspan="3" class="plugin-update colspanchange">
								<div class="update-message notice inline notice-warning notice-alt"><p>
										Update Current version <?php echo $currentVersion ?> → <b><?php echo $plugin->Version; ?></b> | Archive Date: <b><?php echo date( 'Y.m.d - H:i', $plugin->filemtime ) ?></b>
									</p></div>
							</td>
						</tr>
					<?php endif;
				}
			?>
			</tbody>
			
			<tfoot>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><!--<label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox">--></td>
				<th scope="col" id="name" class="manage-column column-name column-primary">Plugin</th>
				<th scope="col" id="description" class="manage-column column-description">Description</th>
			</tr>
			</tfoot>
			
			</table><?php
		else :
			?><h2>Unable to get the list of plugins</h2><h3>Reason: <?php echo hiweb_plugins_server()->remote()->status( true ); ?></h3><?php
		endif;

	?>


</div>
