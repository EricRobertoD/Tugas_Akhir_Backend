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
        $credentials = getenv('GOOGLE_API_CREDENTIALS');
        if ($credentials === false) {
            throw new \Exception("GOOGLE_API_CREDENTIALS environment variable not set");
        }

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => json_decode($credentials, true)
        ]);
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
