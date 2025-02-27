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

'accepted' => 'Het :attribute veld moet geaccepteerd zijn.',
'accepted_if' => 'Het :attribute veld moet geaccepteerd zijn wanneer :other :value is.',
'active_url' => 'Het :attribute veld moet een geldige URL zijn.',
'after' => 'Het :attribute veld moet een datum zijn na :date.',
'after_or_equal' => 'Het :attribute veld moet een datum zijn na of gelijk aan :date.',
'alpha' => 'Het :attribute veld mag alleen letters bevatten.',
'alpha_dash' => 'Het :attribute veld mag alleen letters, cijfers, streepjes en underscores bevatten.',
'alpha_num' => 'Het :attribute veld mag alleen letters en cijfers bevatten.',
'array' => 'Het :attribute veld moet een array zijn.',
'ascii' => 'Het :attribute veld mag alleen enkelbytes alfanumerieke tekens en symbolen bevatten.',
'before' => 'Het :attribute veld moet een datum zijn voor :date.',
'before_or_equal' => 'Het :attribute veld moet een datum zijn voor of gelijk aan :date.',
'between' => [
    'array' => 'Het :attribute veld moet tussen :min en :max items bevatten.',
    'file' => 'Het :attribute veld moet tussen :min en :max kilobytes zijn.',
    'numeric' => 'Het :attribute veld moet tussen :min en :max zijn.',
    'string' => 'Het :attribute veld moet tussen :min en :max karakters zijn.',
],
'boolean' => 'Het :attribute veld moet waar of onwaar zijn.',
'can' => 'Het :attribute veld bevat een ongeautoriseerde waarde.',
'confirmed' => 'De :attribute bevestiging komt niet overeen.',
'current_password' => 'Het wachtwoord is incorrect.',
'date' => 'Het :attribute veld moet een geldige datum zijn.',
'date_equals' => 'Het :attribute veld moet een datum zijn gelijk aan :date.',
'date_format' => 'Het :attribute veld moet overeenkomen met het formaat :format.',
'decimal' => 'Het :attribute veld moet :decimal decimalen bevatten.',
'declined' => 'Het :attribute veld moet geweigerd zijn.',
'declined_if' => 'Het :attribute veld moet geweigerd zijn wanneer :other :value is.',
'different' => 'Het :attribute veld en :other moeten verschillend zijn.',
'digits' => 'Het :attribute veld moet :digits cijfers bevatten.',
'digits_between' => 'Het :attribute veld moet tussen :min en :max cijfers bevatten.',
'dimensions' => 'Het :attribute veld heeft ongeldige afbeeldingsafmetingen.',
'distinct' => 'Het :attribute veld heeft een dubbele waarde.',
'doesnt_end_with' => 'Het :attribute veld mag niet eindigen met een van de volgende: :values.',
'doesnt_start_with' => 'Het :attribute veld mag niet beginnen met een van de volgende: :values.',
'email' => 'Het :attribute veld moet een geldig e-mailadres zijn.',
'ends_with' => 'Het :attribute veld moet eindigen met een van de volgende: :values.',
'enum' => 'De geselecteerde :attribute is ongeldig.',
'exists' => 'De geselecteerde :attribute is ongeldig.',
'extensions' => 'Het :attribute veld moet een van de volgende extensies hebben: :values.',
'file' => 'Het :attribute veld moet een bestand zijn.',
'filled' => 'Het :attribute veld moet een waarde hebben.',
'gt' => [
    'array' => 'Het :attribute veld moet meer dan :value items bevatten.',
    'file' => 'Het :attribute veld moet groter zijn dan :value kilobytes.',
    'numeric' => 'Het :attribute veld moet groter zijn dan :value.',
    'string' => 'Het :attribute veld moet groter zijn dan :value karakters.',
],
'gte' => [
    'array' => 'Het :attribute veld moet :value items of meer bevatten.',
    'file' => 'Het :attribute veld moet groter zijn dan of gelijk zijn aan :value kilobytes.',
    'numeric' => 'Het :attribute veld moet groter zijn dan of gelijk zijn aan :value.',
    'string' => 'Het :attribute veld moet groter zijn dan of gelijk zijn aan :value karakters.',
],
'hex_color' => 'Het :attribute veld moet een geldige hexadecimale kleur zijn.',
'image' => 'Het :attribute veld moet een afbeelding zijn.',
'in' => 'De geselecteerde :attribute is ongeldig.',
'in_array' => 'Het :attribute veld moet bestaan in :other.',
'integer' => 'Het :attribute veld moet een geheel getal zijn.',
'ip' => 'Het :attribute veld moet een geldig IP-adres zijn.',
'ipv4' => 'Het :attribute veld moet een geldig IPv4-adres zijn.',
'ipv6' => 'Het :attribute veld moet een geldig IPv6-adres zijn.',
'json' => 'Het :attribute veld moet een geldige JSON-string zijn.',
'lowercase' => 'Het :attribute veld moet in kleine letters zijn.',
'lt' => [
    'array' => 'Het :attribute veld moet minder dan :value items bevatten.',
    'file' => 'Het :attribute veld moet kleiner zijn dan :value kilobytes.',
    'numeric' => 'Het :attribute veld moet kleiner zijn dan :value.',
    'string' => 'Het :attribute veld moet kleiner zijn dan :value karakters.',
],
'lte' => [
    'array' => 'Het :attribute veld mag niet meer dan :value items bevatten.',
    'file' => 'Het :attribute veld moet kleiner zijn dan of gelijk zijn aan :value kilobytes.',
    'numeric' => 'Het :attribute veld moet kleiner zijn dan of gelijk zijn aan :value.',
    'string' => 'Het :attribute veld moet kleiner zijn dan of gelijk zijn aan
