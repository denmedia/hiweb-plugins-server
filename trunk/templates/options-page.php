<?php _hw_plugins_server_script( '/templates/options-page' ); ?>
<div id="hw_plugins_server_options">
	<h1>hiWeb Plugins Client / Server Settings</h1>
	
	<table class="form-table">
		<tbody>
		
		<tr>
			<th scope="row">
				Client Status : <code><?php echo hiweb_plugins_server()->remote()->status( true ); ?></code>
				<p class="description">A client that connects to the server. After connecting the plug-ins can be downloaded from the remote server.</p>
			</th>
			<td id="front-static-pages">
				<input placeholder="http://example.com" name="hw_plugins_server_remote_url" value="<?php echo get_option( HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, '' ); ?>"/>
				<button class="button button-primary" id="hw_plugins_server_remote_url_update">UPDATE</button>
				<?php if( hiweb_plugins_server()->remote()->status() == 1 ): ?>
					<a class="button" href="<?php echo self_admin_url( 'plugins.php?page=' . HW_PLUGINS_SERVER_REMOTE_PAGE_SLUG ) ?>">Plugin download's Page</a>
				<?php endif; ?>
				<p class="description">Enter server address, after that press "Update Button"</p>
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				Server Status : <code><?php echo hiweb_plugins_server()->host()->status() ? 'ON' : 'OFF' ?></code>
				<p class="description">Server. can connect to other customers (other plugins hiWeb Plugins Server) from remote sites to the server.</p>
			</th>
			<td id="front-static-pages">
				<?php if( hiweb_plugins_server()->host()->status() ) : ?>
					<button class="button" id="hw_plugins_server_status_toggle">STOP LOCAL SERVER</button>
					<a href="admin.php?page=<?php echo HW_PLUGINS_SERVER_PAGE_SLUG ?>" class="button button-primary">ADMIN SERVER</a>
				<?php else: ?>
					<button class="button button-primary" id="hw_plugins_server_status_toggle">START LOCAL SERVER</button>
				<?php endif; ?>
				<p class="description">Press on button for start / stop server</p>
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				Kickback. Allow to take remote plugins : <code><?php echo hiweb_plugins_server()->host()->kickback_status() ? 'ENABLE' : 'DISABLE' ?></code>
				<p class="description">Allow remote clients to upload their plugins. These plug-ins will be packaged in the archive file and can not be started automatically or via the URL-request. To unpack the plugin, you must go to the section for
					unpacking and installing plug-ins.</p>
			</th>
			<td id="front-static-pages">
				<?php if( !hiweb_plugins_server()->host()->status() ) : ?>
					<button class="button button-primary-disabled" disabled>Unable to enable.</button>
					<p class="description">Start server first, after that enable KICKBACK</p>
				<?php else: ?>
					<?php if( !hiweb_plugins_server()->host()->kickback_status() ) : ?>
						<button class="button button-primary" id="hw_plugins_server_kickback_status_toggle">ENABLE KICKBACK</button>
						<p class="description">Press on button for enable / disable server kickback</p>
					<?php else: ?>
						<button class="button" id="hw_plugins_server_kickback_status_toggle">DISABLE KICKBACK</button>
						<p class="description">Press on button for enable / disable server kickback</p>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
		
		</tbody>
	</table>
</div>