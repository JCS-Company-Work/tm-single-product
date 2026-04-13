<?php

    namespace TMProductConfigurator\ColourOptions;

    use TMProductConfigurator\ColourOptions\TMPC_ColourOptionsData;

    /**
     * Service class to handle fetching colour options from cache or Google Sheets
     * and return it in the expected format for frontend use.
     */
    class TMPC_ColourOptionsService {

        /**
         * Fetches colour options from cache or Google Sheets if cache is empty.
         *
         * @return \WP_REST_Response
         */
        public static function getColourOptions() {

            // Get cached data from transient
            $cache_key = 'tmpc_sheets_data';
            $cached = get_transient($cache_key);

            // If cached data exists, return it
            if ($cached !== false) {
                return rest_ensure_response($cached);
            }

            // If no cached data, fetch from Google Sheets (internal call, bypass token)
            TMPC_ColourOptionsData::getDataFromGoogleSheets(true);

            // Return the freshly cached data
            return rest_ensure_response(get_transient($cache_key));

        }

        /**
         * Get raw colour options for admin (unformatted)
         *
         * @return mixed Returns cached data or false if cache is empty
         */
        public static function getAdminColourOptions() {

            // Get cached data from transient
            $cache_key = 'tmpc_admin_sheets_data';
            $cached = get_transient($cache_key);

            // If cached data exists, return it
            if ($cached !== false) {
                return $cached; // NOTE: no REST response needed internally
            }

            // Populate cache if missing
            TMPC_ColourOptionsData::getDataFromGoogleSheets(true);

            // Return the newly cached data
            return get_transient($cache_key);

        }
    }