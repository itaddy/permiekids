<?php
/**
 * This file contains data sets for country states/provinces
 * @package IT_Exchagne
 * @since 1.2.0
 */

/**
 * Grabs the array we want based on country
 *
 * @since 1.2.0
 *
 * @param array $options
*/
function it_exchange_get_country_states( $options=array() ) {
	// Defaults
	$defaults = array(
		'country' => false,
	);

	$options = ITUtility::merge_defaults( $options, $defaults );

	// Core supports the following countries
	$supported_countries = array(
		'AU' => 'it_exchange_get_country_states_for_au',
		'CA' => 'it_exchange_get_country_states_for_ca',
		'DE' => 'it_exchange_get_country_states_for_de',
		'ES' => 'it_exchange_get_country_states_for_es',
		'FR' => 'it_exchange_get_country_states_for_fr',
		'NL' => 'it_exchange_get_country_states_for_nl',
		'US' => 'it_exchange_get_country_states_for_us',
		'ZA' => 'it_exchange_get_country_states_for_za',
	);
	$supported_countries = apply_filters( 'it_exchange_get_country_states_supported_countries', $supported_countries );

	// Return the state data if its supported and we can find a callable function
	if ( ! empty( $options['country'] ) && isset( $supported_countries[$options['country']] ) && is_callable( $supported_countries[$options['country']] ) )
		return call_user_func( $supported_countries[$options['country']], $options );

	return false;
}

/**
 * Returns an array of states for AU
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_au( $options=array() ) {
	// Defaults
	$defaults = array(
		'include-territories'             => true,
		'sort-territories-alphabetically' => false,
	);

	$options = ITUtility::merge_defaults( $options, $defaults );
	// States
	$states = array(
		'NSW' => __( 'New South Wales', 'it-l10n-ithemes-exchange' ),
		'QLD' => __( 'Queensland', 'it-l10n-ithemes-exchange' ),
		'SA'  => __( 'South Australia', 'it-l10n-ithemes-exchange' ),
		'TAS' => __( 'Tasmania', 'it-l10n-ithemes-exchange' ),
		'VIC' => __( 'Victoria', 'it-l10n-ithemes-exchange' ),
		'WA'  => __( 'Western Australia', 'it-l10n-ithemes-exchange' )
	);
	$states = apply_filters( 'it_exchange_au_states', $states );

	// Territories
	$territories = array(
		'ACT' => __( 'Australian Capital Territory', 'it-l10n-ithemes-exchange' ),
		'JBT' => __( 'Jervis Bay Territory', 'it-l10n-ithemes-exchange' ),
		'NT'  => __( 'Northern Territory', 'it-l10n-ithemes-exchange' ),

	);
	$territories = apply_filters( 'it_exchange_au_territories', $territories );

	// Merge territories and states if needed
	if ( ! empty( $options['include-territories'] ) )
		$states = array_merge( $states, $territories );

	// Sort alphabetically or keep territories at the end
	if ( ! empty( $options['sort-territories-alphabetically'] ) )
		ksort( $states );

	$states = apply_filters( 'it_exchange_get_country_states_for_au', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the Canada
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_ca( $options=array() ) {
	// States
	$states = array(
		'AB' => __( 'Alberta', 'it-l10n-ithemes-exchange' ),
		'BC' => __( 'British Columbia', 'it-l10n-ithemes-exchange' ),
		'MB' => __( 'Manitoba', 'it-l10n-ithemes-exchange' ),
		'NB' => __( 'New Brunswick', 'it-l10n-ithemes-exchange' ),
		'NF' => __( 'Newfoundland', 'it-l10n-ithemes-exchange' ),
		'NT' => __( 'Northwest Territories', 'it-l10n-ithemes-exchange' ),
		'NS' => __( 'Nova Scotia', 'it-l10n-ithemes-exchange' ),
		'NU' => __( 'Nunavut', 'it-l10n-ithemes-exchange' ),
		'ON' => __( 'Ontario', 'it-l10n-ithemes-exchange' ),
		'PE' => __( 'Prince Edward Island', 'it-l10n-ithemes-exchange' ),
		'QC' => __( 'Quebec', 'it-l10n-ithemes-exchange' ),
		'SK' => __( 'Saskatchewan', 'it-l10n-ithemes-exchange' ),
		'YT' => __( 'Yukon Territory', 'it-l10n-ithemes-exchange' ),
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_ca', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the Germany
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_de( $options=array() ) {
	// States
	$states = array(
		'BW' => __( 'Baden-Württemberg', 'it-l10n-ithemes-exchange' ),
		'BY' => __( 'Bayern', 'it-l10n-ithemes-exchange' ),
		'BE' => __( 'Berlin', 'it-l10n-ithemes-exchange' ),
		'BB' => __( 'Brandenburg', 'it-l10n-ithemes-exchange' ),
		'HB' => __( 'Bremen', 'it-l10n-ithemes-exchange' ),
		'HH' => __( 'Hamburg', 'it-l10n-ithemes-exchange' ),
		'HE' => __( 'Hessen', 'it-l10n-ithemes-exchange' ),
		'MV' => __( 'Mecklenburg-Vorpommern', 'it-l10n-ithemes-exchange' ),
		'NI' => __( 'Niedersachsen', 'it-l10n-ithemes-exchange' ),
		'NW' => __( 'Nordrhein-Westfalen', 'it-l10n-ithemes-exchange' ),
		'RP' => __( 'Rheinland-Pfalz', 'it-l10n-ithemes-exchange' ),
		'SL' => __( 'Saarland', 'it-l10n-ithemes-exchange' ),
		'SN' => __( 'Sachsen', 'it-l10n-ithemes-exchange' ),
		'ST' => __( 'Sachsen-Anhalt', 'it-l10n-ithemes-exchange' ),
		'SH' => __( 'Schleswig-Holstein', 'it-l10n-ithemes-exchange' ),
		'TH' => __( 'Thüringen', 'it-l10n-ithemes-exchange' ),
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_de', $states, $options );
	return $states;
}

/**
 * Returns an array of states for France
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_fr( $options=array() ) {
	// States
	$states = array(
		'A' => __( 'Alsace', 'it-l10n-ithemes-exchange' ),
		'B' => __( 'Aquitaine', 'it-l10n-ithemes-exchange' ),
		'C' => __( 'Auvergne', 'it-l10n-ithemes-exchange' ),
		'P' => __( 'Basse-Normandie', 'it-l10n-ithemes-exchange' ),
		'D' => __( 'Bourgogne', 'it-l10n-ithemes-exchange' ),
		'E' => __( 'Bretagne', 'it-l10n-ithemes-exchange' ),
		'F' => __( 'Centre', 'it-l10n-ithemes-exchange' ),
		'G' => __( 'Champagne-Ardenne', 'it-l10n-ithemes-exchange' ),
		'H' => __( 'Corse', 'it-l10n-ithemes-exchange' ),
		'I' => __( 'Franche-Comté', 'it-l10n-ithemes-exchange' ),
		'Q' => __( 'Haute-Normandie', 'it-l10n-ithemes-exchange' ),
		'J' => __( 'Île-de-France', 'it-l10n-ithemes-exchange' ),
		'K' => __( 'Languedoc-Roussillon', 'it-l10n-ithemes-exchange' ),
		'L' => __( 'Limousin', 'it-l10n-ithemes-exchange' ),
		'M' => __( 'Lorraine', 'it-l10n-ithemes-exchange' ),
		'N' => __( 'Midi-Pyrénées', 'it-l10n-ithemes-exchange' ),
		'O' => __( 'Nord-Pas-de-Calais', 'it-l10n-ithemes-exchange' ),
		'R' => __( 'Pays de la Loire', 'it-l10n-ithemes-exchange' ),
		'S' => __( 'Picardie', 'it-l10n-ithemes-exchange' ),
		'T' => __( 'Poitou-Charentes', 'it-l10n-ithemes-exchange' ),
		'U' => __( 'Provence-Alpes-Côte d\'Azur', 'it-l10n-ithemes-exchange' ),
		'V' => __( 'Rhône-Alpes', 'it-l10n-ithemes-exchange' ),
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_fr', $states, $options );
	return $states;
}


/**
 * Returns an array of states for the Netherlands
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_nl( $options=array() ) {
	// States
	$states = array(
		'DR' => __( 'Drenthe', 'it-l10n-ithemes-exchange' ),
		'FL' => __( 'Flevoland', 'it-l10n-ithemes-exchange' ),
		'FR' => __( 'Friesland', 'it-l10n-ithemes-exchange' ),
		'GE' => __( 'Gelderland', 'it-l10n-ithemes-exchange' ),
		'GR' => __( 'Groningen', 'it-l10n-ithemes-exchange' ),
		'LI' => __( 'Limburg', 'it-l10n-ithemes-exchange' ),
		'NB' => __( 'Noord-Brabant', 'it-l10n-ithemes-exchange' ),
		'NH' => __( 'Noord-Holland', 'it-l10n-ithemes-exchange' ),
		'OV' => __( 'Overijssel', 'it-l10n-ithemes-exchange' ),
		'UT' => __( 'Utrecht', 'it-l10n-ithemes-exchange' ),
		'ZE' => __( 'Zeeland', 'it-l10n-ithemes-exchange' ),
		'ZH' => __( 'Zuid-Holland', 'it-l10n-ithemes-exchange' ),
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_nl', $states, $options );
	return $states;
}

/**
 * Returns an array of states for Spain
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_es( $options=array() ) {
	// States
	$states = array(
		'C'  => __( 'A Coruña', 'it-l10n-ithemes-exchange' ),
		'VI' => __( 'Álava / Araba', 'it-l10n-ithemes-exchange' ),
		'AB' => __( 'Albacete', 'it-l10n-ithemes-exchange' ),
		'A'  => __( 'Alicante / Alacant', 'it-l10n-ithemes-exchange' ),
		'AL' => __( 'Almería', 'it-l10n-ithemes-exchange' ),
		'O'  => __( 'Asturias', 'it-l10n-ithemes-exchange' ),
		'AV' => __( 'Ávila', 'it-l10n-ithemes-exchange' ),
		'BA' => __( 'Badajoz', 'it-l10n-ithemes-exchange' ),
		'PM' => __( 'Balears', 'it-l10n-ithemes-exchange' ),
		'B'  => __( 'Barcelona', 'it-l10n-ithemes-exchange' ),
		'BU' => __( 'Burgos', 'it-l10n-ithemes-exchange' ),
		'CC' => __( 'Cáceres', 'it-l10n-ithemes-exchange' ),
		'CA' => __( 'Cádiz', 'it-l10n-ithemes-exchange' ),
		'S'  => __( 'Cantabria', 'it-l10n-ithemes-exchange' ),
		'CS' => __( 'Castelló', 'it-l10n-ithemes-exchange' ),
		'CR' => __( 'Ciudad Real', 'it-l10n-ithemes-exchange' ),
		'CO' => __( 'Córdoba', 'it-l10n-ithemes-exchange' ),
		'CU' => __( 'Cuenca', 'it-l10n-ithemes-exchange' ),
		'GI' => __( 'Girona', 'it-l10n-ithemes-exchange' ),
		'GR' => __( 'Granada', 'it-l10n-ithemes-exchange' ),
		'GU' => __( 'Guadalajara', 'it-l10n-ithemes-exchange' ),
		'SS' => __( 'Guipúzcoa / Gipuzkoa', 'it-l10n-ithemes-exchange' ),
		'H'  => __( 'Huelva', 'it-l10n-ithemes-exchange' ),
		'HU' => __( 'Huesca', 'it-l10n-ithemes-exchange' ),
		'J'  => __( 'Jaén', 'it-l10n-ithemes-exchange' ),
		'LO' => __( 'La Rioja', 'it-l10n-ithemes-exchange' ),
		'GC' => __( 'Las Palmas', 'it-l10n-ithemes-exchange' ),
		'LE' => __( 'León', 'it-l10n-ithemes-exchange' ),
		'L'  => __( 'Lleida', 'it-l10n-ithemes-exchange' ),
		'LU' => __( 'Lugo', 'it-l10n-ithemes-exchange' ),
		'M'  => __( 'Madrid', 'it-l10n-ithemes-exchange' ),
		'MA' => __( 'Málaga', 'it-l10n-ithemes-exchange' ),
		'MU' => __( 'Murcia', 'it-l10n-ithemes-exchange' ),
		'NA' => __( 'Navarra / Nafarroa', 'it-l10n-ithemes-exchange' ),
		'OR' => __( 'Ourense', 'it-l10n-ithemes-exchange' ),
		'P'  => __( 'Palencia', 'it-l10n-ithemes-exchange' ),
		'PO' => __( 'Pontevedra', 'it-l10n-ithemes-exchange' ),
		'SA' => __( 'Salamanca', 'it-l10n-ithemes-exchange' ),
		'TF' => __( 'Santa Cruz de Tenerife', 'it-l10n-ithemes-exchange' ),
		'SG' => __( 'Segovia', 'it-l10n-ithemes-exchange' ),
		'SE' => __( 'Sevilla', 'it-l10n-ithemes-exchange' ),
		'SO' => __( 'Soria', 'it-l10n-ithemes-exchange' ),
		'T'  => __( 'Tarragona', 'it-l10n-ithemes-exchange' ),
		'TE' => __( 'Teruel', 'it-l10n-ithemes-exchange' ),
		'TO' => __( 'Toledo', 'it-l10n-ithemes-exchange' ),
		'V'  => __( 'Valencia / València', 'it-l10n-ithemes-exchange' ),
		'VA' => __( 'Valladolid', 'it-l10n-ithemes-exchange' ),
		'BI' => __( 'Vizcaya / Bizkaia', 'it-l10n-ithemes-exchange' ),
		'ZA' => __( 'Zamora', 'it-l10n-ithemes-exchange' ),
		'Z'  => __( 'Zaragoza', 'it-l10n-ithemes-exchange' ),
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_es', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the US
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_us( $options=array() ) {

	// Defaults
	$defaults = array(
		'include-territories'             => false,
		'sort-territories-alphabetically' => false,
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	// States array
	$states = array(
		'AL' => __( 'Alabama', 'it-l10n-ithemes-exchange' ),
		'AK' => __( 'Alaska', 'it-l10n-ithemes-exchange' ),
		'AZ' => __( 'Arizona', 'it-l10n-ithemes-exchange' ),
		'AR' => __( 'Arkansas', 'it-l10n-ithemes-exchange' ),
		'CA' => __( 'California', 'it-l10n-ithemes-exchange' ),
		'CO' => __( 'Colorado', 'it-l10n-ithemes-exchange' ),
		'CT' => __( 'Connecticut', 'it-l10n-ithemes-exchange' ),
		'DE' => __( 'Delaware', 'it-l10n-ithemes-exchange' ),
		'DC' => __( 'District Of Columbia', 'it-l10n-ithemes-exchange' ),
		'FL' => __( 'Florida', 'it-l10n-ithemes-exchange' ),
		'GA' => __( 'Georgia', 'it-l10n-ithemes-exchange' ),
		'HI' => __( 'Hawaii', 'it-l10n-ithemes-exchange' ),
		'ID' => __( 'Idaho', 'it-l10n-ithemes-exchange' ),
		'IL' => __( 'Illinois', 'it-l10n-ithemes-exchange' ),
		'IN' => __( 'Indiana', 'it-l10n-ithemes-exchange' ),
		'IA' => __( 'Iowa', 'it-l10n-ithemes-exchange' ),
		'KS' => __( 'Kansas', 'it-l10n-ithemes-exchange' ),
		'KY' => __( 'Kentucky', 'it-l10n-ithemes-exchange' ),
		'LA' => __( 'Louisiana', 'it-l10n-ithemes-exchange' ),
		'ME' => __( 'Maine', 'it-l10n-ithemes-exchange' ),
		'MD' => __( 'Maryland', 'it-l10n-ithemes-exchange' ),
		'MA' => __( 'Massachusetts', 'it-l10n-ithemes-exchange' ),
		'MI' => __( 'Michigan', 'it-l10n-ithemes-exchange' ),
		'MN' => __( 'Minnesota', 'it-l10n-ithemes-exchange' ),
		'MS' => __( 'Mississippi', 'it-l10n-ithemes-exchange' ),
		'MO' => __( 'Missouri', 'it-l10n-ithemes-exchange' ),
		'MT' => __( 'Montana', 'it-l10n-ithemes-exchange' ),
		'NE' => __( 'Nebraska', 'it-l10n-ithemes-exchange' ),
		'NV' => __( 'Nevada', 'it-l10n-ithemes-exchange' ),
		'NH' => __( 'New Hampshire', 'it-l10n-ithemes-exchange' ),
		'NJ' => __( 'New Jersey', 'it-l10n-ithemes-exchange' ),
		'NM' => __( 'New Mexico', 'it-l10n-ithemes-exchange' ),
		'NY' => __( 'New York', 'it-l10n-ithemes-exchange' ),
		'NC' => __( 'North Carolina', 'it-l10n-ithemes-exchange' ),
		'ND' => __( 'North Dakota', 'it-l10n-ithemes-exchange' ),
		'OH' => __( 'Ohio', 'it-l10n-ithemes-exchange' ),
		'OK' => __( 'Oklahoma', 'it-l10n-ithemes-exchange' ),
		'OR' => __( 'Oregon', 'it-l10n-ithemes-exchange' ),
		'PA' => __( 'Pennsylvania', 'it-l10n-ithemes-exchange' ),
		'RI' => __( 'Rhode Island', 'it-l10n-ithemes-exchange' ),
		'SC' => __( 'South Carolina', 'it-l10n-ithemes-exchange' ),
		'SD' => __( 'South Dakota', 'it-l10n-ithemes-exchange' ),
		'TN' => __( 'Tennessee', 'it-l10n-ithemes-exchange' ),
		'TX' => __( 'Texas', 'it-l10n-ithemes-exchange' ),
		'UT' => __( 'Utah', 'it-l10n-ithemes-exchange' ),
		'VT' => __( 'Vermont', 'it-l10n-ithemes-exchange' ),
		'VA' => __( 'Virginia', 'it-l10n-ithemes-exchange' ),
		'WA' => __( 'Washington', 'it-l10n-ithemes-exchange' ),
		'WV' => __( 'West Virginia', 'it-l10n-ithemes-exchange' ),
		'WI' => __( 'Wisconsin', 'it-l10n-ithemes-exchange' ),
		'WY' => __( 'Wyoming', 'it-l10n-ithemes-exchange' ),
	);
	$states = apply_filters( 'it_exchange_us_states', $states );

	// Territories
	$territories = array(
		'AS' => __( 'American Samoa', 'it-l10n-ithemes-exchange' ),
		'FM' => __( 'Federated States of Micronesia', 'it-l10n-ithemes-exchange' ),
		'GU' => __( 'Guam', 'it-l10n-ithemes-exchange' ),
		'MH' => __( 'Marshall Islands', 'it-l10n-ithemes-exchange' ),
		'MP' => __( 'Northern Mariana Islands', 'it-l10n-ithemes-exchange' ),
		'PR' => __( 'Puerto Rico', 'it-l10n-ithemes-exchange' ),
		'PW' => __( 'Palau', 'it-l10n-ithemes-exchange' ),
		'VI' => __( 'Virgin Islands', 'it-l10n-ithemes-exchange' ),
	);
	$territories = apply_filters( 'it_exchange_us_territories', $territories );

	// Include territories?
	if ( ! empty( $options['include-territories'] ) )
		$states = array_merge( $states, $territories );

	// Sort alphabetically or keep territories at the end
	if ( ! empty( $options['sort-territories-alphabetically'] ) )
		ksort( $states );

	$states = apply_filters( 'it_exchange_get_country_states_for_us', $states, $options );
	return $states;
}

/**
 * Returns an array of states for the South Africa
 *
 * @since 1.2.0
 *
 * @return array
*/
function it_exchange_get_country_states_for_za( $options=array() ) {
	// States
	$states = array(
		'EC'  => __( 'Eastern Cape', 'it-l10n-ithemes-exchange' ) ,
		'FS'  => __( 'Free State', 'it-l10n-ithemes-exchange' ) ,
		'GP'  => __( 'Gauteng', 'it-l10n-ithemes-exchange' ) ,
		'KZN' => __( 'KwaZulu-Natal', 'it-l10n-ithemes-exchange' ) ,
		'LP'  => __( 'Limpopo', 'it-l10n-ithemes-exchange' ) ,
		'MP'  => __( 'Mpumalanga', 'it-l10n-ithemes-exchange' ) ,
		'NC'  => __( 'Northern Cape', 'it-l10n-ithemes-exchange' ) ,
		'NW'  => __( 'North West', 'it-l10n-ithemes-exchange' ) ,
		'WC'  => __( 'Western Cape', 'it-l10n-ithemes-exchange' )
	);

	$states = apply_filters( 'it_exchange_get_country_states_for_za', $states, $options );
	return $states;
}
