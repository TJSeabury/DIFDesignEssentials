<?php namespace DIFDesign;

class Difdesign
{
	private $themePath = '';
	private $themeUri = '';
	private $initTime = 0;
	
	public function __construct( string $themeRootPath, string $themeRootUri, $Time )
	{
		$this->themePath = $themeRootPath;
		$this->themeUri = $themeRootUri;
		$this->initTime = $Time;
	}
	
	/*
	* Initializes Wordpress admin theme menu.
	*/
	public function initAdmin()
	{
		add_action( 'admin_init', function()
	    {
			add_settings_section( 
				'primary_settings',
				'Primary Settings',
				function( $arg )
			    {
					?>
					<p>Super important settings.</p>
					<?php
				},
				'difdesign'
			);
			
			/*
			* Theme Mode
			*/
			register_setting( 
				'difdesign_options', 
				'difdesign_theme_mode' 
			);
			add_settings_field(
				'difdesign_theme_mode',
				'Theme Mode',
				function( $args )
				{
				?>
					<label for="<?php echo $args['id']; ?>">
						<input type="checkbox" id="<?php echo $args['id']; ?>" name="<?php echo $args['id']; ?>" value="1" <?php checked( '1', get_option('difdesign_theme_mode') ); ?> />
						Live
					</labe>
				<?php
				},
				'difdesign',
				'primary_settings',
				array(
					'id' => 'difdesign_theme_mode'
				)
			);
			
			/*
			* Primary Color
			*/
			register_setting(
				'difdesign_options',
				'difdesign_primary_color'
			);
			add_settings_field(
				'difdesign_primary_color',
				'Primary Color',
				function( $args )
				{
				?>
					<div style="display:inline-block;width:25px;height:25px;margin:2px 0 0 0;background-color:<?php echo get_option('difdesign_primary_color'); ?>;vertical-align:top;"></div>
					<input type="text" id="<?php echo $args['id']; ?>" name="<?php echo $args['id']; ?>" value="<?php echo get_option('difdesign_primary_color'); ?>" />
				<?php
				},
				'difdesign',
				'primary_settings',
				array(
					'id' => 'difdesign_primary_color'
				)
			);
			
			/*
			* Minify css
			*/
			register_setting(
				'difdesign_options',
				'difdesign_minify_css'
			);
			add_settings_field(
				'difdesign_minify_css',
				'Minify CSS',
				function( $args )
				{
					?>
						<label for="<?php echo $args['id']; ?>">
							<input type="checkbox" id="<?php echo $args['id']; ?>" name="<?php echo $args['id']; ?>" value="1" <?php checked( '1', get_option('difdesign_minify_css') ); ?> />
							Enable
						</label>
					<?php
				},
				'difdesign',
				'primary_settings',
				array(
					'id' => 'difdesign_minify_css'
				)
			);
			
			/*
			* Ajax Shortcodes Interface
			*/
			register_setting( 
				'difdesign_options', 
				'difdesign_interface_ajax_shortcodes' 
			);
			add_settings_field(
				'difdesign_interface_ajax_shortcodes',
				'Ajax Shortcodes Interface',
				function( $args )
				{
				?>
					<label for="<?php echo $args['id']; ?>">
						<input type="checkbox" id="<?php echo $args['id']; ?>" name="<?php echo $args['id']; ?>" value="1" <?php checked( '1', get_option('difdesign_interface_ajax_shortcodes') ); ?> />
						Enable
					</labe>
				<?php
				},
				'difdesign',
				'primary_settings',
				array(
					'id' => 'difdesign_interface_ajax_shortcodes'
				)
			);
			
		} );
		
		add_action( 'admin_menu', function()
		{
			add_menu_page(
				'DIF Design Theme Options',
				'Theme Options',
				'manage_options',
				'difdesign',
				function()
				{
					// check user capabilities
					if ( !current_user_can( 'manage_options' ) )
					{
						return;
					}
					?>
					<div class="wrap">
						<h1><?= esc_html( get_admin_page_title() ); ?></h1>
						<p>Various options to toggle theme functinos and components.</p>
						<form action="options.php" method="post">
							<?php
							// output security fields for the registered setting "difdesign_options"
							settings_fields( 'difdesign_options' );
							// output setting sections and their fields
							// (sections are registered for "difdesign", each field is registered to a specific section)
							do_settings_sections( 'difdesign' );
							// output save settings button
							submit_button( 'Save Settings' );
							?>
						</form>
					</div>
					<?php
				},
				$this->themeUri . '/admin/img/difdesign-logo.png',
				2
			);
		} );
		
	}
	
	/*
	* Initializes theme styles.
	*/
	public function initStyles( string $readPath, string $writePath, string $filename )
	{
		add_action( 'after_setup_theme', function() use( $readPath, $writePath, $filename )
		{
			$currentCss = $this->themePath . $writePath . $filename;
			
			$cssModulePaths = utils\AggregatorCss::getFiles(
				$this->themePath . $readPath,
				true
			);
			
			if ( file_exists( $currentCss ) )
			{
				$areNewFiles = utils\FileVersion::comparator( $currentCss, $cssModulePaths );
			}
			else
			{
				$areNewFiles = true;
			}
			
			if ( $areNewFiles )
			{
				$css = utils\AggregatorCss::agg(
					utils\AggregatorCss::getFiles(
						$this->themePath . $readPath,
						false
					)
				);
				if ( (bool)get_option('difdesign_minify_css') )
				{
					$css = utils\AggregatorCss::minify( $css );
				}
				utils\AggregatorCss::write(
					$css,
					$this->themePath . $writePath,
					$filename
				);
			}
		} );
		
		add_action( 'update_option_' . 'difdesign_minify_css', function() use( $readPath, $writePath, $filename )
		{
			$css = utils\AggregatorCss::agg(
				utils\AggregatorCss::getFiles(
					$this->themePath . $readPath,
					false
				)
			);
			if ( (bool)get_option('difdesign_minify_css') )
			{
				$css = utils\AggregatorCss::minify( $css );
			}
			utils\AggregatorCss::write(
				$css,
				$this->themePath . $writePath,
				$filename
			);
		} ); 
		
		add_action( 'wp_enqueue_scripts', function() use( $writePath, $filename )
		{
			wp_enqueue_style( 'difdesign-aggregate-minified-styles', $this->themeUri . $writePath . $filename, array( 'avada-stylesheet' ) );
		}, 3 );
		
	}
	
	/*
	* Initialize theme scripts
	*/
	public function initScripts()
	{
		add_action( 'wp_enqueue_scripts', function()
		{
			$coreJsPath = $this->themeUri . '/public/js/difdesigncoreutilities.js';
			wp_register_script( 'DIFDesignCoreJS', $coreJsPath );
			wp_localize_script( 'DIFDesignCoreJS', 'wpMeta', array( 'siteURL' => get_option( 'siteurl' ) ) );
			wp_enqueue_script( 'DIFDesignCoreJS', $coreJsPath, array(), utils\FileVersion::getVersion( $coreJsPath ), true);
		}, 2 );
		add_action( 'wp_enqueue_scripts', function()
		{
			$mainJsPath = $this->themeUri . '/public/js/main.js';
			wp_enqueue_script( 'DIFDesignMainJS', $mainJsPath, array(), utils\FileVersion::getVersion( $mainJsPath ), true);
		}, 1 );
	}
	
	
	
}