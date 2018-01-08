<?php

class Test_Foyer_Slide_Backgrounds extends Foyer_UnitTestCase {

	function test_is_default_slide_background_registered() {
		$slide_background = Foyer_Slides::get_slide_background_by_slug( 'default' );
		$this->assertNotEmpty( $slide_background );
	}

	function test_is_image_slide_background_registered() {
		$slide_background = Foyer_Slides::get_slide_background_by_slug( 'image' );
		$this->assertNotEmpty( $slide_background );
	}

}