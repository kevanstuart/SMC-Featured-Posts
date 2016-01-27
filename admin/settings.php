<div class="wrap">
	<h1><?php esc_html_e( 'Featured Posts' , 'smcfp');?></h1>
	<p>By <a href="http://sometimesicode.com" target="_blank">Sometimes I Code</a></p>

	<form method="POST" action="options.php" novalidate="novalidate">
		<?php 
			settings_fields( 'section' );
			do_settings_sections( 'featured-posts' );
			submit_button();
		?>
	</form>
</div>