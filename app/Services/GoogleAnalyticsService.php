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
        $privateKey = str_replace("\\n", "\n", getenv('private_key'));

        $credentials = [
            "type" =>"service_account",
            "project_id" => getenv('project_id'),
            "private_key_id" => getenv('private_key_id'),
            "private_key" => $privateKey,
            "client_email" => getenv('client_email'),
            "client_id" => getenv('client_id'),
            "auth_uri" => getenv('auth_uri'),
            "token_uri" => getenv('token_uri'),
            "auth_provider_x509_cert_url" => getenv('auth_provider_x509_cert_url'),
            "client_x509_cert_url" => getenv('client_x509_cert_url'),
            "universe_domain" => getenv('universe_domain')
        ];
    
    
        foreach ($credentials as $key => $value) {
            if ($value === false) {
                throw new \Exception("Environment variable for $key not set");
            }
        }

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $credentials
        ]);
    
    }
    
    public function getRealTimeData()
    {
        $PROPERTY_ID = 'properties/443953748';

        $dimensions = [
            (new Dimension())->setName('minutesAgo'),
        ];

        $metrics = [
            (new Metric())->setName('activeUsers'),
        ];

        $request = [
            'property' => $PROPERTY_ID,
            'dimensions' => $dimensions,
            'metrics' => $metrics,
        ];

        try {
            $response = $this->client->runRealtimeReport($request);
            return json_decode($response->serializeToJsonString(), true);
        } catch (\Exception $e) {
            Log::error('Error fetching real-time data:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to get real-time data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    public function getLateTimeData()
    {
        $PROPERTY_ID = 'properties/443953748';

        $dimensions = [
            (new Dimension())->setName('date'),
        ];

        $metrics = [
            (new Metric())->setName('activeUsers'),
        ];

        $requests = [];
        $dateRanges = [
            new DateRange(['start_date' => '30daysAgo', 'end_date' => 'today']),
            new DateRange(['start_date' => '15daysAgo', 'end_date' => 'today']),
            new DateRange(['start_date' => '3daysAgo', 'end_date' => 'today']),
        ];
        foreach ($dateRanges as $dateRange) {
            $requests[] = [
                'property' => $PROPERTY_ID,
                'dateRanges' => [$dateRange],
                'dimensions' => $dimensions,
                'metrics' => $metrics,
            ];
        }

        $responses = [];
        foreach ($requests as $request) {
            $response = $this->client->runReport($request);
            $responses[] = json_decode($response->serializeToJsonString(), true);
        }

        return $responses;
    }

    

    //unifiedScreenName
    //fullPageUrl
    //unified
}
