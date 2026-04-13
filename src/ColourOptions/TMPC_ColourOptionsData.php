<?php

    namespace TMProductConfigurator\ColourOptions;

    class TMPC_ColourOptionsData {

        /**
         * Fetches colour options from Google Sheets and caches the result.
         *
         * @param bool $bypassToken Whether to bypass token verification (internal calls)
         * @return \WP_REST_Response
         */
        public static function getDataFromGoogleSheets($bypassToken = false) {

            // Get expected token from environment variable
            $expected_token = $_ENV['TMPC_UPDATE_TOKEN'] ?? null;

            // Get provided token from request headers
            $provided_token = $_SERVER['HTTP_X_TMPC_TOKEN'] ?? null;

            // If not bypassing and expected token is set and does not match the provided token, return a 403 error
            if (!$bypassToken && (!$expected_token || $provided_token !== $expected_token)) {
                return new \WP_Error('forbidden', 'Invalid or missing token', array('status' => 403));
            }

            // fetch data from Google Sheets API using the Google API PHP Client
            require_once(TMPC_PATH . '/google-api-client/vendor/autoload.php');

            // Set up Google Client with credentials and cell ranges
            $client = new \Google_Client();
            $client->setApplicationName($_ENV['GOOGLE_APPLICATION_NAME'] ?? '');
            $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
            $client->setAccessType('offline');
            $client->setAuthConfig(TMPC_PATH . ($_ENV['GOOGLE_PRIVATE_KEY_PATH'] ?? ''));
            $service = new \Google_Service_Sheets($client);
            $spreadsheetId = $_ENV['GOOGLE_SPREADSHEET_ID'] ?? '';
            $range = $_ENV['GOOGLE_RANGE'] ?? '';
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            // Update admin data
            self::formatAdminData($values);

            // Update frontend data
            self::formatFrontEndData($values);

        }

        /**
         * Method to format admin colour options for backend dropdown
         * and update transient cache for use in product edit screen
         * @param array $values
         * @return void
         */
        public static function formatAdminData($values) {

            // Array to hold final data
            $admin_colours = [];

            // Ensure we have data and at least a header row
            if (count($values) > 1) {

                // Extract headers from the first row
                $headers = $values[0];

                // Convert header values to keys (e.g. "Top Colour" => "top_colour") for easier access
                $colMap = array_flip($headers); 

                // Find the index of the 'Data End' column, if it exists
                $dataEndIndex = array_search('Data End', $headers);

                foreach (array_slice($values, 1) as $row) {

                    // Remove the 'Data End' value from the row if present
                    if ($dataEndIndex !== false && isset($row[$dataEndIndex])) {
                        var_dump('Removing Data End column from row: ' . $row[$dataEndIndex]);
                        unset($row[$dataEndIndex]);
                        unset($headers[$dataEndIndex]); // Remove the 'Data End' value from the row
                    }

                    // Skip incomplete rows
                    if (count($row) < 2) continue;

                    // Convert comma-separated values for base and metal values into arrays
                    $baseColours = self::processValue($row[$colMap['Base Colours']]);
                    $metalColours = self::processValue($row[$colMap['Metal Colours']]);

                    // Only include entries where at least one of base or edge options is provided
                    if (!empty($baseColours) || !empty($metalColours)) {

                        // Extract top type value and convert to lowercase, hyphenated format for object key
                        $top_type_raw = trim($row[$colMap['Top Type']]);
                        $top_type = strtolower(str_replace('/', '-', $top_type_raw));

                        // Combine headers and values to give assoc array for easy access to values
                        $combined_row = array_combine($headers, $row);

                        // Build data array, only include 'edge' if not empty
                        sort($baseColours);
                        
                        // Build data array, base colours first as these are present for all top types
                        $data = ['base' => $baseColours];

                        // If top type is 'slim/edge', create data for both 'slim' and 'edge' keys with the same data
                        if (strtolower(str_replace(' ', '', $top_type_raw)) === 'slim/edge') {

                            // In the case of slim include bases only
                            $admin_colours['slim'][$combined_row['Top Colour']] = $data;

                            // In the case of edge include bases and edges
                            if (!empty($metalColours)) {
                                sort($metalColours);
                                $data['metal'] = $metalColours;
                            }

                            // In the case of edge include bases and edges
                            $admin_colours['edge'][$combined_row['Top Colour']] = $data;

                        } else {
                            // Store the processed data in the final array
                            $admin_colours[$top_type][$combined_row['Top Colour']] = $data;
                        }
                    }
                }
            }

            // Set transient for admin area data to power default colours dropdown in product edit screen
            set_transient('tmpc_admin_sheets_data', $admin_colours, 2592000);

        }

        /**
         * Format frontend data returned from API 
         * and update transient cache for use in product configurator logic in the browser
         *
         * @param array $values
         * @return void
         */
        public static function formatFrontEndData($values) {

            // Array to hold final data
            $colour_options = [];

            // Ensure we have data and at least a header row
            if (count($values) > 1) {

                // Extract headers from the first row
                $headers = $values[0];

                // Convert header values to keys (e.g. "Top Colour" => "top_colour") for easier access
                $colMap = array_flip($headers); 

                // Find the index of the 'Data End' column, if it exists
                $dataEndIndex = array_search('Data End', $headers);

                foreach (array_slice($values, 1) as $row) {

                    // Remove the 'Data End' value from the row if present
                    if ($dataEndIndex !== false && isset($row[$dataEndIndex])) {
                        unset($row[$dataEndIndex]);
                    }

                    // Skip incomplete rows
                    if (count($row) < 2) continue;

                    // Process base and edge values into arrays (handles comma-separated values)
                    $baseColours = self::processValue($row[$colMap['Base Colours']]);
                    $metalColours = self::processValue($row[$colMap['Metal Colours']]);

                    // Only include entries where at least one of base or edge options is provided
                    if (!empty($baseColours) || !empty($metalColours)) {

                        // Convert top colour value to correct format for object key
                        $top_colour = strtolower(str_replace(' ', '_', trim($row[$colMap['Top Colour']])));

                        // Extract top type value and convert to lowercase, hyphenated format for object key
                        $top_type_raw = trim($row[$colMap['Top Type']]);
                        $top_type = strtolower(str_replace('/', '-', $top_type_raw));

                        // Build data array, only include 'edge' if not empty
                        $data = ['base' => $baseColours];
                        if (!empty($metalColours)) {
                            $data['metal'] = $metalColours;
                        }

                        // If top type is 'slim/edge', create data for both 'slim' and 'edge' keys with the same data
                        if (strtolower(str_replace(' ', '', $top_type_raw)) === 'slim/edge') {

                            // In the case of slim include bases only
                            $colour_options[$top_colour]['slim'] = ['base' => $baseColours];

                            // In the case of edge include bases and edges
                            $colour_options[$top_colour]['edge'] = $data;

                        } else {
                            // Store the processed data in the final array
                            $colour_options[$top_colour][$top_type] = $data;
                        }
                    }
                }
            }

            // Cache for 30 days
            set_transient('tmpc_sheets_data', $colour_options, 2592000);

        }

        /**
         * Process data value
         *
         * @param string $value
         * @return array
         */
        public static function processValue($value) {

            return array_filter(array_map(fn($v) => strtolower(trim($v)), preg_split('/,\s*/', $value)));

        }

    }