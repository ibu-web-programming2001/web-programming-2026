<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/HttpClient.php';

/**
 * Countries and their languages, capitals, currencies and flags.
 * Data source: ONE static JSON file on GitHub (https://github.com/mledoze/countries) – no API key.
 *
 * Difference to Open-Meteo: this is not a search API, it is a whole file (~1.4 MB, 250 countries).
 * So we:
 *  - download it at most once per COUNTRIES_CACHE_HOURS and keep a copy in backend/cache/ (CACHING)
 *  - do the searching and filtering ourselves in PHP
 *  - if GitHub is down, keep using the old copy (the app keeps working)
 */
class CountryService
{
    const UNAVAILABLE = 'The country data is not available at the moment. Please try again later.';

    private $cache_file;
    private $countries = null;   // loaded once per request

    public function __construct($cache_file = null)
    {
        $this->cache_file = $cache_file ?? __DIR__ . '/../../cache/countries.json';
    }

    /** All countries (short form), optionally filtered by name (?search=) and/or language (?language=). */
    public function search($search = '', $language = '')
    {
        $search = mb_strtolower(trim((string) $search));
        $language = mb_strtolower(trim((string) $language));

        $result = [];
        foreach ($this->load() as $country) {
            if ($search !== '' && !$this->name_matches($country, $search)) {
                continue;
            }
            if ($language !== '' && !$this->speaks($country, $language)) {
                continue;
            }
            $result[] = $this->short($country);
        }
        usort($result, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $result;
    }

    /** One country by ISO code: 2 letters (BA) or 3 letters (BIH). */
    public function get_by_code($code)
    {
        $code = strtoupper(trim((string) $code));
        if (!preg_match('/^[A-Z]{2,3}$/', $code)) {
            throw new ValidationException('The country code must have 2 or 3 letters (e.g. BA or BIH).');
        }
        foreach ($this->load() as $country) {
            if ($country['cca2'] === $code || $country['cca3'] === $code) {
                return $this->details($country);
            }
        }
        throw new NotFoundException("No country with code '$code' was found.");
    }

    /** Every language once, with the number of countries where it is an official language. */
    public function languages()
    {
        $languages = [];
        foreach ($this->load() as $country) {
            foreach ($country['languages'] ?? [] as $code => $name) {
                $languages[$code] ??= ['code' => $code, 'name' => $name, 'country_count' => 0];
                $languages[$code]['country_count']++;
            }
        }
        $languages = array_values($languages);
        usort($languages, fn($a, $b) => $b['country_count'] <=> $a['country_count'] ?: strcmp($a['name'], $b['name']));
        return $languages;
    }

    /* ---------- helpers ---------- */

    /** Cached copy if it is fresh enough, otherwise download again. */
    private function load()
    {
        if ($this->countries !== null) {
            return $this->countries;
        }

        $max_age = (float) Config::COUNTRIES_CACHE_HOURS() * 3600;
        $has_cache = is_file($this->cache_file);
        if ($has_cache && time() - filemtime($this->cache_file) < $max_age) {
            $this->countries = json_decode(file_get_contents($this->cache_file), true);
            if (is_array($this->countries)) {
                return $this->countries;
            }
        }

        try {
            $data = HttpClient::get_json(Config::COUNTRIES_DATA_URL(), Config::EXTERNAL_API_TIMEOUT(), self::UNAVAILABLE);
            if (!isset($data[0]['cca2'])) {
                error_log('Countries file has an unexpected format (' . Config::COUNTRIES_DATA_URL() . ')');
                throw new ExternalServiceException(self::UNAVAILABLE);
            }
            if (!is_dir(dirname($this->cache_file))) {
                mkdir(dirname($this->cache_file), 0775, true);
            }
            file_put_contents($this->cache_file, json_encode($data));
            return $this->countries = $data;
        } catch (ExternalServiceException $e) {
            // GitHub not reachable -> an old copy is better than an error
            if ($has_cache) {
                $old = json_decode(file_get_contents($this->cache_file), true);
                if (is_array($old)) {
                    error_log('Countries download failed – using the old cached copy.');
                    return $this->countries = $old;
                }
            }
            throw $e;
        }
    }

    private function name_matches($country, $search)
    {
        $names = [$country['name']['common'], $country['name']['official'], $country['cca2'], $country['cca3']];
        foreach ($names as $name) {
            if (str_contains(mb_strtolower($name), $search)) {
                return true;
            }
        }
        return false;
    }

    /** $language can be a name ("bosnian") or a code ("bos"). */
    private function speaks($country, $language)
    {
        foreach ($country['languages'] ?? [] as $code => $name) {
            if ($code === $language || mb_strtolower($name) === $language) {
                return true;
            }
        }
        return false;
    }

    /** Only the fields a list needs. */
    private function short($country)
    {
        return [
            'code' => $country['cca2'],
            'name' => $country['name']['common'],
            'capital' => $country['capital'][0] ?? null,
            'region' => $country['region'],
            'flag' => $country['flag'] ?? null,
            'languages' => array_values($country['languages'] ?? [])
        ];
    }

    /** All fields for the detail view. The file uses objects {code: name} – we turn them into lists. */
    private function details($country)
    {
        $languages = [];
        foreach ($country['languages'] ?? [] as $code => $name) {
            $languages[] = ['code' => $code, 'name' => $name];
        }
        $currencies = [];
        foreach ($country['currencies'] ?? [] as $code => $currency) {
            $currencies[] = ['code' => $code, 'name' => $currency['name'] ?? null, 'symbol' => $currency['symbol'] ?? null];
        }
        $root = $country['idd']['root'] ?? '';
        $suffixes = $country['idd']['suffixes'] ?? [];

        return [
            'code' => $country['cca2'],
            'code3' => $country['cca3'],
            'name' => $country['name']['common'],
            'official_name' => $country['name']['official'],
            'capital' => $country['capital'][0] ?? null,
            'region' => $country['region'],
            'subregion' => $country['subregion'] ?? null,
            'flag' => $country['flag'] ?? null,
            'flag_url' => 'https://flagcdn.com/' . strtolower($country['cca2']) . '.svg',
            'languages' => $languages,
            'currencies' => $currencies,
            'calling_code' => count($suffixes) === 1 ? $root . $suffixes[0] : $root,
            'area_km2' => $country['area'] ?? null,
            'landlocked' => $country['landlocked'] ?? null,
            'borders' => $country['borders'] ?? [],
            'latitude' => $country['latlng'][0] ?? null,
            'longitude' => $country['latlng'][1] ?? null
        ];
    }
}
