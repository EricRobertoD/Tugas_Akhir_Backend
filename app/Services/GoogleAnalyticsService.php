<?php

namespace App\Services;

use Carbon\Carbon;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Illuminate\Support\Facades\Log;

class GoogleAnalyticsService
{
    protected $client;

    public function __construct()
    {
        $credentials = [
            "type" => getenv('type'),
            "project_id" => getenv('project_id'),
            "private_key_id" => getenv('private_key_id'),
            "private_key" => getenv('private_key'),
            "client_email" => getenv('client_email'),
            "client_id" => getenv('client_id'),
            "auth_uri" => getenv('auth_uri'),
            "token_uri" => getenv('token_uri'),
            "auth_provider_x509_cert_url" => getenv('auth_provider_x509_cert_url'),
            "client_x509_cert_url" => getenv('client_x509_cert_url'),
            "universe_domain" => getenv('universe_domain')
        ];
    
        // Log the environment variables
        Log::info('Environment Variables:', $credentials);
    
        // Check for any missing values
        foreach ($credentials as $key => $value) {
            if ($value === false) {
                throw new \Exception("Environment variable for $key not set");
            }
        }
    
    }


    public function getAllRawData()
    {
        $PROPERTY_ID = 'properties/443953748';

        $dateRanges = [
            new DateRange(['start_date' => '30daysAgo', 'end_date' => 'today']),
            new DateRange(['start_date' => '7daysAgo', 'end_date' => 'today']),
            new DateRange(['start_date' => 'today', 'end_date' => 'today'])
        ];

        // Define dimensions
        $dimensions = [
            (new Dimension())->setName('eventName'),
            (new Dimension())->setName('customEvent:role'),
            (new Dimension())->setName('pagePath'),
            (new Dimension())->setName('pageTitle'),
            (new Dimension())->setName('date'),
            (new Dimension())->setName('sessionSource'),
        ];

        // Define metrics
        $metrics = [
            (new Metric())->setName('eventCount'),
            (new Metric())->setName('sessions'),
            (new Metric())->setName('totalUsers'),
            (new Metric())->setName('newUsers'),
        ];

        // Prepare the request array
        $requests = [];
        foreach ($dateRanges as $dateRange) {
            $requests[] = [
                'property' => $PROPERTY_ID,
                'dateRanges' => [$dateRange],
                'dimensions' => $dimensions,
                'metrics' => $metrics,
            ];
        }

        // Send requests and collect responses
        $responses = [];
        foreach ($requests as $request) {
            $response = $this->client->runReport($request);
            $responses[] = json_decode($response->serializeToJsonString(), true);
        }

        return $responses;
    }
}
