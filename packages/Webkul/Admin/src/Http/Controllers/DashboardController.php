<?php

namespace Webkul\Admin\Http\Controllers;

use Webkul\Admin\Helpers\Dashboard;

class DashboardController extends Controller
{
    /**
     * Request param functions
     *
     * @var array
     */
    protected $typeFunctions = [
        'over-all'                 => 'getOverAllStats',
        'today'                    => 'getTodayStats',
        'stock-threshold-products' => 'getStockThresholdProducts',
        'total-sales'              => 'getSalesStats',
        'total-visitors'           => 'getVisitorStats',
        'top-selling-products'     => 'getTopSellingProducts',
        'top-customers'            => 'getTopCustomers',
    ];

    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(protected Dashboard $dashboardHelper) {}

    /**
     * Dashboard page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return view('admin::dashboard.index')->with([
            'startDate' => $this->dashboardHelper->getStartDate(),
            'endDate'   => $this->dashboardHelper->getEndDate(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        try {
            $type = request()->query('type');
            
            if (!isset($this->typeFunctions[$type])) {
                return response()->json(['error' => 'Invalid type: ' . $type], 400);
            }
            
            $stats = $this->dashboardHelper->{$this->typeFunctions[$type]}();

            return response()->json([
                'statistics' => $stats,
                'date_range' => $this->dashboardHelper->getDateRange(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage(), [
                'type' => request()->query('type'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}

