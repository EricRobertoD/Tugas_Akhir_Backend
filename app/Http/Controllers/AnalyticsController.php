<?php

namespace App\Http\Controllers;

use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $googleAnalyticsService;

    public function __construct(GoogleAnalyticsService $googleAnalyticsService)
    {
        $this->googleAnalyticsService = $googleAnalyticsService;
    }

    public function getAllRawData()
    {
        $data = $this->googleAnalyticsService->getAllRawData();
        return response()->json($data);
    }
    
    public function getRealTimeData()
    {
        $data = $this->googleAnalyticsService->getRealTimeData();
        return response()->json($data);
    }

    public function getLateTimeData()
    {
        $data = $this->googleAnalyticsService->getLateTimeData();
        return response()->json($data);
    }
}
