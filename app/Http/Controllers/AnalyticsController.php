<?php
// app/Http/Controllers/AnalyticsController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\AnalyticsReporting;
use Google\Service\AnalyticsReporting\DateRange;
use Google\Service\AnalyticsReporting\Dimension;
use Google\Service\AnalyticsReporting\Metric;
use Google\Service\AnalyticsReporting\ReportRequest;
use Google\Service\AnalyticsReporting\GetReportsRequest;

class AnalyticsController extends Controller
{
    public function getLoginStats()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->addScope(AnalyticsReporting::ANALYTICS_READONLY);

        $analytics = new AnalyticsReporting($client);

        $VIEW_ID = "YOUR_VIEW_ID";

        $dateRange = new DateRange();
        $dateRange->setStartDate("30daysAgo");
        $dateRange->setEndDate("today");

        $eventMetric = new Metric();
        $eventMetric->setExpression("ga:totalEvents");
        $eventMetric->setAlias("totalEvents");

        $eventDimension = new Dimension();
        $eventDimension->setName("ga:eventCategory");

        $request = new ReportRequest();
        $request->setViewId($VIEW_ID);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$eventMetric]);
        $request->setDimensions([$eventDimension]);

        $body = new GetReportsRequest();
        $body->setReportRequests([$request]);
        $reports = $analytics->reports->batchGet($body);

        return response()->json($reports);
    }
}
