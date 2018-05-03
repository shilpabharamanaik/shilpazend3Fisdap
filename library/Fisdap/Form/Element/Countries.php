<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Custom Zend_Form_Element_Select for displaying a list of countries
 */

/**
 * @package Fisdap
 */
class Fisdap_Form_Element_Countries extends Zend_Form_Element_Select
{
	protected static $_countries = array(
        array('Afghanistan', 'AFG'),
        array('Aland Islands', 'ALA'),
        array('Albania', 'ALB'),
        array('Algeria', 'DZA'),
        array('American Samoa', 'ASM'),
        array('Andorra', 'AND'),
        array('Angola', 'AGO'),
        array('Anguilla', 'AIA'),
        array('Antigua and Barbuda', 'ATG'),
        array('Argentina', 'ARG'),
        array('Armenia', 'ARM'),
        array('Aruba', 'ABW'),
        array('Australia', 'AUS'),
        array('Austria', 'AUT'),
        array('Azerbaijan', 'AZE'),
        array('Bahamas', 'BHS'),
        array('Bahrain', 'BHR'),
        array('Bangladesh', 'BGD'),
        array('Barbados', 'BRB'),
        array('Belarus', 'BLR'),
        array('Belgium', 'BEL'),
        array('Belize', 'BLZ'),
        array('Benin', 'BEN'),
        array('Bermuda', 'BMU'),
        array('Bhutan', 'BTN'),
        array('Bolivia', 'BOL'),
        array('Bosnia and Herzegovina', 'BIH'),
        array('Botswana', 'BWA'),
        array('Brazil', 'BRA'),
        array('British Virgin Islands', 'VGB'),
        array('Brunei Darussalam', 'BRN'),
        array('Bulgaria', 'BGR'),
        array('Burkina Faso', 'BFA'),
        array('Burundi', 'BDI'),
        array('Cambodia', 'KHM'),
        array('Cameroon', 'CMR'),
        array('Canada', 'CAN'),
        array('Cape Verde', 'CPV'),
        array('Cayman Islands', 'CYM'),
        array('Central African Republic', 'CAF'),
        array('Chad', 'TCD'),
        array('Channel Islands', 'CHI'),
        array('Chile', 'CHL'),
        array('China', 'CHN'),
        array('Colombia', 'COL'),
        array('Comoros', 'COM'),
        array('Congo', 'COG'),
        array('Cook Islands', 'COK'),
        array('Costa Rica', 'CRI'),
        array('Cote d\'Ivoire', 'CIV'),
        array('Croatia', 'HRV'),
        array('Cuba', 'CUB'),
        array('Cyprus', 'CYP'),
        array('Czech Republic', 'CZE'),
        array('Democratic People\'s Republic of Korea', 'PRK'),
        array('Democratic Republic of the Congo', 'COD'),
        array('Denmark', 'DNK'),
        array('Djibouti', 'DJI'),
        array('Dominica', 'DMA'),
        array('Dominican Republic', 'DOM'),
        array('Ecuador', 'ECU'),
        array('Egypt', 'EGY'),
        array('El Salvador', 'SLV'),
        array('Equatorial Guinea', 'GNQ'),
        array('Eritrea', 'ERI'),
        array('Estonia', 'EST'),
        array('Ethiopia', 'ETH'),
        array('Faeroe Islands', 'FRO'),
        array('Falkland Islands (Malvinas)', 'FLK'),
        array('Fiji', 'FJI'),
        array('Finland', 'FIN'),
        array('France', 'FRA'),
        array('French Guiana', 'GUF'),
        array('French Polynesia', 'PYF'),
        array('Gabon', 'GAB'),
        array('Gambia', 'GMB'),
        array('Georgia', 'GEO'),
        array('Germany', 'DEU'),
        array('Ghana', 'GHA'),
        array('Gibraltar', 'GIB'),
        array('Greece', 'GRC'),
        array('Greenland', 'GRL'),
        array('Grenada', 'GRD'),
        array('Guadeloupe', 'GLP'),
        array('Guam', 'GUM'),
        array('Guatemala', 'GTM'),
        array('Guernsey', 'GGY'),
        array('Guinea', 'GIN'),
        array('Guinea-Bissau', 'GNB'),
        array('Guyana', 'GUY'),
        array('Haiti', 'HTI'),
        array('Holy See', 'VAT'),
        array('Honduras', 'HND'),
        array('Hong Kong Special Administrative Region of China', 'HKG'),
        array('Hungary', 'HUN'),
        array('Iceland', 'ISL'),
        array('India', 'IND'),
        array('Indonesia', 'IDN'),
        array('Iran, Islamic Republic of', 'IRN'),
        array('Iraq', 'IRQ'),
        array('Ireland', 'IRL'),
        array('Isle of Man', 'IMN'),
        array('Israel', 'ISR'),
        array('Italy', 'ITA'),
        array('Jamaica', 'JAM'),
        array('Japan', 'JPN'),
        array('Jersey', 'JEY'),
        array('Jordan', 'JOR'),
        array('Kazakhstan', 'KAZ'),
        array('Kenya', 'KEN'),
        array('Kiribati', 'KIR'),
        array('Kuwait', 'KWT'),
        array('Kyrgyzstan', 'KGZ'),
        array('Lao People\'s Democratic Republic', 'LAO'),
        array('Latvia', 'LVA'),
        array('Lebanon', 'LBN'),
        array('Lesotho', 'LSO'),
        array('Liberia', 'LBR'),
        array('Libyan Arab Jamahiriya', 'LBY'),
        array('Liechtenstein', 'LIE'),
        array('Lithuania', 'LTU'),
        array('Luxembourg', 'LUX'),
        array('Macao Special Administrative Region of China', 'MAC'),
        array('Madagascar', 'MDG'),
        array('Malawi', 'MWI'),
        array('Malaysia', 'MYS'),
        array('Maldives', 'MDV'),
        array('Mali', 'MLI'),
        array('Malta', 'MLT'),
        array('Marshall Islands', 'MHL'),
        array('Martinique', 'MTQ'),
        array('Mauritania', 'MRT'),
        array('Mauritius', 'MUS'),
        array('Mayotte', 'MYT'),
        array('Mexico', 'MEX'),
        array('Micronesia, Federated States of', 'FSM'),
        array('Monaco', 'MCO'),
        array('Mongolia', 'MNG'),
        array('Montenegro', 'MNE'),
        array('Montserrat', 'MSR'),
        array('Morocco', 'MAR'),
        array('Mozambique', 'MOZ'),
        array('Myanmar', 'MMR'),
        array('Namibia', 'NAM'),
        array('Nauru', 'NRU'),
        array('Nepal', 'NPL'),
        array('Netherlands', 'NLD'),
        array('Netherlands Antilles', 'ANT'),
        array('New Caledonia', 'NCL'),
        array('New Zealand', 'NZL'),
        array('Nicaragua', 'NIC'),
        array('Niger', 'NER'),
        array('Nigeria', 'NGA'),
        array('Niue', 'NIU'),
        array('Norfolk Island', 'NFK'),
        array('Northern Mariana Islands', 'MNP'),
        array('Norway', 'NOR'),
        array('Occupied Palestinian Territory', 'PSE'),
        array('Oman', 'OMN'),
        array('Pakistan', 'PAK'),
        array('Palau', 'PLW'),
        array('Panama', 'PAN'),
        array('Papua New Guinea', 'PNG'),
        array('Paraguay', 'PRY'),
        array('Peru', 'PER'),
        array('Philippines', 'PHL'),
        array('Pitcairn', 'PCN'),
        array('Poland', 'POL'),
        array('Portugal', 'PRT'),
        array('Puerto Rico', 'PRI'),
        array('Qatar', 'QAT'),
        array('Republic of Korea', 'KOR'),
        array('Republic of Moldova', 'MDA'),
        array('Reunion', 'REU'),
        array('Romania', 'ROU'),
        array('Russian Federation', 'RUS'),
        array('Rwanda', 'RWA'),
        array('Saint-Barthelemy', 'BLM'),
        array('Saint Helena', 'SHN'),
        array('Saint Kitts and Nevis', 'KNA'),
        array('Saint Lucia', 'LCA'),
        array('Saint-Martin (French part)', 'MAF'),
        array('Saint Pierre and Miquelon', 'SPM'),
        array('Saint Vincent and the Grenadines', 'VCT'),
        array('Samoa', 'WSM'),
        array('San Marino', 'SMR'),
        array('Sao Tome and Principe', 'STP'),
        array('Saudi Arabia', 'SAU'),
        array('Senegal', 'SEN'),
        array('Serbia', 'SRB'),
        array('Seychelles', 'SYC'),
        array('Sierra Leone', 'SLE'),
        array('Singapore', 'SGP'),
        array('Slovakia', 'SVK'),
        array('Slovenia', 'SVN'),
        array('Solomon Islands', 'SLB'),
        array('Somalia', 'SOM'),
        array('South Africa', 'ZAF'),
        array('Spain', 'ESP'),
        array('Sri Lanka', 'LKA'),
        array('Sudan', 'SDN'),
        array('Suriname', 'SUR'),
        array('Svalbard and Jan Mayen Islands', 'SJM'),
        array('Swaziland', 'SWZ'),
        array('Sweden', 'SWE'),
        array('Switzerland', 'CHE'),
        array('Syrian Arab Republic', 'SYR'),
        array('Tajikistan', 'TJK'),
        array('Thailand', 'THA'),
        array('The former Yugoslav Republic of Macedonia', 'MKD'),
        array('Timor-Leste', 'TLS'),
        array('Togo', 'TGO'),
        array('Tokelau', 'TKL'),
        array('Tonga', 'TON'),
        array('Trinidad and Tobago', 'TTO'),
        array('Tunisia', 'TUN'),
        array('Turkey', 'TUR'),
        array('Turkmenistan', 'TKM'),
        array('Turks and Caicos Islands', 'TCA'),
        array('Tuvalu', 'TUV'),
        array('Uganda', 'UGA'),
        array('Ukraine', 'UKR'),
        array('United Arab Emirates', 'ARE'),
        array('United Kingdom of Great Britain and Northern Ireland', 'GBR'),
        array('United Republic of Tanzania', 'TZA'),
        array('United States of America', 'USA'),
        array('United States Virgin Islands', 'VIR'),
        array('Uruguay', 'URY'),
        array('Uzbekistan', 'UZB'),
        array('Vanuatu', 'VUT'),
        array('Venezuela (Bolivarian Republic of)', 'VEN'),
        array('Viet Nam', 'VNM'),
        array('Wallis and Futuna Islands', 'WLF'),
        array('Western Sahara', 'ESH'),
        array('Yemen', 'YEM'),
        array('Zambia', 'ZMB'),
        array('Zimbabwe', 'ZWE'));

	public function init()
	{
        $this->getView()->headScript()->appendFile("/js/library/Fisdap/Form/Element/countries.js");
		$this->setAttrib("class", $this->getAttrib("class"). " country");
		$this->setStateElementName("state");
		
        foreach (self::$_countries as $country) {
            $this->addMultiOption($country[1], $country[0]);
        }
	}

	/**
	 * Use the full name of the country rather than 3 char abbreviation
	 * @return \Fisdap_Form_Element_States
	 */
	public function useFullNames()
	{
        $this->clearMultiOptions();
		foreach (self::$_countries as $country) {
            $this->addMultiOption($country[1], $country[1]);
        }

		return $this;
	}
	
	public function setStateElementName($stateElementName)
	{
		$this->setAttrib("data-state", $stateElementName);
		return $this;
	}

	/**
	 * Get the abbreviation for a country given the full name
	 *
	 * @param string $fullName the full name of the country
	 * @return string the abbreviation for the country
	 */
	public static function getAbbreviation($fullName)
	{
        foreach (self::$_countries as $country) {
            if ($country[0] == $fullName) {
                return $country[1];
            }
        }
        return null;
	}

	/**
	 * Get the full name for a country given the abbreviation
	 *
	 * @param string $abbreviation the abbreviation for the country
	 * @return string the full name of the country
	 */
	public static function getFullName($abbreviation)
	{
		foreach (self::$_countries as $country) {
            if ($country[1] == $abbreviation) {
                return $country[1];
            }
        }
        return null;
	}
}