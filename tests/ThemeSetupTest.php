<?php
/**
 * Tests for theme setup: theme supports, menus, sidebars, scripts.
 *
 * @package Neurg_Kreisverband
 */

class ThemeSetupTest extends WP_UnitTestCase {

    public function test_theme_is_active() {
        $this->assertEquals( 'theme', get_stylesheet() );
    }

    public function test_theme_supports_post_thumbnails() {
        $this->assertTrue( current_theme_supports( 'post-thumbnails' ) );
    }

    public function test_theme_supports_title_tag() {
        $this->assertTrue( current_theme_supports( 'title-tag' ) );
    }

    public function test_theme_supports_automatic_feed_links() {
        $this->assertTrue( current_theme_supports( 'automatic-feed-links' ) );
    }

    public function test_nav_menus_registered() {
        $locations = get_registered_nav_menus();
        $this->assertArrayHasKey( 'nav-main', $locations );
        $this->assertArrayHasKey( 'nav-footer', $locations );
        $this->assertArrayHasKey( 'nav-mobile', $locations );
        $this->assertArrayHasKey( 'nav-sozial', $locations );
    }

    public function test_sidebars_registered() {
        global $wp_registered_sidebars;
        $this->assertArrayHasKey( 'infospalte', $wp_registered_sidebars );
        $this->assertArrayHasKey( 'hometeaser', $wp_registered_sidebars );
        $this->assertArrayHasKey( 'homeone', $wp_registered_sidebars );
        $this->assertArrayHasKey( 'hometwo', $wp_registered_sidebars );
        $this->assertArrayHasKey( 'presse', $wp_registered_sidebars );
        $this->assertArrayHasKey( 'fussleiste', $wp_registered_sidebars );
    }

    public function test_custom_image_sizes_registered() {
        $sizes = wp_get_additional_image_sizes();
        $this->assertArrayHasKey( 'titelbild', $sizes );
        $this->assertArrayHasKey( 'listenansicht', $sizes );
        $this->assertArrayHasKey( '350uncropped', $sizes );
    }

    public function test_content_width_is_set() {
        global $content_width;
        $this->assertEquals( 783, $content_width );
    }

    public function test_gk_version_constant_defined() {
        $this->assertTrue( defined( 'GK_VERSION' ) );
        $this->assertMatchesRegularExpression( '/^\d+\.\d+\.\d+$/', GK_VERSION );
    }

    public function test_gk_dir_constant_defined() {
        $this->assertTrue( defined( 'GK_DIR' ) );
    }

    public function test_gk_uri_constant_defined() {
        $this->assertTrue( defined( 'GK_URI' ) );
    }
}
