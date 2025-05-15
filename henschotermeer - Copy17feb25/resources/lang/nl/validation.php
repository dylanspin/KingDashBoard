<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

	'Accepted' 				=> ' :attribute moet worden geaccepteerd.',
    'active_url' 			=> ' :attribute is geen geldige URL.',
    'after' 				=> ' :attribute moet een datum na :date zijn.',
    'after_or_equal' 		=> ' :attribute moet een datum na of gelijk zijn aan :date.',
    'alpha' 				=> ' :attribute mag alleen letters bevatten.',
    'alpha_dash' 			=> ' :attribute mag alleen letters, cijfers en streepjes bevatten.',
    'alpha_num' 			=> ' :attribute mag alleen letters en cijfers bevatten.',
    'array' 				=> ' :attribute moet een array zijn.',
    'before' 				=> ' :attribute moet een datum zijn vóór :date.',
    'before_or_equal' 		=> ' :attribute moet een datum vóór of gelijk zijn aan :date.',
    'between' 				=> [
        'numeric' 	=> ' :attribute moet tussen :min en :max zijn.',
        'file' 		=> ' :attribute moet tussen :min en :max kilobytes zijn.',
        'string' 	=> ' :attribute moet tussen :min en :max karakters zijn.',
        'array' 	=> ' :attribute moet tussen :min en :max items bevatten.',
    ],
    'boolean' 				=> ' :attribute veld moet waar of onwaar zijn.',
    'confirmated' 			=> ' :attribute bevestiging komt niet overeen.',
    'date' 					=> ' :attribute is geen geldige datum.',
    'date_format' 			=> ' :attribute komt niet overeen met het format: format.',
    'different' 			=> ' :attribute en: other moeten verschillend zijn.',
    'digits' 				=> ' :attribute moet: digits digits zijn.',
    'digits_between' 		=> ' :attribute moet tussen :min en :max cijfers zijn.',
    'Dimensions' 			=> ' :attribute heeft ongeldige afbeeldingsafmetingen.',
    'distinct' 				=> ' :attribute veld heeft een dubbele waarde.',
    'email' 				=> ' :attribute moet een geldig e-mailadres zijn.',
    'exist' 				=> 'Het geselecteerde: attribuut is ongeldig.',
    'file' 					=> ' :attribute moet een bestand zijn.',
    'filled' 				=> ' :attribute veld moet een waarde hebben.',
    'gt' 					=> [
        'numeric' 	=> ' :attribute moet groter zijn dan: waarde.',
        'file' 		=> ' :attribute moet groter zijn dan :value kilobytes.',
        'string' 	=> ' :attribute moet groter zijn dan :value karakters.',
        'array' 	=> ' :attribute moet meer dan :value items bevatten.',
    ],
    'gte' 					=> [
        'numeric' 	=> ' :attribute moet groter zijn dan of gelijk zijn aan: waarde.',
        'file' 		=> ' :attribute moet groter zijn dan of gelijk zijn aan :value kilobytes.',
        'string' 	=> ' :attribute moet groter zijn dan of gelijk zijn aan :value karakters.',
        'array' 	=> ' :attribute moet: waarde items of meer hebben.',
    ],
    'image' 				=> ' :attribute moet een afbeelding zijn.',
    'in' 					=> 'Het geselecteerde  :attribute is ongeldig.',
    'in_array' 				=> ' :attribute veld bestaat niet in: other.',
    'integer' 				=> ' :attribute moet een geheel getal zijn.',
    'ip' 					=> ' :attribute moet een geldig IP-adres zijn.',
    'ipv4' 					=> ' :attribute moet een geldig IPv4-adres zijn.',
    'ipv6' 					=> ' :attribute moet een geldig IPv6-adres zijn.',
    'json' 					=> ' :attribute moet een geldige JSON-string zijn.',
    'lt' 					=> [
        'numeric' 	=> ' :attribute moet kleiner zijn dan: waarde.',
        'file' 		=> ' :attribute moet kleiner zijn dan :value kilobytes.',
        'string' 	=> ' :attribute moet minder zijn dan :value karakters.',
        'array' 	=> ' :attribute moet minder dan :value items bevatten.',
    ],
    'lte' 					=> [
        'numeric' 	=> ' :attribute moet kleiner zijn dan of gelijk zijn aan :value.',
        'file' 		=> ' :attribute moet kleiner zijn dan of gelijk zijn aan :value kilobytes.',
        'string' 	=> ' :attribute moet kleiner zijn dan of gelijk zijn aan :value karakters.',
        'array' 	=> ' :attribute mag niet meer dan :value items bevatten.',
    ],
    'max' 					=> [
        'numeric' 	=> ' :attribute mag niet groter zijn dan :max.',
        'file' 		=> ' :attribute mag niet groter zijn dan :max kilobytes.',
        'string' 	=> ' :attribute mag niet groter zijn dan :max karakters.',
        'array' 	=> ' :attribute mag niet meer dan :max items bevatten.',
    ],
    'mimes' 				=> ' :attribute moet een bestand zijn van het type: :values.',
    'mimetypes' 			=> ' :attribute moet een bestand zijn van het type: :values.',
    'min' 					=> [
        'numeric' 	=> ' :attribute moet minstens :min zijn.',
        'file' 		=> ' :attribute moet minstens :min kilobytes zijn.',
        'string' 	=> ' :attribute moet minstens :min karakters zijn.',
        'array' 	=> ' :attribute moet minstens :min items bevatten.',
    ],
	'not_in' 				=> 'Het geselecteerde :attribuut is ongeldig.',
    'not_regex' 			=> ' :attribute formaat is ongeldig.',
    'numeric' 				=> ' :attribute moet een getal zijn.',
    'present' 				=> ' :attribute veld moet aanwezig zijn.',
    'regex' 				=> ' :attribute formaat is ongeldig.',
    'required' 				=> ' :attribute veld is verplicht.',
    'required_if' 			=> ' :attribute veld is verplicht als :other is :value.',
    'required_unless' 		=> ' :attribute veld is verplicht tenzij :other in :values ​​staat.',
    'required_with' 		=> ' :attribute veld is vereist als :values ​​aanwezig is.',
    'required_with_all' 	=> ' :attribute veld is verplicht als :values ​​aanwezig is.',
    'required_without' 		=> ' :attribute veld is verplicht als :values ​​niet aanwezig is.',
    'required_without_all' 	=> ' :attribute veld is vereist als geen van :values aanwezig is.',
    'same' 					=> ' :attribute en: other moeten overeenkomen.',
    'size' 					=> [
        'numeric' 	=> ' :attribute moet :size zijn.',
        'file' 		=> ' :attribute moet :size kilobytes zijn.',
        'string' 	=> ' :attribute moet :size karakters zijn.',
        'array' 	=> ' :attribute moet :size items bevatten.',
    ],
    'string' 				=> ' :attribute moet een string zijn.',
    'timezone' 				=> ' :attribute moet een geldige tijdzone zijn.',
    'unique' 				=> ' :attribute bestaat al.',
    'geüpload' 				=> ' :attribute kan niet worden geüpload.',
    'url' 					=> ' :attribute formaat is ongeldig.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
