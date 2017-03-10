<?php
/**
 * Default slide template.
 *
 * @since	1.0.0
 */

$slide = new Foyer_Slide( get_the_id() );

?><div<?php $slide->class(); ?><?php $slide->data();?>>
	<div class="inner">
		<figure>
			<img src="<?php echo $slide->image(); ?>" />
		</figure>
	</div>
</div>