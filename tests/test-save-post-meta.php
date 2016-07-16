<?php
class Simplechart_Test_Save_Post_Meta extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();
		$this->post_id =  $this->factory->post->create();
	}

	/**
	 * Handle multiline csv input
	 */
	function test_save_rawData() {
		$raw_data = file_get_contents( dirname( __FILE__ ) . '/data/testcsv.txt' );
		$sanitized_data = esc_textarea( strip_tags( $raw_data ) );
		update_post_meta( $this->post_id, 'save-rawData', $sanitized_data );
		$retrieved_data = get_post_meta( $this->post_id, 'save-rawData', true );
		$this->assertSame( $raw_data, $retrieved_data );
	}

	/**
	 * Handle base64 ping image URI
	 */
	function test_save_previewImg() {
		$raw_data = file_get_contents( dirname( __FILE__ ) . '/data/testpngdata.txt' );
		update_post_meta( $this->post_id, 'save-previewImg', sanitize_text_field( $raw_data ) );
		$retrieved_data = get_post_meta( $this->post_id, 'save-previewImg', true );
		$this->assertSame( $raw_data, $retrieved_data );
	}

	/**
	 * Handle JSON data
	 */
	function _test_save_json( $path ) {
		$raw_data = file_get_contents( dirname( __FILE__ ) . $path );

		// input must be valid JSON when unslashed
		$this->assertTrue( !!json_decode( wp_unslash( $raw_data ) ) );

		// sanitize and store data
		$sanitized_data = sanitize_text_field( wp_unslash( $raw_data ) );
		update_post_meta( $this->post_id, 'save-previewImg', $sanitized_data );

		// retrieve data
		$retrieved_data = get_post_meta( $this->post_id, 'save-previewImg', true );
		$this->assertJsonStringEqualsJsonString( wp_unslash( $raw_data ), $retrieved_data );
	}

	function test_save_json() {
		$this->_test_save_json( '/data/testjson.txt' );
		$this->_test_save_json( '/data/testjson2.txt' );
	}
}
