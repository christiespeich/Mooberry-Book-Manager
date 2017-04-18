<?php
/**
 *
 * @since 4.0
 */
class Mooberry_Book_Manager_Options {

	protected $options;
	protected $retailers;
	protected $edition_formats;
	protected $download_formats;
	protected $publishers;
	protected $social_media_sites;
	protected $languages;
	protected $units;
	protected $currencies;
	protected $currency_symbols;
	protected $default_language;
	protected $default_unit;
	protected $default_currency;
	protected $placeholder_image;
	protected $placeholder_locations;
	protected $book_page_template;
	protected $goodreads_image;
	protected $book_grid_default_height;
	protected $tax_grid_page;
	protected $tax_grid_template;
	protected $tax_grid_slugs;
	protected $comments_on_books;
	protected $override_wpseo;
	
	
	
	
	public function __construct() {
		$this->options = $this->get_options();
		
	/*	$this->set_placeholder_locations( $options );
		$this->set_override_wpseo( $options );
		$this->set_comments_on_books( $options );
		$this->set_retailers( $options );
		$this->set_publishers( $options );
		$this->set_social_media_sites( $options );
		$this->set_download_formats( $options );
		$this->set_edition_formats( $options );
		$this->set_languages( );
		$this->set_default_language( $options );
		$this->set_units(  );
		$this->set_default_unit( $options );
		$this->set_currencies();
		$this->set_default_currency( $options );
		$this->set_currency_symbols();
		$this->set_placeholder_image( $options );
		$this->set_book_page_template( $options );
		$this->set_goodreads_image( $options );
		$this->set_book_grid_default_height( $options );
		$this->set_tax_grid_template( $options );
		$this->set_tax_grid_slugs( $options );
		
		*/
		
		
	}
	
	protected function get_options( ) {
		if ( $this->options == null ) {
			$this->options = get_option('mbdb_options');
		}
		if ( !is_array( $this->options ) ) {
			$this->options = array( $this->options );
		}
		return $this->options;
	}
	
	protected function save_options(  ) {
		update_option( 'mbdb_options', $this->options );			
	}
	
	protected function add_element( $key, $array) {
		
		
		if ( !array_key_exists( $key, $this->options ) ) {
			$this->options[ $key ] = array();
		}
		$this->options[ $key ][] = $array;
		$this->save_options( );
		
	}
		
	protected function get_tax_grid_page() {
		return $this->get_option_value( 'mbdb_tax_grid_page', true, '');
	}
	
	public function set_tax_grid_page( $page_id ) {
		$this->options[ 'mbdb_tax_grid_page' ] = $page_id;
		$this->save_options();
		flush_rewrite_rules();
	}
			
	protected function get_placeholder_locations(  ) {
		$placeholder_locations = $this->get_option_value(  'show_placeholder_cover');
		if ( !is_array( $placeholder_locations ) )  {
			$placeholder_locations = array( $placeholder_locations );
		}
		return $placeholder_locations;
		
	}
	
	public function show_placeholder( $location ) {
		// always show placeholder in the book grid
		if ( $location == 'grid' ) {
			return true;
		}
		$placeholder_locations = $this->get_placeholder_locations();
		return ( in_array( $location, $placeholder_locations ) );
	}
	
	protected function get_override_wpseo(  ) {
		$override_wpseo = $this->get_option_value( 'override_wpseo');

		if ( !is_array( $override_wpseo  ) ) {
			$override_wpseo  = array( $override_wpseo  );
		}	
		return $override_wpseo;
	}		
		
	public function override_wpseo( $setting ) {
		$override_wpseo = $this->get_override_wpseo();
		return ( in_array( $setting, $override_wpseo ) );
	}
	
	protected function get_comments_on_books(  ) {
	
			$comments_on_books =  $this->get_option_value(  'comments_on_books', true, apply_filters('mbdb_default_comments_on_books', false ) );
			return $comments_on_books;
		
	}
			
			
	protected function get_retailers( ) {
		$retailers = array();
		$retailer_list = $this->create_array_with_ids( 'retailers', 'uniqueID' );
		foreach ( $retailer_list as $id => $retailer ) {
			$retailers[ $id ] = new Mooberry_Book_Manager_Retailer( $retailer );
		}
		return $retailers;
	}
	
	public function add_retailer ( $retailer ) {
		$retailer_array = array( 
							'uniqueID' => $retailer->id,
							'image' => $retailer->logo,
							'name' => $retailer->name,
							'affiliate_code' => $retailer->affiliate_code,
							'affiliate_position' => $retailer->affiliate_position,
						);
		$this->add_element( 'retailers', $retailer_array);
		
	}
	
	protected function get_publishers(  ) {
		$publishers = array();
		$publisher_list = $this->create_array_with_ids(  'publishers', 'uniqueID' );
		foreach ( $publisher_list as $id => $publisher ) {
			$publishers[ $id ] = new Mooberry_Book_Manager_Publisher( $publisher );
		}
		asort($publishers);
		return $publishers;
	}
	
	public function add_publiser( $publisher ) {
		
		
		$publisher_array = array(
							'uniqueID'	=>	$publisher->id,
							'name'		=>	$publisher->name,
							'website'	=>	$publisher->website,
						);
		$this->add_element( 'publishers', $publisher_array );
		
	}
	
	protected function get_social_media_sites(  ) {
		$social_media_sites = array();
		
		$social_media_sites_list = $this->create_array_with_ids(  'social_media', 'uniqueID' );
		
		foreach ( $social_media_sites_list as $id => $site ) {
			$social_media_sites[ $id ] = new Mooberry_Book_Manager_Social_Media_Site( $site );
		}
		
		return $social_media_sites;
	}
	
	public function add_social_media_site( $site ) {
		
		$format_array = array( 
							'uniqueID' => $site->id,
							'name' => $site->name,
							'image'	=> $site->logo,
							'image_id'	=>	$site->logo_id,
						);
		$this->add_element('social_media', $format_array);
		
	}
	
	
	
	protected function get_download_formats( ) {
			$download_formats = array();
			$download_format_list = $this->create_array_with_ids( 'formats', 'uniqueID' );
			foreach ( $download_format_list as $id => $download_format ) {
				$download_formats[ $id ] = new Mooberry_Book_Manager_Download_Format( $download_format );
			}
			return $download_formats;
	}
	
	public function add_download_format( $format ) {
		
		$format_array = array( 
							'uniqueID' => $format->id,
							'name' => $format->name,
							'image'	=> $format->logo,
						);
		$this->add_element('formats', $format_array );
		
	}
	
	protected function get_edition_formats(  ) {
		$edition_formats = array();
		$edition_format_list = $this->create_array_with_ids( 'editions', 'uniqueID' );
		foreach ( $edition_format_list as $id => $edition_format ) {
			$edition_formats[ $id ] = new Mooberry_Book_Manager_Edition_Format( $edition_format );
		}
		return $edition_formats;
		
	}
	
	public function add_edition_format ( $format ) {
		
		$edition_array = array( 
							'uniqueID' => $format->id,
							'name' => $format->name,
						);
		$this->add_element( 'editions', $edition_array );
		
	}
	
	protected function get_languages() {
		if ( $this->languages == null ) {
			$this->languages = apply_filters('mbdb_get_language_array', array(
					'AB' => __('Abkhazian', 'mooberry-book-manager'),
					'AA' => __('Afar', 'mooberry-book-manager'),
					'AF' => __('Afrikaans', 'mooberry-book-manager'),
					'SQ' => __('Albanian', 'mooberry-book-manager'),
					'AM' => __('Amharic', 'mooberry-book-manager'),
					'AR' => __('Arabic', 'mooberry-book-manager'),
					'HY' => __('Armenian', 'mooberry-book-manager'),
					'AS' => __('Assamese', 'mooberry-book-manager'),
					'AY' => __('Aymara', 'mooberry-book-manager'),
					'AZ' => __('Azerbaijani', 'mooberry-book-manager'),
					'BA' => __('Bashkir', 'mooberry-book-manager'),
					'EU' => __('Basque', 'mooberry-book-manager'),
					'BN' => __('Bengali, Bangla', 'mooberry-book-manager'),
					'DZ' => __('Bhutani', 'mooberry-book-manager'),
					'BH' => __('Bihari', 'mooberry-book-manager'),
					'BI' => __('Bislama', 'mooberry-book-manager'),
					'BR' => __('Breton', 'mooberry-book-manager'),
					'BG' => __('Bulgarian', 'mooberry-book-manager'),
					'MY' => __('Burmese', 'mooberry-book-manager'),
					'BE' => __('Byelorussian', 'mooberry-book-manager'),
					'KM' => __('Cambodian', 'mooberry-book-manager'),
					'CA' => __('Catalan', 'mooberry-book-manager'),
					'ZH' => __('Chinese', 'mooberry-book-manager'),
					'CO' => __('Corsican', 'mooberry-book-manager'),
					'HR' => __('Croatian', 'mooberry-book-manager'),
					'CS' => __('Czech', 'mooberry-book-manager'),
					'DA' => __('Danish', 'mooberry-book-manager'),
					'NL' => __('Dutch', 'mooberry-book-manager'),
					'EN' => __('English', 'mooberry-book-manager'),
					'EO' => __('Esperanto', 'mooberry-book-manager'),
					'ET' => __('Estonian', 'mooberry-book-manager'),
					'FO' => __('Faeroese', 'mooberry-book-manager'),
					'FJ' => __('Fiji', 'mooberry-book-manager'),
					'FI' => __('Finnish', 'mooberry-book-manager'),
					'FR' => __('French', 'mooberry-book-manager'),
					'FY' => __('Frisian', 'mooberry-book-manager'),
					'GD' => __('Gaelic (Scots Gaelic)', 'mooberry-book-manager'),
					'GL' => __('Galician', 'mooberry-book-manager'),
					'KA' => __('Georgian', 'mooberry-book-manager'),
					'DE' => __('German', 'mooberry-book-manager'),
					'EL' => __('Greek', 'mooberry-book-manager'),
					'KL' => __('Greenlandic', 'mooberry-book-manager'),
					'GN' => __('Guarani', 'mooberry-book-manager'),
					'GU' => __('Gujarati', 'mooberry-book-manager'),
					'HA' => __('Hausa', 'mooberry-book-manager'),
					'IW' => __('Hebrew', 'mooberry-book-manager'),
					'HI' => __('Hindi', 'mooberry-book-manager'),
					'HU' => __('Hungarian', 'mooberry-book-manager'),
					'IS' => __('Icelandic', 'mooberry-book-manager'),
					'IN' => __('Indonesian', 'mooberry-book-manager'),
					'IA' => __('Interlingua', 'mooberry-book-manager'),
					'IE' => __('Interlingue', 'mooberry-book-manager'),
					'IK' => __('Inupiak', 'mooberry-book-manager'),
					'GA' => __('Irish', 'mooberry-book-manager'),
					'IT' => __('Italian', 'mooberry-book-manager'),
					'JA' => __('Japanese', 'mooberry-book-manager'),
					'JW' => __('Javanese', 'mooberry-book-manager'),
					'KN' => __('Kannada', 'mooberry-book-manager'),
					'KS' => __('Kashmiri', 'mooberry-book-manager'),
					'KK' => __('Kazakh', 'mooberry-book-manager'),
					'RW' => __('Kinyarwanda', 'mooberry-book-manager'),
					'KY' => __('Kirghiz', 'mooberry-book-manager'),
					'RN' => __('Kirundi', 'mooberry-book-manager'),
					'KO' => __('Korean', 'mooberry-book-manager'),
					'KU' => __('Kurdish', 'mooberry-book-manager'),
					'LO' => __('Laothian', 'mooberry-book-manager'),
					'LA' => __('Latin', 'mooberry-book-manager'),
					'LV' => __('Latvian, Lettish', 'mooberry-book-manager'),
					'LN' => __('Lingala', 'mooberry-book-manager'),
					'LT' => __('Lithuanian', 'mooberry-book-manager'),
					'MK' => __('Macedonian', 'mooberry-book-manager'),
					'MG' => __('Malagasy', 'mooberry-book-manager'),
					'MS' => __('Malay', 'mooberry-book-manager'),
					'ML' => __('Malayalam', 'mooberry-book-manager'),
					'MT' => __('Maltese', 'mooberry-book-manager'),
					'MI' => __('Maori', 'mooberry-book-manager'),
					'MR' => __('Marathi', 'mooberry-book-manager'),
					'MO' => __('Moldavian', 'mooberry-book-manager'),
					'MN' => __('Mongolian', 'mooberry-book-manager'),
					'NA' => __('Nauru', 'mooberry-book-manager'),
					'NE' => __('Nepali', 'mooberry-book-manager'),
					'NO' => __('Norwegian', 'mooberry-book-manager'),
					'OC' => __('Occitan', 'mooberry-book-manager'),
					'OR' => __('Oriya', 'mooberry-book-manager'),
					'OM' => __('Oromo, Afan', 'mooberry-book-manager'),
					'PS' => __('Pashto, Pushto', 'mooberry-book-manager'),
					'FA' => __('Persian', 'mooberry-book-manager'),
					'PL' => __('Polish', 'mooberry-book-manager'),
					'PT' => __('Portuguese', 'mooberry-book-manager'),
					'PA' => __('Punjabi', 'mooberry-book-manager'),
					'QU' => __('Quechua', 'mooberry-book-manager'),
					'RM' => __('Rhaeto-Romance', 'mooberry-book-manager'),
					'RO' => __('Romanian', 'mooberry-book-manager'),
					'RU' => __('Russian', 'mooberry-book-manager'),
					'SM' => __('Samoan', 'mooberry-book-manager'),
					'SG' => __('Sangro', 'mooberry-book-manager'),
					'SA' => __('Sanskrit', 'mooberry-book-manager'),
					'SR' => __('Serbian', 'mooberry-book-manager'),
					'SH' => __('Serbo-Croatian', 'mooberry-book-manager'),
					'ST' => __('Sesotho', 'mooberry-book-manager'),
					'TN' => __('Setswana', 'mooberry-book-manager'),
					'SN' => __('Shona', 'mooberry-book-manager'),
					'SD' => __('Sindhi', 'mooberry-book-manager'),
					'SI' => __('Singhalese', 'mooberry-book-manager'),
					'SS' => __('Siswati', 'mooberry-book-manager'),
					'SK' => __('Slovak', 'mooberry-book-manager'),
					'SL' => __('Slovenian', 'mooberry-book-manager'),
					'SO' => __('Somali', 'mooberry-book-manager'),
					'ES' => __('Spanish', 'mooberry-book-manager'),
					'SU' => __('Sudanese', 'mooberry-book-manager'),
					'SW' => __('Swahili', 'mooberry-book-manager'),
					'SV' => __('Swedish', 'mooberry-book-manager'),
					'TL' => __('Tagalog', 'mooberry-book-manager'),
					'TG' => __('Tajik', 'mooberry-book-manager'),
					'TA' => __('Tamil', 'mooberry-book-manager'),
					'TT' => __('Tatar', 'mooberry-book-manager'),
					'TE' => __('Tegulu', 'mooberry-book-manager'),
					'TH' => __('Thai', 'mooberry-book-manager'),
					'BO' => __('Tibetan', 'mooberry-book-manager'),
					'TI' => __('Tigrinya', 'mooberry-book-manager'),
					'TO' => __('Tonga', 'mooberry-book-manager'),
					'TS' => __('Tsonga', 'mooberry-book-manager'),
					'TR' => __('Turkish', 'mooberry-book-manager'),
					'TK' => __('Turkmen', 'mooberry-book-manager'),
					'TW' => __('Twi', 'mooberry-book-manager'),
					'UK' => __('Ukrainian', 'mooberry-book-manager'),
					'UR' => __('Urdu', 'mooberry-book-manager'),
					'UZ' => __('Uzbek', 'mooberry-book-manager'),
					'VI' => __('Vietnamese', 'mooberry-book-manager'),
					'VO' => __('Volapuk', 'mooberry-book-manager'),
					'CY' => __('Welsh', 'mooberry-book-manager'),
					'WO' => __('Wolof', 'mooberry-book-manager'),
					'XH' => __('Xhosa', 'mooberry-book-manager'),
					'JI' => __('Yiddish', 'mooberry-book-manager'),
					'YO' => __('Yoruba', 'mooberry-book-manager'),
					'ZU' => __('Zulu', 'mooberry-book-manager'), 
				)
			);
		}
		return $this->languages;
		
	}
	
	protected function get_default_language(  ) {
		
			if ( array_key_exists( 'mbdb_default_language', $this->options ) && isset( $options[ 'mbdb_default_language' ] ) && array_key_exists( $this->options['mbdb_default_language'], $this->languages) ) {
			
				return $this->options[ 'mbdb_default_language' ];
			} else {
				return 'EN';
			}
	}
	
	protected function get_units( ) {
		if ( $this->units == null ) {
			$this->units = apply_filters('mbdb_get_units_array', array(
				'in'	=>	__('inches (in)', 'mooberry-book-manager'),
				'cm'	=> __('centimeters (cm)', 'mooberry-book-manager'),
				'mm'	=>	__('millimeters (mm)', 'mooberry-book-manager'),
				)
			);
		}
		return $this->units;
		
	}
	
	protected function get_default_unit(  ) {
	
		if ( array_key_exists( 'mbdb_default_unit', $this->options ) && isset( $options[ 'mbdb_default_unit' ] ) && array_key_exists( $this->options['mbdb_default_unit'], $this->units) ) {
			return $options[ 'mbdb_default_unit' ];
		} else {
			return 'in';
		}
	}
	
	protected function get_currencies() {
		if ( $this->currencies == null ) {
			$this->currencies = apply_filters('mbdb_get_currency_array', array(
					'AUD'   => __('Australian Dollar', 'mooberry-book-manager'),
					'BRL'   => __('Brazilian Real ', 'mooberry-book-manager'),
					'CAD'   => __('Canadian Dollar', 'mooberry-book-manager'),
					'CZK'   => __('Czech Koruna', 'mooberry-book-manager'),
					'DKK'   => __('Danish Krone', 'mooberry-book-manager'),
					'EUR'   => __('Euro', 'mooberry-book-manager'),
					'HKD'   => __('Hong Kong Dollar', 'mooberry-book-manager'),
					'HUF'   => __('Hungarian Forint', 'mooberry-book-manager'),
					'INR'	=> __('Indian Rupee', 'mooberry-book-manager'),
					'ILS'   => __('Israeli New Sheqel', 'mooberry-book-manager'),
					'JPY'   => __('Japanese Yen', 'mooberry-book-manager'),
					'MYR'   => __('Malaysian Ringgit', 'mooberry-book-manager'),
					'MXN'   => __('Mexican Peso', 'mooberry-book-manager'),
					'NGN'	=> __('Nigerian Naira', 'mooberry-book-manager'),
					'NOK'   => __('Norwegian Krone', 'mooberry-book-manager'),
					'NZD'   => __('New Zealand Dollar', 'mooberry-book-manager'),
					'PHP'   => __('Philippine Peso', 'mooberry-book-manager'),
					'PLN'   => __('Polish Zloty', 'mooberry-book-manager'),
					'RUB'	=> __('Russian Rube', 'mooberry-book-manager'),
					'SGD'   => __('Singapore Dollar', 'mooberry-book-manager'),
					'ZAR'   => __('South African Rand', 'mooberry-book-manager'),
					'SEK'   => __('Swedish Krona', 'mooberry-book-manager'),
					'CHF'   => __('Swiss Franc', 'mooberry-book-manager'),
					'TWD'   => __('Taiwan New Dollar', 'mooberry-book-manager'),
					'THB'   => __('Thai Baht', 'mooberry-book-manager'),
					'TRY'   => __('Turkish Lira', 'mooberry-book-manager'),
					'GBP'   => __('U.K. Pound Sterling', 'mooberry-book-manager'),
					'USD'   => __('U.S. Dollar', 'mooberry-book-manager'),
				)	
			);
		}
		return $this->currencies;
		
	}
	
	protected function get_default_currency( ) {
		
			if ( array_key_exists( 'mbdb_default_currency', $this->options ) && isset( $this->options[ 'mbdb_default_currency' ] ) && array_key_exists( $this->options['mbdb_default_currency' ], $this->currencies ) ) {
				return $this->options[ 'mbdb_default_currency' ];
			} else {
				return 'USD';
			}
		
		
	}
	
	protected function get_currency_symbols() {
		if ( $this->currency_symbols == null ) {
			$this->currency_symbols = apply_filters('mbdb_get_currency_symbol_array', array(
				'AUD'   => '$',
				'BRL'   => 'R$',
				'CAD'   => '$',
				'CZK'   => 'Kč',
				'DKK'   => 'kr',
				'EUR'   => '€',
				'HKD'   => '$',
				'HUF'   => 'Ft',
				'INR'	=> '₹',
				'ILS'   => '₪',
				'JPY'   => '¥',
				'MYR'   => 'RM',
				'MXN'   => '$',
				'NGN'	=> '₦',
				'NOK'   => 'kr',
				'NZD'   => '$',
				'PHP'   => '₱',
				'PLN'   => 'zł',
				'RUB'	=> '₽',
				'GBP'   => '£',
				'SGD'   => '$',
				'ZAR'	=> 'R',
				'SEK'   => 'kr',
				'CHF'   => 'CHF',
				'TWD'   => 'NT$',
				'THB'   => '฿',
				'TRY'   => '₤',
				'USD'   => '$',
				)
			);
		}
		return $this->currency_symbols;
		
	}
	
	
	protected function get_placeholder_image( ) {
			$placeholder_image = $this->get_option_value( 'coming-soon', true, '' );
			if ( $placeholder_image != ''  && is_ssl()) {
				$placeholder_image = preg_replace('/^http:/', 'https:', $placeholder_image);
			}
			return $placeholder_image;
	}
	
	protected function get_book_page_template(  ) {
		
			return $this->get_option_value(  'mbdb_default_template', true, apply_filters( 'mbdb_default_page_template', 'default' ) );
		
	}
	
	protected function get_goodreads_image(  ) {
		
			return $this->get_option_value(  'goodreads', true, '' );
		
		
	}
	
	protected function get_book_grid_default_height(  ) {
	
			return $this->get_option_value(  'mbdb_default_cover_height', true, apply_filters('mbdb_default_grid_height', 200 ) );
		
	}
	
	protected function get_tax_grid_template(  ) {
		
			return $this->get_option_value( 'mbdb_tax_grid_template', true, apply_filters('mbdb_default_tax_grid_template', 'default' ) );
		
	}
	
	protected function get_tax_grid_slugs( ) {
		// get all taxonomies on a book
		$taxonomies = get_object_taxonomies('mbdb_book', 'objects' ); 
		//$taxonomies = MBDB()->book_CPT->taxonomies;
		
		foreach($taxonomies as $name => $taxonomy) {
			$id = 'mbdb_book_grid_' . $name . '_slug';
			
			// make sure each tax grid is valid
			$reserved_terms = mbdb_wp_reserved_terms();
			
			$slug = sanitize_title( $this->get_option_value( $id, true, $taxonomy->labels->singular_name ) );
			if ( in_array( $slug, $reserved_terms ) ) {
					$slug = 'book-' . $slug;
			}
			$tax_grid_slugs[ $name ] = $slug;
		}
		return $tax_grid_slugs;
	}
	/*
	protected function get_tax_grid_slugs( ) {
		if ( $this->tax_grid_slugs == '' ) {
			$options = get_option('mbdb_options');
			$this->set_tax_grid_slugs( $options );
		}
		return $this->tax_grid_slugs;
	}
	*/
	public function get_tax_grid_slug( $taxonomy ) {
		$slugs = $this->get_tax_grid_slugs();
		if ( !array_key_exists( $taxonomy, $slugs ) ) {
			return '';
		} else {
			return $slugs[ $taxonomy ];
		}
			
	}
	
	protected function get_option_value(  $key, $set_default = false, $default = null ) {
		
		$value = null;
		if ( !is_array( $this->options ) ) {
			$this->options = array( $this->options );
		}
		if ( array_key_exists( $key, $this->options ) ) {
			$value = $this->options[ $key ];
		} else {
			if ( $set_default ) {
				$value = $default;
			} 
		}
		return $value;
	}
	
	// turn the publishers array from [0]['unqiueID'] = '',
	//								  [0]['name'] = '',
	//								  [0]['link'] = ''
	// into array like this			[uniqueID] => { ['name'],
	//												['link'], }
	//			
	protected function create_array_with_ids(  $options_key, $id_key ) {
		if ( !is_array($this->options) ) {
			$this->options = array( $this->options );
		}
		/* if ( $options_key == 'social_media' ) {
		print_r( array_key_exists( $options_key, $this->options ) );
		print_r(array_column(  $this->options[ $options_key ], $id_key ) );
		} */
		if (array_key_exists( $options_key, $this->options ) ) {
			$array = $this->options[ $options_key ];
			// get an array of uniqueIDs
			$keys = array_column( $array, $id_key );
			// map uniqueIDs to the rest of the publisher info
			return array_combine( $keys, $array );
		} else {
			return array();
		}
	}
	
	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	public function __get( $key ) {

		if( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {
			
			if ( property_exists( $this, $key ) ) {
				
				$ungettable_properties = array(  );
				
				if ( !in_array( $key, $ungettable_properties ) ) {
				
					return $this->$key;

				}
		
			}
		
		}
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t get property %s', 'mooberry-book-manager' ), $key ) );
				
	}
	
	/**
	 * Magic __set function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 */
	public function __set( $key, $value ) {
	
		// this class does not set any properties and does not write anything to the 
		// database. Changes to the options are done through the settings pages
	
		return new WP_Error( 'mbdb-invalid-property', sprintf( __( 'Can\'t set property %s', 'mooberry-book-manager' ), $key ) );
		
	}
	
	
}

