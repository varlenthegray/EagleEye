<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 2/6/2019
 * Time: 10:35 AM
 */

namespace DropdownOpts;


class dropdown_options {
  public function getTimezoneOpts($selTimezone) {
    $timezone = array('Etc/GMT+10' => '', 'America/Anchorage' => '', 'America/Los_Angeles' => '', 'America/Denver' => '', 'America/Chicago' => '',
      'America/New_York' => '', 'America/Glace_Bay' => '', 'Pacific/Midway' => '', 'Pacific/Marquesas' => '', 'Pacific/Gambier' => '', 'America/Ensenada' => '',
      'America/Chihuahua' => '', 'America/Belize' => '', 'America/Havana' => '', 'America/Caracas' => '', 'America/Santiago' => '', 'America/St_Johns' => '',
      'America/Araguaina' => '', 'America/Noronha' => '', 'Atlantic/Cape_Verde' => '', 'Europe/Belfast' => '', 'Europe/Amsterdam' => '', 'Asia/Beirut' => '',
      'Europe/Moscow' => '', 'Asia/Tehran' => '', 'Asia/Dubai' => '', 'Asia/Kabul' => '', 'Asia/Tashkent' => '', 'Asia/Kolkata' => '', 'Asia/Katmandu' => '',
      'Asia/Dhaka' => '', 'Asia/Rangoon' => '', 'Asia/Bangkok' => '', 'Asia/Hong_Kong' => '', 'Australia/Eucla' => '', 'Asia/Tokyo' => '', 'Australia/Adelaide' => '',
      'Australia/Brisbane' => '', 'Australia/Lord_Howe' => '', 'Etc/GMT-11' => '', 'Pacific/Norfolk' => '', 'Asia/Anadyr' => '', 'Pacific/Chatham' => '',
      'Pacific/Tongatapu' => '', 'Pacific/Kiritimati' => '');

    if(!empty($selTimezone)) {
      $timezone[$selTimezone] = 'selected';
    }

    return "
  <optgroup label='North America'>
    <option value='Etc/GMT+10' {$timezone['Etc/GMT+10']}>(GMT-10:00) Hawaii</option>
    <option value='America/Anchorage' {$timezone['America/Anchorage']}>(GMT-09:00) Alaska</option>
    <option value='America/Los_Angeles' {$timezone['America/Los_Angeles']}>(GMT-08:00) Pacific</option>
    <option value='America/Denver' {$timezone['America/Denver']}>(GMT-07:00) Mountain</option>
    <option value='America/Chicago' {$timezone['America/Chicago']}>(GMT-06:00) Central</option>
    <option value='America/New_York' {$timezone['America/New_York']}>(GMT-05:00) Eastern</option>
    <option value='America/Glace_Bay' {$timezone['America/Glace_Bay']}>(GMT-04:00) Atlantic</option>
  </optgroup>
  <optgroup label='International'>
    <option value='Pacific/Midway' {$timezone['Pacific/Midway']}>GMT-11:00</option>
    <option value='Pacific/Marquesas' {$timezone['Pacific/Marquesas']}>GMT-09:30</option>
    <option value='Pacific/Gambier' {$timezone['Pacific/Gambier']}>GMT-09:00</option>
    <option value='America/Ensenada' {$timezone['America/Ensenada']}>GMT-08:00</option>
    <option value='America/Chihuahua' {$timezone['America/Chihuahua']}>GMT-07:00</option>
    <option value='America/Belize' {$timezone['America/Belize']}>GMT-06:00</option>
    <option value='America/Havana' {$timezone['America/Havana']}>GMT-05:00</option>
    <option value='America/Caracas' {$timezone['America/Caracas']}>GMT-04:30</option>
    <option value='America/Santiago' {$timezone['America/Santiago']}>GMT-04:00</option>
    <option value='America/St_Johns' {$timezone['America/St_Johns']}>GMT-03:30</option>
    <option value='America/Araguaina' {$timezone['America/Araguaina']}>GMT-03:00</option>
    <option value='America/Noronha' {$timezone['America/Noronha']}>GMT-02:00</option>
    <option value='Atlantic/Cape_Verde' {$timezone['Atlantic/Cape_Verde']}>GMT-01:00</option>
    <option value='Europe/Belfast' {$timezone['Europe/Belfast']}>GMT</option>
    <option value='Europe/Amsterdam' {$timezone['Europe/Amsterdam']}>GMT+01:00</option>
    <option value='Asia/Beirut' {$timezone['Asia/Beirut']}>GMT+02:00</option>
    <option value='Europe/Moscow' {$timezone['Europe/Moscow']}>GMT+03:00</option>
    <option value='Asia/Tehran' {$timezone['Asia/Tehran']}>GMT+03:30</option>
    <option value='Asia/Dubai' {$timezone['Asia/Dubai']}>GMT+04:00</option>
    <option value='Asia/Kabul' {$timezone['Asia/Kabul']}>GMT+04:30</option>
    <option value='Asia/Tashkent' {$timezone['Asia/Tashkent']}>GMT+05:00</option>
    <option value='Asia/Kolkata' {$timezone['Asia/Kolkata']}>GMT+05:30</option>
    <option value='Asia/Katmandu' {$timezone['Asia/Katmandu']}>GMT+05:45</option>
    <option value='Asia/Dhaka' {$timezone['Asia/Dhaka']}>GMT+06:00</option>
    <option value='Asia/Rangoon' {$timezone['Asia/Rangoon']}>GMT+06:30</option>
    <option value='Asia/Bangkok' {$timezone['Asia/Bangkok']}>GMT+07:00</option>
    <option value='Asia/Hong_Kong' {$timezone['Asia/Hong_Kong']}>GMT+08:00</option>
    <option value='Australia/Eucla' {$timezone['Australia/Eucla']}>GMT+08:45</option>
    <option value='Asia/Tokyo' {$timezone['Asia/Tokyo']}>GMT+09:00</option>
    <option value='Australia/Adelaide' {$timezone['Australia/Adelaide']}>GMT+09:30</option>
    <option value='Australia/Brisbane' {$timezone['Australia/Brisbane']}>GMT+10:00</option>
    <option value='Australia/Lord_Howe' {$timezone['Australia/Lord_Howe']}>GMT+10:30</option>
    <option value='Etc/GMT-11' {$timezone['Etc/GMT-11']}>GMT+11:00</option>
    <option value='Pacific/Norfolk' {$timezone['Pacific/Norfolk']}>GMT+11:30</option>
    <option value='Asia/Anadyr' {$timezone['Asia/Anadyr']}>GMT+12:00</option>
    <option value='Pacific/Chatham' {$timezone['Pacific/Chatham']}>GMT+12:45</option>
    <option value='Pacific/Tongatapu' {$timezone['Pacific/Tongatapu']}>GMT+13:00</option>
    <option value='Pacific/Kiritimati' {$timezone['Pacific/Kiritimati']}>GMT+14:00</option>
  </optgroup>
  ";
  }

  public function getCountryOpts($selCountry) {
    $country = array('AF' => '', 'AX' => '', 'AL' => '', 'DZ' => '', 'AS' => '', 'AD' => '', 'AO' => '', 'AI' => '', 'AQ' => '', 'AG' => '', 'AR' => '', 'AM' => '',
      'AW' => '', 'AU' => '', 'AT' => '', 'AZ' => '', 'BS' => '', 'BH' => '', 'BD' => '', 'BB' => '', 'BY' => '', 'BE' => '', 'BZ' => '', 'BJ' => '', 'BM' => '',
      'BT' => '', 'BO' => '', 'BA' => '', 'BW' => '', 'BV' => '', 'BR' => '', 'IO' => '', 'BN' => '', 'BG' => '', 'BF' => '', 'BI' => '', 'KH' => '', 'CM' => '',
      'CA' => '', 'CV' => '', 'KY' => '', 'CF' => '', 'TD' => '', 'CL' => '', 'CN' => '', 'CX' => '', 'CC' => '', 'CO' => '', 'KM' => '', 'CG' => '', 'CD' => '',
      'CK' => '', 'CR' => '', 'CI' => '', 'HR' => '', 'CU' => '', 'CY' => '', 'CZ' => '', 'DK' => '', 'DJ' => '', 'DM' => '', 'DO' => '', 'EC' => '', 'EG' => '',
      'SV' => '', 'GQ' => '', 'ER' => '', 'EE' => '', 'ET' => '', 'FK' => '', 'FO' => '', 'FJ' => '', 'FI' => '', 'FR' => '', 'GF' => '', 'PF' => '', 'TF' => '',
      'GA' => '', 'GM' => '', 'GE' => '', 'DE' => '', 'GH' => '', 'GI' => '', 'GR' => '', 'GL' => '', 'GD' => '', 'GP' => '', 'GU' => '', 'GT' => '', 'GG' => '',
      'GN' => '', 'GW' => '', 'GY' => '', 'HT' => '', 'HM' => '', 'VA' => '', 'HN' => '', 'HK' => '', 'HU' => '', 'IS' => '', 'IN' => '', 'ID' => '', 'IR' => '',
      'IQ' => '', 'IE' => '', 'IM' => '', 'IL' => '', 'IT' => '', 'JM' => '', 'JP' => '', 'JE' => '', 'JO' => '', 'KZ' => '', 'KE' => '', 'KI' => '', 'KR' => '',
      'KW' => '', 'KG' => '', 'LA' => '', 'LV' => '', 'LB' => '', 'LS' => '', 'LR' => '', 'LY' => '', 'LI' => '', 'LT' => '', 'LU' => '', 'MO' => '', 'MK' => '',
      'MG' => '', 'MW' => '', 'MY' => '', 'MV' => '', 'ML' => '', 'MT' => '', 'MH' => '', 'MQ' => '', 'MR' => '', 'MU' => '', 'YT' => '', 'MX' => '', 'FM' => '',
      'MD' => '', 'MC' => '', 'MN' => '', 'ME' => '', 'MS' => '', 'MA' => '', 'MZ' => '', 'MM' => '', 'NA' => '', 'NR' => '', 'NP' => '', 'NL' => '', 'AN' => '',
      'NC' => '', 'NZ' => '', 'NI' => '', 'NE' => '', 'NG' => '', 'NU' => '', 'NF' => '', 'MP' => '', 'NO' => '', 'OM' => '', 'PK' => '', 'PW' => '', 'PS' => '',
      'PA' => '', 'PG' => '', 'PY' => '', 'PE' => '', 'PH' => '', 'PN' => '', 'PL' => '', 'PT' => '', 'PR' => '', 'QA' => '', 'RE' => '', 'RO' => '', 'RU' => '',
      'RW' => '', 'BL' => '', 'SH' => '', 'KN' => '', 'LC' => '', 'MF' => '', 'PM' => '', 'VC' => '', 'WS' => '', 'SM' => '', 'ST' => '', 'SA' => '', 'SN' => '',
      'RS' => '', 'SC' => '', 'SL' => '', 'SG' => '', 'SK' => '', 'SI' => '', 'SB' => '', 'SO' => '', 'ZA' => '', 'GS' => '', 'ES' => '', 'LK' => '', 'SD' => '',
      'SR' => '', 'SJ' => '', 'SZ' => '', 'SE' => '', 'CH' => '', 'SY' => '', 'TW' => '', 'TJ' => '', 'TZ' => '', 'TH' => '', 'TL' => '', 'TG' => '', 'TK' => '',
      'TO' => '', 'TT' => '', 'TN' => '', 'TR' => '', 'TM' => '', 'TC' => '', 'TV' => '', 'UG' => '', 'UA' => '', 'AE' => '', 'GB' => '', 'US' => '', 'UM' => '',
      'UY' => '', 'UZ' => '', 'VU' => '', 'VE' => '', 'VN' => '', 'VG' => '', 'VI' => '', 'WF' => '', 'EH' => '', 'YE' => '', 'ZM' => '', 'ZW' => '');

    if(!empty($selCountry)) {
      $country[$selCountry] = 'selected';
    } else {
      $country['US'] = 'selected';
    }

    return "
  <optgroup label='North America'>
    <option value='US' {$country['US']}>United States</option>
    <option value='UM' {$country['UM']}>United States Minor Outlying Islands</option>
    <option value='CA' {$country['CA']}>Canada</option>
    <option value='MX' {$country['MX']}>Mexico</option>
    <option value='AI' {$country['AI']}>Anguilla</option>
    <option value='AG' {$country['AG']}>Antigua and Barbuda</option>
    <option value='AW' {$country['AW']}>Aruba</option>
    <option value='BS' {$country['BS']}>Bahamas</option>
    <option value='BB' {$country['BB']}>Barbados</option>
    <option value='BZ' {$country['BZ']}>Belize</option>
    <option value='BM' {$country['BM']}>Bermuda</option>
    <option value='VG' {$country['VG']}>British Virgin Islands</option>
    <option value='KY' {$country['KY']}>Cayman Islands</option>
    <option value='CR' {$country['CR']}>Costa Rica</option>
    <option value='CU' {$country['CU']}>Cuba</option>
    <option value='DM' {$country['DM']}>Dominica</option>
    <option value='DO' {$country['DO']}>Dominican Republic</option>
    <option value='SV' {$country['SV']}>El Salvador</option>
    <option value='GD' {$country['GD']}>Grenada</option>
    <option value='GP' {$country['GP']}>Guadeloupe</option>
    <option value='GT' {$country['GT']}>Guatemala</option>
    <option value='HT' {$country['HT']}>Haiti</option>
    <option value='HN' {$country['HN']}>Honduras</option>
    <option value='JM' {$country['JM']}>Jamaica</option>
    <option value='MQ' {$country['MQ']}>Martinique</option>
    <option value='MS' {$country['MS']}>Montserrat</option>
    <option value='AN' {$country['AN']}>Netherlands Antilles</option>
    <option value='NI' {$country['NI']}>Nicaragua</option>
    <option value='PA' {$country['PA']}>Panama</option>
    <option value='PR' {$country['PR']}>Puerto Rico</option>
    <option value='KN' {$country['KN']}>Saint Kitts and Nevis</option>
    <option value='LC' {$country['LC']}>Saint Lucia</option>
    <option value='VC' {$country['VC']}>Saint Vincent and the Grenadines</option>
    <option value='TT' {$country['TT']}>Trinidad and Tobago</option>
    <option value='TC' {$country['TC']}>Turks and Caicos Islands</option>
    <option value='VI' {$country['VI']}>US Virgin Islands</option>
  </optgroup>
  <optgroup label='South America'>
    <option value='AR' {$country['AR']}>Argentina</option>
    <option value='BO' {$country['BO']}>Bolivia</option>
    <option value='BR' {$country['BR']}>Brazil</option>
    <option value='CL' {$country['CL']}>Chile</option>
    <option value='CO' {$country['CO']}>Colombia</option>
    <option value='EC' {$country['EC']}>Ecuador</option>
    <option value='FK' {$country['FK']}>Falkland Islands (Malvinas)</option>
    <option value='GF' {$country['GF']}>French Guiana</option>
    <option value='GY' {$country['GY']}>Guyana</option>
    <option value='PY' {$country['PY']}>Paraguay</option>
    <option value='PE' {$country['PE']}>Peru</option>
    <option value='SR' {$country['SR']}>Suriname</option>
    <option value='UY' {$country['UY']}>Uruguay</option>
    <option value='VE' {$country['VE']}>Venezuela</option>
  </optgroup>
  <optgroup label='Europe'>
    <option value='GB' {$country['GB']}>United Kingdom</option>
    <option value='AL' {$country['AL']}>Albania</option>
    <option value='AD' {$country['AD']}>Andorra</option>
    <option value='AT' {$country['AT']}>Austria</option>
    <option value='BY' {$country['BY']}>Belarus</option>
    <option value='BE' {$country['BE']}>Belgium</option>
    <option value='BA' {$country['BA']}>Bosnia and Herzegovina</option>
    <option value='BG' {$country['BG']}>Bulgaria</option>
    <option value='HR' {$country['HR']}>Croatia (Hrvatska)</option>
    <option value='CY' {$country['CY']}>Cyprus</option>
    <option value='CZ' {$country['CZ']}>Czech Republic</option>
    <option value='FR' {$country['FR']}>France</option>
    <option value='GI' {$country['GI']}>Gibraltar</option>
    <option value='DE' {$country['DE']}>Germany</option>
    <option value='GR' {$country['GR']}>Greece</option>
    <option value='VA' {$country['VA']}>Holy See (Vatican City State)</option>
    <option value='HU' {$country['HU']}>Hungary</option>
    <option value='IT' {$country['IT']}>Italy</option>
    <option value='LI' {$country['LI']}>Liechtenstein</option>
    <option value='LU' {$country['LU']}>Luxembourg</option>
    <option value='MK' {$country['MK']}>Macedonia</option>
    <option value='MT' {$country['MT']}>Malta</option>
    <option value='MD' {$country['MD']}>Moldova</option>
    <option value='MC' {$country['MC']}>Monaco</option>
    <option value='ME' {$country['ME']}>Montenegro</option>
    <option value='NL' {$country['NL']}>Netherlands</option>
    <option value='PL' {$country['PL']}>Poland</option>
    <option value='PT' {$country['PT']}>Portugal</option>
    <option value='RO' {$country['RO']}>Romania</option>
    <option value='SM' {$country['SM']}>San Marino</option>
    <option value='RS' {$country['RS']}>Serbia</option>
    <option value='SK' {$country['SK']}>Slovakia</option>
    <option value='SI' {$country['SI']}>Slovenia</option>
    <option value='ES' {$country['ES']}>Spain</option>
    <option value='UA' {$country['UA']}>Ukraine</option>
    <option value='DK' {$country['DK']}>Denmark</option>
    <option value='EE' {$country['EE']}>Estonia</option>
    <option value='FO' {$country['FO']}>Faroe Islands</option>
    <option value='FI' {$country['FI']}>Finland</option>
    <option value='GL' {$country['GL']}>Greenland</option>
    <option value='IS' {$country['IS']}>Iceland</option>
    <option value='IE' {$country['IE']}>Ireland</option>
    <option value='LV' {$country['LV']}>Latvia</option>
    <option value='LT' {$country['LT']}>Lithuania</option>
    <option value='NO' {$country['NO']}>Norway</option>
    <option value='SJ' {$country['SJ']}>Svalbard and Jan Mayen Islands</option>
    <option value='SE' {$country['SE']}>Sweden</option>
    <option value='CH' {$country['CH']}>Switzerland</option>
    <option value='TR' {$country['TR']}>Turkey</option>
  </optgroup>
  <optgroup label='Asia'>
    <option value='AF' {$country['AF']}>Afghanistan</option>
    <option value='AM' {$country['AM']}>Armenia</option>
    <option value='AZ' {$country['AZ']}>Azerbaijan</option>
    <option value='BH' {$country['BH']}>Bahrain</option>
    <option value='BD' {$country['BD']}>Bangladesh</option>
    <option value='BT' {$country['BT']}>Bhutan</option>
    <option value='IO' {$country['IO']}>British Indian Ocean Territory</option>
    <option value='BN' {$country['BN']}>Brunei Darussalam</option>
    <option value='KH' {$country['KH']}>Cambodia</option>
    <option value='CN' {$country['CN']}>China</option>
    <option value='CX' {$country['CX']}>Christmas Island</option>
    <option value='CC' {$country['CC']}>Cocos (Keeling) Islands</option>
    <option value='GE' {$country['GE']}>Georgia</option>
    <option value='HK' {$country['HK']}>Hong Kong</option>
    <option value='IN' {$country['IN']}>India</option>
    <option value='ID' {$country['ID']}>Indonesia</option>
    <option value='IR' {$country['IR']}>Iran</option>
    <option value='IQ' {$country['IQ']}>Iraq</option>
    <option value='IL' {$country['IL']}>Israel</option>
    <option value='JP' {$country['JP']}>Japan</option>
    <option value='JO' {$country['JO']}>Jordan</option>
    <option value='KZ' {$country['KZ']}>Kazakhstan</option>
    <option value='KP' {$country['KP']}>Korea, Democratic People's Republic of</option>
    <option value='KR' {$country['KR']}>Korea, Republic of</option>
    <option value='KW' {$country['KW']}>Kuwait</option>
    <option value='KG' {$country['KG']}>Kyrgyzstan</option>
    <option value='LA' {$country['LA']}>Lao</option>
    <option value='LB' {$country['LB']}>Lebanon</option>
    <option value='MY' {$country['MY']}>Malaysia</option>
    <option value='MV' {$country['MV']}>Maldives</option>
    <option value='MN' {$country['MN']}>Mongolia</option>
    <option value='MM' {$country['MM']}>Myanmar (Burma)</option>
    <option value='NP' {$country['NP']}>Nepal</option>
    <option value='OM' {$country['OM']}>Oman</option>
    <option value='PK' {$country['PK']}>Pakistan</option>
    <option value='PH' {$country['PH']}>Philippines</option>
    <option value='QA' {$country['QA']}>Qatar</option>
    <option value='RU' {$country['RU']}>Russian Federation</option>
    <option value='SA' {$country['SA']}>Saudi Arabia</option>
    <option value='SG' {$country['SG']}>Singapore</option>
    <option value='LK' {$country['LK']}>Sri Lanka</option>
    <option value='SY' {$country['SY']}>Syria</option>
    <option value='TW' {$country['TW']}>Taiwan</option>
    <option value='TJ' {$country['TJ']}>Tajikistan</option>
    <option value='TH' {$country['TH']}>Thailand</option>
    <option value='TP' {$country['TP']}>East Timor</option>
    <option value='TM' {$country['TM']}>Turkmenistan</option>
    <option value='AE' {$country['AE']}>United Arab Emirates</option>
    <option value='UZ' {$country['UZ']}>Uzbekistan</option>
    <option value='VN' {$country['VN']}>Vietnam</option>
    <option value='YE' {$country['YE']}>Yemen</option>
  </optgroup>
  <optgroup label='Australia / Oceania'>
    <option value='AS' {$country['AS']}>American Samoa</option>
    <option value='AU' {$country['AU']}>Australia</option>
    <option value='CK' {$country['CK']}>Cook Islands</option>
    <option value='FJ' {$country['FJ']}>Fiji</option>
    <option value='PF' {$country['PF']}>French Polynesia (Tahiti)</option>
    <option value='GU' {$country['GU']}>Guam</option>
    <option value='KB' {$country['KB']}>Kiribati</option>
    <option value='MH' {$country['MH']}>Marshall Islands</option>
    <option value='FM' {$country['FM']}>Micronesia, Federated States of</option>
    <option value='NR' {$country['NR']}>Nauru</option>
    <option value='NC' {$country['NC']}>New Caledonia</option>
    <option value='NZ' {$country['NZ']}>New Zealand</option>
    <option value='NU' {$country['NU']}>Niue</option>
    <option value='MP' {$country['MP']}>Northern Mariana Islands</option>
    <option value='PW' {$country['PW']}>Palau</option>
    <option value='PG' {$country['PG']}>Papua New Guinea</option>
    <option value='PN' {$country['PN']}>Pitcairn</option>
    <option value='WS' {$country['WS']}>Samoa</option>
    <option value='SB' {$country['SB']}>Solomon Islands</option>
    <option value='TK' {$country['TK']}>Tokelau</option>
    <option value='TO' {$country['TO']}>Tonga</option>
    <option value='TV' {$country['TV']}>Tuvalu</option>
    <option value='VU' {$country['VU']}>Vanuatu</option>
    <option valud='WF' {$country['WF']}>Wallis and Futuna Islands</option>
  </optgroup>
  <optgroup label='Africa'>
    <option value='DZ' {$country['DZ']}>Algeria</option>
    <option value='AO' {$country['AO']}>Angola</option>
    <option value='BJ' {$country['BJ']}>Benin</option>
    <option value='BW' {$country['BW']}>Botswana</option>
    <option value='BF' {$country['BF']}>Burkina Faso</option>
    <option value='BI' {$country['BI']}>Burundi</option>
    <option value='CM' {$country['CM']}>Cameroon</option>
    <option value='CV' {$country['CV']}>Cape Verde</option>
    <option value='CF' {$country['CF']}>Central African Republic</option>
    <option value='TD' {$country['TD']}>Chad</option>
    <option value='KM' {$country['KM']}>Comoros</option>
    <option value='CG' {$country['CG']}>Congo</option>
    <option value='CD' {$country['CD']}>Congo, the Democratic Republic of the</option>
    <option value='DJ' {$country['DJ']}>Dijibouti</option>
    <option value='EG' {$country['EG']}>Egypt</option>
    <option value='GQ' {$country['GQ']}>Equatorial Guinea</option>
    <option value='ER' {$country['ER']}>Eritrea</option>
    <option value='ET' {$country['ET']}>Ethiopia</option>
    <option value='GA' {$country['GA']}>Gabon</option>
    <option value='GM' {$country['GM']}>Gambia</option>
    <option value='GH' {$country['GH']}>Ghana</option>
    <option value='GN' {$country['GN']}>Guinea</option>
    <option value='GW' {$country['GW']}>Guinea-Bissau</option>
    <option value='CI' {$country['CI']}>Cote d'Ivoire (Ivory Coast)</option>
    <option value='KE' {$country['KE']}>Kenya</option>
    <option value='LS' {$country['LS']}>Lesotho</option>
    <option value='LR' {$country['LR']}>Liberia</option>
    <option value='LY' {$country['LY']}>Libya</option>
    <option value='MG' {$country['MG']}>Madagascar</option>
    <option value='MW' {$country['MW']}>Malawi</option>
    <option value='ML' {$country['ML']}>Mali</option>
    <option value='MR' {$country['MR']}>Mauritania</option>
    <option value='MU' {$country['MU']}>Mauritius</option>
    <option value='YT' {$country['YT']}>Mayotte</option>
    <option value='MA' {$country['MA']}>Morocco</option>
    <option value='MZ' {$country['MZ']}>Mozambique</option>
    <option value='NA' {$country['NA']}>Namibia</option>
    <option value='NE' {$country['NE']}>Niger</option>
    <option value='NG' {$country['NG']}>Nigeria</option>
    <option value='RE' {$country['RE']}>Reunion</option>
    <option value='RW' {$country['RW']}>Rwanda</option>
    <option value='ST' {$country['ST']}>Sao Tome and Principe</option>
    <option value='SH' {$country['SH']}>Saint Helena</option>
    <option value='SN' {$country['SN']}>Senegal</option>
    <option value='SC' {$country['SC']}>Seychelles</option>
    <option value='SL' {$country['SL']}>Sierra Leone</option>
    <option value='SO' {$country['SO']}>Somalia</option>
    <option value='ZA' {$country['ZA']}>South Africa</option>
    <option value='SS' {$country['SS']}>South Sudan</option>
    <option value='SD' {$country['SD']}>Sudan</option>
    <option value='SZ' {$country['SZ']}>Swaziland</option>
    <option value='TZ' {$country['TZ']}>Tanzania</option>
    <option value='TG' {$country['TG']}>Togo</option>
    <option value='TN' {$country['TN']}>Tunisia</option>
    <option value='UG' {$country['UG']}>Uganda</option>
    <option value='EH' {$country['EH']}>Western Sahara</option>
    <option value='ZM' {$country['ZM']}>Zambia</option>
    <option value='ZW' {$country['ZW']}>Zimbabwe</option>
  </optgroup>
  <option value='AQ' {$country['AQ']}>Antarctica</option>";
  }

  public function getStateOpts($selState) {
    $state = array(
      'AL' => '', 'AK' => '', 'AZ' => '', 'AR' => '', 'CA' => '', 'CO' => '', 'CT' => '', 'DE' => '', 'DC' => '', 'FL' => '', 'GA' => '', 'HI' => '', 'ID' => '',
      'IL' => '', 'IN' => '', 'IA' => '', 'KS' => '', 'KY' => '', 'LA' => '', 'ME' => '', 'MD' => '', 'MA' => '', 'MI' => '', 'MN' => '', 'MS' => '', 'MO' => '',
      'MT' => '', 'NE' => '', 'NV' => '', 'NH' => '', 'NJ' => '', 'NM' => '', 'NY' => '', 'NC' => '', 'ND' => '', 'OH' => '', 'OK' => '', 'OR' => '', 'PA' => '',
      'RI' => '', 'SC' => '', 'SD' => '', 'TN' => '', 'TX' => '', 'UT' => '', 'VT' => '', 'VA' => '', 'WA' => '', 'WV' => '', 'WI' => '', 'WY' => '');

    if(!empty($selState)) {
      $state[$selState] = 'selected';
    } else {
      $state['NC'] = 'selected';
    }


    return "
  <option value='AL' {$state['AL']}>Alabama</option>
  <option value='AK' {$state['AK']}>Alaska</option>
  <option value='AZ' {$state['AZ']}>Arizona</option>
  <option value='AR' {$state['AR']}>Arkansas</option>
  <option value='CA' {$state['CA']}>California</option>
  <option value='CO' {$state['CO']}>Colorado</option>
  <option value='CT' {$state['CT']}>Connecticut</option>
  <option value='DE' {$state['DE']}>Delaware</option>
  <option value='DC' {$state['DC']}>District Of Columbia</option>
  <option value='FL' {$state['FL']}>Florida</option>
  <option value='GA' {$state['GA']}>Georgia</option>
  <option value='HI' {$state['HI']}>Hawaii</option>
  <option value='ID' {$state['ID']}>Idaho</option>
  <option value='IL' {$state['IL']}>Illinois</option>
  <option value='IN' {$state['IN']}>Indiana</option>
  <option value='IA' {$state['IA']}>Iowa</option>
  <option value='KS' {$state['KS']}>Kansas</option>
  <option value='KY' {$state['KY']}>Kentucky</option>
  <option value='LA' {$state['LA']}>Louisiana</option>
  <option value='ME' {$state['ME']}>Maine</option>
  <option value='MD' {$state['MD']}>Maryland</option>
  <option value='MA' {$state['MA']}>Massachusetts</option>
  <option value='MI' {$state['MI']}>Michigan</option>
  <option value='MN' {$state['MN']}>Minnesota</option>
  <option value='MS' {$state['MS']}>Mississippi</option>
  <option value='MO' {$state['MO']}>Missouri</option>
  <option value='MT' {$state['MT']}>Montana</option>
  <option value='NE' {$state['NE']}>Nebraska</option>
  <option value='NV' {$state['NV']}>Nevada</option>
  <option value='NH' {$state['NH']}>New Hampshire</option>
  <option value='NJ' {$state['NJ']}>New Jersey</option>
  <option value='NM' {$state['NM']}>New Mexico</option>
  <option value='NY' {$state['NY']}>New York</option>
  <option value='NC' {$state['NC']}>North Carolina</option>
  <option value='ND' {$state['ND']}>North Dakota</option>
  <option value='OH' {$state['OH']}>Ohio</option>
  <option value='OK' {$state['OK']}>Oklahoma</option>
  <option value='OR' {$state['OR']}>Oregon</option>
  <option value='PA' {$state['PA']}>Pennsylvania</option>
  <option value='RI' {$state['RI']}>Rhode Island</option>
  <option value='SC' {$state['SC']}>South Carolina</option>
  <option value='SD' {$state['SD']}>South Dakota</option>
  <option value='TN' {$state['TN']}>Tennessee</option>
  <option value='TX' {$state['TX']}>Texas</option>
  <option value='UT' {$state['UT']}>Utah</option>
  <option value='VT' {$state['VT']}>Vermont</option>
  <option value='VA' {$state['VA']}>Virginia</option>
  <option value='WA' {$state['WA']}>Washington</option>
  <option value='WV' {$state['WV']}>West Virginia</option>
  <option value='WI' {$state['WI']}>Wisconsin</option>
  <option value='WY' {$state['WY']}>Wyoming</option>";
  }
}