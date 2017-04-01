<?php
/**
 *
 * @since 4.0
 */
class Mooberry_Book_Manager_Options {

	//protected $options;
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
	protected $tax_grid_template;
	protected $tax_grid_slugs;
	protected $comments_on_books;
	protected $override_wpseo;
	
	
	
	
	public function __construct() {
	
		$options = $this->get_options();
		if ( !is_array( $options ) ) {
			$options = array( $options );
		}
		$this->set_placeholder_locations( $options );
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
		
		
		
		
	}
	
	protected function get_options( ) {
		return get_option('mbdb_options');
	}
	
	protected function save_options( $options ) {
		update_option( 'mbdb_options', $options );			
	}
	
	protected function add_element( $key, $array, $callback ) {
		$options = $this->get_options();
		
		if ( !array_key_exists( $key, $options ) ) {
			$options[ $key ] = array();
		}
		$options[ $key ][] = $array;
		$this->save_options( $options );
		// reset 
		if ( method_exists(  $this, $callback  ) ) {
			$this->$callback( $options );
		}
	}
		
			
	protected function set_placeholder_locations( $options ) {
		$this->placeholder_locations = $this->get_option_value( $options, 'show_placeholder_cover');
		if ( !is_array( $this->placeholder_locations ) )  {
			$this->placeholder_locations = array( $this->placeholder_locations );
		}
		
	}
	
	public function show_placeholder( $location ) {
		// always show placeholder in the book grid
		if ( $location == 'grid' ) {
			return true;
		}
		
		return ( in_array( $location, $this->placeholder_locations ) );
	}
	
	protected function set_override_wpseo( $options ) {
		$this->override_wpseo = $this->get_option_value( $options, 'override_wpseo');

		if ( !is_array( $this->override_wpseo  ) ) {
			$this->override_wpseo  = array( $this->override_wpseo  );
		}	
	}		
		
	public function override_wpseo( $setting ) {
		
		return ( in_array( $setting, $this->override_wpseo ) );
	}
	
	protected function set_comments_on_books( $options ) {
	
			$this->comments_on_books =  $this->get_option_value( $options, 'comments_on_books', true, apply_filters('mbdb_default_comments_on_books', false ) );
		
	}
			
			
	protected function set_retailers( $options) {
		
		
			$this->retailers = array();
			$retailers = $this->create_array_with_ids( $options, 'retailers', 'uniqueID' );
			foreach ( $retailers as $id => $retailer ) {
				$this->retailers[ $id ] = new Mooberry_Book_Manager_Retailer( $retailer );
			}
		
	}
	
	public function add_retailer ( $retailer ) {
		$retailer_array = array( 
							'uniqueID' => $retailer->id,
							'image' => $retailer->logo,
							'name' => $retailer->name,
							'affiliate_code' => $retailer->affiliate_code,
							'affiliate_code_position' => $retailer->affiliate_code_position,
						);
		$this->add_element( 'retailers', $retailer_array, 'set_retailers');
		
	}
	
	protected function set_publishers( $options ) {
			$this->publishers = array();
			$publishers = $this->create_array_with_ids( $options, 'publishers', 'uniqueID' );
			foreach ( $publishers as $id => $publisher ) {
				$this->publishers[ $id ] = new Mooberry_Book_Manager_Publisher( $publisher );
			}
	}
	
	public function add_publiser( $publisher ) {
		
		
		$publisher_array = array(
							'uniqueID'	=>	$publisher->id,
							'name'		=>	$publisher->name,
							'website'	=>	$publisher->website,
						);
		$this->add_element( 'publishers', $publisher_array, 'set_publishers' );
		
	}
	
	protected function set_social_media_sites( $options ) {
			$this->social_media_sites = array();
			$social_media_sites = $this->create_array_with_ids( $options, 'social_media', 'uniqueID' );
			foreach ( $social_media_sites as $id => $site ) {
				$this->social_media_sites[ $id ] = new Mooberry_Book_Manager_Social_Media_Site( $site );
			}
	}
	
	protected function set_download_formats( $options) {
			$this->download_formats = array();
			$download_formats = $this->create_array_with_ids( $options, 'formats', 'uniqueID' );
			foreach ( $download_formats as $id => $download_format ) {
				$this->download_formats[ $id ] = new Mooberry_Book_Manager_Download_Format( $download_format );
			}
	}
	
	public function add_download_format( $format ) {
		
		$format_array = array( 
							'uniqueID' => $format->id,
							'name' => $format->name,
							'image'	=> $format->logo,
						);
		$this->add_element('formats', $format_array, 'set_download_formats' );
		
	}
	
	protected function set_edition_formats( $options ) {
			$this->edition_formats = array();
			$edition_formats = $this->create_array_with_ids( $options, 'editions', 'uniqueID' );
			foreach ( $edition_formats as $id => $edition_format ) {
				$this->edition_formats[ $id ] = new Mooberry_Book_Manager_Edition_Format( $edition_format );
			}
		
	}
	
	public function add_edition_format ( $format ) {
		
		$edition_array = array( 
							'uniqueID' => $format->id,
							'name' => $format->name,
						);
		$this->add_element( 'editions', $edition_array, 'set_edition_format' );
		
	}
	
	protected function set_languages() {
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
	
	protected function set_default_language( $options ) {
			if ( array_key_exists( 'mbdb_default_language', $options ) && isset( $options[ 'mbdb_default_language' ] ) && array_key_exists( $options['mbdb_default_language'], $this->languages) ) {
			
				$this->default_language = $options[ 'mbdb_default_language' ];
			} else {
				$this->default_language = 'EN';
			}
	}
	
	protected function set_units( ) {
			$this->units = apply_filters('mbdb_get_units_array', array(
				'in'	=>	__('inches (in)', 'mooberry-book-manager'),
				'cm'	=> __('centimeters (cm)', 'mooberry-book-manager'),
				'mm'	=>	__('millimeters (mm)', 'mooberry-book-manager'),
				)
			);
		
	}
	
	protected function set_default_unit( $options ) {
			if ( array_key_exists( 'mbdb_default_unit', $options ) && isset( $options[ 'mbdb_default_unit' ] ) && array_key_exists( $options['mbdb_default_unit'], $this->units) ) {
				$this->default_unit = $options[ 'mbdb_default_unit' ];
			} else {
				$this->default_unit = 'in';
			}
	}
	
	protected function set_currencies() {
		
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
	
	protected function set_default_currency( $options ) {
			if ( array_key_exists( 'mbdb_default_currency', $options ) && isset( $options[ 'mbdb_default_currency' ] ) && array_key_exists( $options['mbdb_default_currency' ], $this->currencies ) ) {
				$this->default_currency = $options[ 'mbdb_default_currency' ];
			} else {
				$this->default_currency = 'USD';
			}
	}
	
	protected function set_currency_symbols() {
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
	
	
	protected function set_placeholder_image( $options ) {
			$this->placeholder_image = $this->get_option_value( $options, 'coming-soon', true, '' );
			if (is_ssl()) {
				$this->placeholder_image = preg_replace('/^http:/', 'https:', $this->placeholder_image);
			}
	}
	
	protected function set_book_page_template( $options ) {
		
			$this->book_page_template = $this->get_option_value( $options, 'mbdb_default_template', true, apply_filters( 'mbdb_default_page_template', 'default' ) );
		
	}
	
	protected function set_goodreads_image( $options ) {
		
			$this->goodreads_image = $this->get_option_value( $options, 'goodreads', true, '' );
		
		
	}
	
	protected function set_book_grid_default_height( $options ) {
	
			$this->book_grid_default_height = $this->get_option_value( $options,  'mbdb_default_cover_height', true, apply_filters('mbdb_default_grid_height', 200 ) );
		
	}
	
	protected function set_tax_grid_template( $options ) {
		
			$this->tax_grid_template = $this->get_option_value( $options, 'mbdb_tax_grid_template', true, apply_filters('mbdb_default_tax_grid_template', 'default' ) );
		
	}
	
	protected function set_tax_grid_slugs( $options ) {
			// get all taxonomies on a book
			$taxonomies = get_object_taxonomies('mbdb_book', 'objects' ); 
			//$taxonomies = MBDB()->book_CPT->taxonomies;
			
			foreach($taxonomies as $name => $taxonomy) {
				$id = 'mbdb_book_grid_' . $name . '_slug';
				
				// make sure each tax grid is valid
				$reserved_terms = mbdb_wp_reserved_terms();
				
				$slug = sanitize_title( $this->get_option_value( $options, $id, true, $taxonomy->labels->singular_name ) );
				if ( in_array( $slug, $reserved_terms ) ) {
						$slug = 'book-' . $slug;
				}
				$this->tax_grid_slugs[ $name ] = $slug;
			}
		
	}
	
	protected function get_tax_grid_slugs( ) {
		if ( $this->tax_grid_slugs == '' ) {
			$options = get_option('mbdb_options');
			$this->set_tax_grid_slugs( $options );
		}
		return $this->tax_grid_slugs;
	}
	
	public function get_tax_grid_slug( $taxonomy ) {
		$slugs = $this->get_tax_grid_slugs();
		if ( !array_key_exists( $taxonomy, $slugs ) ) {
			return '';
		} else {
			return $slugs[ $taxonomy ];
		}
			
	}
	
	protected function get_option_value( $options, $key, $set_default = false, $default = null ) {
		
		$value = null;
		if ( !is_array( $options ) ) {
			$options = array( $options );
		}
		if ( array_key_exists( $key, $options ) ) {
			$value = $options[ $key ];
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
	protected function create_array_with_ids( $options, $options_key, $id_key ) {
		if ( !is_array($options) ) {
			$options = array( $options );
		}
		if (array_key_exists( $options_key, $options ) ) {
			$array = $options[ $options_key ];
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

