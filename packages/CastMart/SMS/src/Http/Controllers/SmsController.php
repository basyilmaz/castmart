<?php

namespace CastMart\SMS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\SMS\Services\SmsService;
use CastMart\SMS\Models\SmsLog;
use CastMart\SMS\Models\OtpVerification;

class SmsController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * SMS yönetimi ana sayfası
     */
    public function index(Request $request)
    {
        $logs = SmsLog::when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->provider, fn($q, $p) => $q->where('provider', $p))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('phone', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total_today' => SmsLog::today()->count(),
            'sent_today' => SmsLog::today()->sent()->count(),
            'failed_today' => SmsLog::today()->failed()->count(),
            'balance' => $this->smsService->getBalance(),
        ];

        return view('castmart-sms::admin.index', compact('logs', 'stats'));
    }

    /**
     * Tek SMS gönder
     */
    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'message' => 'required|string|max:918', // 6 SMS karakter limiti
        ]);

        $result = $this->smsService->send($request->phone, $request->message);

        return response()->json($result);
    }

    /**
     * Toplu SMS gönder
     */
    public function sendBulk(Request $request)
    {
        $request->validate([
            'phones' => 'required|array|min:1',
            'phones.*' => 'string|min:10|max:15',
            'message' => 'required|string|max:918',
        ]);

        $result = $this->smsService->sendBulk($request->phones, $request->message);

        return response()->json($result);
    }

    /**
     * OTP gönder (API)
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $result = $this->smsService->sendOtp($request->phone);

        return response()->json($result);
    }

    /**
     * OTP doğrula (API)
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'code' => 'required|string|size:6',
        ]);

        $result = $this->smsService->verifyOtp($request->phone, $request->code);

        return response()->json($result);
    }

    /**
     * SMS durumu sorgula
     */
    public function getStatus(Request $request)
    {
        $request->validate([
            'message_id' => 'required|string',
        ]);

        $result = $this->smsService->getStatus($request->message_id);

        return response()->json($result);
    }

    /**
     * Bakiye sorgula
     */
    public function getBalance()
    {
        $result = $this->smsService->getBalance();

        return response()->json($result);
    }

    /**
     * İstatistikler
     */
    public function statistics(Request $request)
    {
        $days = $request->input('days', 7);

        $dailyStats = SmsLog::selectRaw('DATE(created_at) as date, COUNT(*) as total, 
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        $providerStats = SmsLog::selectRaw('provider, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('provider')
            ->get();

        return response()->json([
            'success' => true,
            'daily' => $dailyStats,
            'by_provider' => $providerStats,
            'total' => SmsLog::count(),
            'today' => $this->smsService->getTodayCount(),
        ]);
    }

    /**
     * Şablon yönetimi
     */
    public function templates()
    {
        $templates = config('castmart-sms.templates');

        return view('castmart-sms::admin.templates', compact('templates'));
    }

    /**
     * Test SMS gönder
     */
    public function testSms(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $result = $this->smsService->send(
            $request->phone,
            'CastMart SMS testi başarılı! ' . now()->format('d.m.Y H:i:s')
        );

        return response()->json($result);
    }
}
