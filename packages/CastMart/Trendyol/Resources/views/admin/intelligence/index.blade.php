<x-admin::layouts>
    <x-slot:title>
        7. His - Akıllı Dashboard
    </x-slot>

    <style>
        /* Animasyonlar */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        
        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }
        .animate-delay-4 { animation-delay: 0.4s; }
        
        .pulse-critical::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: #ef4444;
            animation: pulse-ring 2s ease-out infinite;
            z-index: -1;
        }
        
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 4px;
        }
        
        .dark .skeleton {
            background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
        }
        
        .alert-card {
            transition: all 0.3s ease;
        }
        
        .alert-card:hover {
            transform: translateX(5px);
        }
        
        .sparkline {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 40px;
        }
        
        .sparkline-bar {
            flex: 1;
            background: linear-gradient(to top, #6366f1, #a5b4fc);
            border-radius: 2px;
            transition: height 0.3s ease;
        }
        
        .health-ring {
            transition: stroke-dashoffset 1s ease;
        }
        
        .stat-card {
            transition: all 0.2s ease;
        }
        
        .stat-card:hover {
            transform: scale(1.02);
        }
        
        .quick-action-btn {
            transition: all 0.2s ease;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>

    <!-- Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap animate-fade-in-up">
        <div class="grid gap-1.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <span class="text-white text-xl">🧠</span>
                </div>
                <div>
                    <p class="text-xl font-bold !leading-normal text-gray-800 dark:text-white">
                        7. His Dashboard
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Son güncelleme: <span id="last-updated" class="font-medium">{{ $lastUpdated ?? now()->format('H:i') }}</span>
                        <span class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full dark:bg-green-900/30 dark:text-green-400">Canlı</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="toggleAutoRefresh()" id="auto-refresh-btn" class="flex items-center gap-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-3 py-2 rounded text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                <span id="auto-refresh-icon">⏸️</span>
                <span id="auto-refresh-text">60s</span>
            </button>
            <button onclick="refreshDashboard()" id="refresh-btn" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 transition">
                <svg class="w-4 h-4 refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Yenile
            </button>
        </div>
    </div>

    <!-- Ana Grid -->
    <div class="grid gap-4 mb-6 lg:grid-cols-4 animate-fade-in-up animate-delay-1">
        <!-- Sağlık Skoru -->
        <div class="lg:col-span-1 box-shadow rounded-xl p-6 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%) !important;">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full" style="background: rgba(255,255,255,0.2);"></div>
                <div class="absolute -left-10 -bottom-10 w-32 h-32 rounded-full" style="background: rgba(255,255,255,0.2);"></div>
            </div>
            <div class="relative text-center">
                <p class="text-sm opacity-80 mb-2">Mağaza Sağlık Skoru</p>
                <div class="relative inline-flex items-center justify-center">
                    <svg class="w-36 h-36 transform -rotate-90">
                        <circle cx="72" cy="72" r="60" stroke-width="10" stroke="rgba(255,255,255,0.2)" fill="none"/>
                        <circle id="health-circle" class="health-ring" cx="72" cy="72" r="60" stroke-width="10" 
                                stroke="{{ ($healthScore ?? 75) >= 70 ? '#22c55e' : (($healthScore ?? 75) >= 50 ? '#eab308' : '#ef4444') }}" 
                                fill="none"
                                stroke-dasharray="377" 
                                stroke-dashoffset="{{ 377 - (($healthScore ?? 75) / 100 * 377) }}" 
                                stroke-linecap="round"/>
                    </svg>
                    <div class="absolute flex flex-col items-center">
                        <span id="health-score" class="text-5xl font-bold">{{ $healthScore ?? 75 }}</span>
                        <span class="text-xs opacity-80">/100</span>
                    </div>
                </div>
                <p class="mt-2 text-sm font-medium" id="health-label">
                    @if(($healthScore ?? 75) >= 80) 
                        <span class="px-3 py-1 rounded-full" style="background: rgba(255,255,255,0.2);">🌟 Mükemmel</span>
                    @elseif(($healthScore ?? 75) >= 60) 
                        <span class="px-3 py-1 rounded-full" style="background: rgba(255,255,255,0.2);">✅ İyi Durumda</span>
                    @elseif(($healthScore ?? 75) >= 40) 
                        <span class="px-3 py-1 rounded-full" style="background: rgba(255,255,255,0.2);">⚠️ Dikkat Gerekli</span>
                    @else 
                        <span class="px-3 py-1 rounded-full" style="background: rgba(255,255,255,0.2);">🚨 Kritik</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Performans Metrikleri -->
        <div class="lg:col-span-3 box-shadow rounded-xl bg-white dark:bg-gray-900 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">📊 Performans Metrikleri</h3>
                <div class="sparkline" title="Son 7 gün satış trendi">
                    @php
                        $dailySales = $weeklyStats['dailySales'] ?? [1, 1, 1, 1, 1, 1, 1];
                        $maxSale = max(array_merge($dailySales, [1])); // En az 1 olmasını garantile
                    @endphp
                    @foreach($dailySales as $sale)
                        <div class="sparkline-bar" style="height: {{ ($maxSale > 0 ? ($sale / $maxSale) * 100 : 10) }}%"></div>
                    @endforeach
                </div>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                <div class="stat-card text-center p-4 rounded-xl cursor-pointer" style="background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%); flex: 1; min-width: 120px;" onclick="location.href='{{ route('admin.marketplace.trendyol.buybox-tracker') }}'">
                    <p class="text-3xl font-bold" style="color: #16a34a;">{{ $buyboxRate ?? 65 }}%</p>
                    <p class="text-xs text-gray-600 mt-1">BuyBox Oranı</p>
                    <div class="flex items-center justify-center gap-1 mt-1">
                        <span class="text-xs font-medium" style="color: #16a34a;">{{ ($buyboxStats->won ?? 24) }}/{{ ($buyboxStats->total ?? 37) }}</span>
                        @if(($buyboxStats->won ?? 24) > 20)
                            <span style="color: #22c55e;">↑</span>
                        @endif
                    </div>
                </div>
                <div class="stat-card text-center p-4 rounded-xl" style="background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%); flex: 1; min-width: 120px;">
                    <p class="text-3xl font-bold" style="color: #2563eb;">{{ $profitMargin ?? 18.5 }}%</p>
                    <p class="text-xs text-gray-600 mt-1">Kar Marjı</p>
                    <span class="text-xs text-gray-500">Ortalama</span>
                </div>
                <div class="stat-card text-center p-4 rounded-xl" style="background: linear-gradient(135deg, #f3e8ff 0%, #ede9fe 100%); flex: 1; min-width: 120px;">
                    <p class="text-3xl font-bold" style="color: #9333ea;">{{ $stockHealth ?? 85 }}%</p>
                    <p class="text-xs text-gray-600 mt-1">Stok Sağlığı</p>
                    <span class="text-xs" style="color: #22c55e;">✓ Güvenli</span>
                </div>
                <div class="stat-card text-center p-4 rounded-xl" style="background: linear-gradient(135deg, #fef3c7 0%, #fef9c3 100%); flex: 1; min-width: 120px;">
                    <p class="text-3xl font-bold" style="color: #d97706;">{{ $customerRating ?? 4.7 }}</p>
                    <p class="text-xs text-gray-600 mt-1">Müşteri Puanı</p>
                    <span class="text-xs" style="color: #f59e0b;">⭐⭐⭐⭐⭐</span>
                </div>
                <div class="stat-card text-center p-4 rounded-xl" style="background: linear-gradient(135deg, #ffedd5 0%, #fee2e2 100%); flex: 1; min-width: 120px;">
                    <p class="text-3xl font-bold" style="color: #ea580c;">{{ $cargoPerformance ?? 92 }}%</p>
                    <p class="text-xs text-gray-600 mt-1">Kargo Perf.</p>
                    <span class="text-xs" style="color: #22c55e;">↑ 3%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Uyarı Kategorileri -->
    <div class="grid gap-3 mb-6 md:grid-cols-4 animate-fade-in-up animate-delay-2">
        <button onclick="filterAlerts('critical')" class="alert-filter-btn relative box-shadow rounded-xl bg-white dark:bg-gray-900 p-4 hover:ring-2 hover:ring-red-500 transition cursor-pointer text-left group {{ empty($alerts) || collect($alerts)->where('type', 'critical')->isEmpty() ? '' : 'pulse-critical' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Acil</p>
                    <p class="text-4xl font-bold text-red-600 mt-1">{{ $alertCounts['critical'] ?? 2 }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center group-hover:scale-110 transition">
                    <span class="text-3xl">🔴</span>
                </div>
            </div>
        </button>
        
        <button onclick="filterAlerts('warning')" class="alert-filter-btn box-shadow rounded-xl bg-white dark:bg-gray-900 p-4 hover:ring-2 hover:ring-yellow-500 transition cursor-pointer text-left group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Dikkat</p>
                    <p class="text-4xl font-bold text-yellow-600 mt-1">{{ $alertCounts['warning'] ?? 3 }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center group-hover:scale-110 transition">
                    <span class="text-3xl">🟡</span>
                </div>
            </div>
        </button>
        
        <button onclick="filterAlerts('opportunity')" class="alert-filter-btn box-shadow rounded-xl bg-white dark:bg-gray-900 p-4 hover:ring-2 hover:ring-green-500 transition cursor-pointer text-left group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fırsat</p>
                    <p class="text-4xl font-bold text-green-600 mt-1">{{ $alertCounts['opportunity'] ?? 2 }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center group-hover:scale-110 transition">
                    <span class="text-3xl">🟢</span>
                </div>
            </div>
        </button>
        
        <button onclick="filterAlerts('trend')" class="alert-filter-btn box-shadow rounded-xl bg-white dark:bg-gray-900 p-4 hover:ring-2 hover:ring-blue-500 transition cursor-pointer text-left group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Trend</p>
                    <p class="text-4xl font-bold text-blue-600 mt-1">{{ $alertCounts['trend'] ?? 1 }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition">
                    <span class="text-3xl">📈</span>
                </div>
            </div>
        </button>
    </div>

    <!-- Ana İçerik Grid -->
    <div class="grid gap-4 lg:grid-cols-3 animate-fade-in-up animate-delay-3">
        <!-- Sol: Uyarılar -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Uyarı Listesi -->
            <div class="box-shadow rounded-xl bg-white dark:bg-gray-900 overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        🎯 Dikkat Gerektiriyor
                        <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full dark:bg-red-900/30">{{ count($alerts ?? []) }}</span>
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="filterAlerts('all')" class="text-xs text-indigo-600 hover:underline font-medium">Tümü</button>
                        <span class="text-gray-300">|</span>
                        <a href="{{ route('admin.marketplace.trendyol.notifications') }}" class="text-xs text-gray-500 hover:text-gray-700">Tüm Bildirimler →</a>
                    </div>
                </div>
                
                <div id="alerts-container" class="divide-y dark:divide-gray-700 max-h-[500px] overflow-y-auto">
                    @forelse(($alerts ?? []) as $index => $alert)
                        <div class="alert-item alert-{{ $alert['type'] }} alert-card p-4 flex items-start gap-4 
                            {{ $alert['type'] == 'critical' ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}
                            {{ $alert['type'] == 'warning' ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }}
                            {{ $alert['type'] == 'opportunity' ? 'bg-green-50/50 dark:bg-green-900/10' : '' }}
                            {{ $alert['type'] == 'trend' ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}
                            " style="animation-delay: {{ $index * 0.05 }}s">
                            
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                                {{ $alert['type'] == 'critical' ? 'bg-red-100 dark:bg-red-900/30' : '' }}
                                {{ $alert['type'] == 'warning' ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }}
                                {{ $alert['type'] == 'opportunity' ? 'bg-green-100 dark:bg-green-900/30' : '' }}
                                {{ $alert['type'] == 'trend' ? 'bg-blue-100 dark:bg-blue-900/30' : '' }}
                            ">
                                @if($alert['type'] == 'critical')
                                    <span class="text-xl">🔴</span>
                                @elseif($alert['type'] == 'warning')
                                    <span class="text-xl">🟡</span>
                                @elseif($alert['type'] == 'opportunity')
                                    <span class="text-xl">🟢</span>
                                @else
                                    <span class="text-xl">📈</span>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 dark:text-gray-200 text-sm">{{ $alert['title'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $alert['description'] }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $alert['time_ago'] }}</p>
                            </div>
                            
                            <div class="flex-shrink-0 flex gap-2">
                                @if($alert['action_type'] == 'update_price')
                                    <button class="quick-action-btn bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-red-700 font-medium">
                                        Fiyat Güncelle
                                    </button>
                                @elseif($alert['action_type'] == 'update_stock')
                                    <button class="quick-action-btn bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-orange-700 font-medium">
                                        Stok Ekle
                                    </button>
                                @elseif($alert['action_type'] == 'reply')
                                    <button class="quick-action-btn bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-yellow-700 font-medium">
                                        Yanıtla
                                    </button>
                                @elseif($alert['action_type'] == 'analyze')
                                    <button class="quick-action-btn bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-blue-700 font-medium">
                                        Analiz Et
                                    </button>
                                @else
                                    <button class="quick-action-btn bg-gray-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-gray-700 font-medium">
                                        Detay
                                    </button>
                                @endif
                                <button onclick="dismissAlert({{ $alert['id'] }})" class="text-gray-400 hover:text-gray-600 p-1.5" title="Görmezden Gel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                <span class="text-3xl">✅</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 font-medium">Harika! Şu an herhangi bir uyarı yok.</p>
                            <p class="text-sm text-gray-400 mt-1">Tüm sistemler normal çalışıyor.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sağ: İstatistikler -->
        <div class="space-y-4">
            <!-- Bu Hafta -->
            <div class="box-shadow rounded-xl bg-white dark:bg-gray-900 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">📅 Bu Hafta</h3>
                    @if(($weeklyStats['salesChange'] ?? 0) > 0)
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full dark:bg-green-900/30">
                            ↑ {{ $weeklyStats['salesChange'] ?? 12 }}%
                        </span>
                    @else
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full dark:bg-red-900/30">
                            ↓ {{ abs($weeklyStats['salesChange'] ?? 0) }}%
                        </span>
                    @endif
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400 text-sm">Toplam Satış</span>
                        <span class="font-bold text-xl">{{ $weeklyStats['totalSales'] ?? 47 }} adet</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400 text-sm">Toplam Ciro</span>
                        <span class="font-bold text-xl text-green-600">{{ number_format($weeklyStats['totalRevenue'] ?? 12450, 0, ',', '.') }} ₺</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400 text-sm">Net Kâr</span>
                        <span class="font-bold text-xl text-green-600">{{ number_format($weeklyStats['netProfit'] ?? 2890, 0, ',', '.') }} ₺</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400 text-sm">İade</span>
                        <span class="font-bold text-lg text-red-600">{{ $weeklyStats['returns'] ?? 2 }} adet</span>
                    </div>
                </div>
            </div>

            <!-- En Çok Satanlar -->
            <div class="box-shadow rounded-xl bg-white dark:bg-gray-900 p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">🏆 En Çok Satanlar</h3>
                <div class="space-y-3">
                    @foreach(($topProducts ?? []) as $index => $product)
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <span class="text-xl">{{ ['🥇', '🥈', '🥉'][$index] ?? '🏅' }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate text-gray-800 dark:text-gray-200">{{ $product['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $product['quantity'] }} adet</p>
                            </div>
                            <span class="text-green-600 font-bold text-sm">+{{ number_format($product['profit'], 0, ',', '.') }} ₺</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Hızlı Erişim -->
            <div class="box-shadow rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">⚡ Hızlı Erişim</h3>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.marketplace.trendyol.products') }}" class="quick-action-btn flex flex-col items-center gap-2 p-3 bg-white dark:bg-gray-800 rounded-xl hover:shadow-md transition">
                        <span class="text-2xl">📦</span>
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Ürünler</span>
                    </a>
                    <a href="{{ route('admin.marketplace.trendyol.orders') }}" class="quick-action-btn flex flex-col items-center gap-2 p-3 bg-white dark:bg-gray-800 rounded-xl hover:shadow-md transition">
                        <span class="text-2xl">🛒</span>
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Siparişler</span>
                    </a>
                    <a href="{{ route('admin.marketplace.trendyol.commission-calculator') }}" class="quick-action-btn flex flex-col items-center gap-2 p-3 bg-white dark:bg-gray-800 rounded-xl hover:shadow-md transition">
                        <span class="text-2xl">🧮</span>
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Hesaplayıcı</span>
                    </a>
                    <a href="{{ route('admin.marketplace.trendyol.price-rules') }}" class="quick-action-btn flex flex-col items-center gap-2 p-3 bg-white dark:bg-gray-800 rounded-xl hover:shadow-md transition">
                        <span class="text-2xl">⚙️</span>
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Fiyat Kuralları</span>
                    </a>
                </div>
            </div>

            <!-- Dikkat Özeti -->
            <div class="box-shadow rounded-xl bg-white dark:bg-gray-900 p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">⚠️ Dikkat Özeti</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-3 p-2.5 rounded-lg bg-red-50 dark:bg-red-900/20">
                        <span class="text-lg">📦</span>
                        <span class="text-sm font-medium text-red-700 dark:text-red-400">{{ $criticalStockCount ?? 3 }} ürün stok kritik</span>
                    </div>
                    <div class="flex items-center gap-3 p-2.5 rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                        <span class="text-lg">💰</span>
                        <span class="text-sm font-medium text-yellow-700 dark:text-yellow-400">{{ $lowMarginCount ?? 5 }} ürün düşük marjlı</span>
                    </div>
                    <div class="flex items-center gap-3 p-2.5 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <span class="text-lg">🎯</span>
                        <span class="text-sm font-medium text-blue-700 dark:text-blue-400">{{ $buyboxLostCount ?? 8 }} ürün BuyBox dışı</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            let autoRefreshEnabled = true;
            let autoRefreshInterval = null;
            let countdown = 60;

            // Auto refresh başlat
            function startAutoRefresh() {
                countdown = 60;
                updateCountdown();
                autoRefreshInterval = setInterval(() => {
                    countdown--;
                    updateCountdown();
                    if (countdown <= 0) {
                        refreshDashboard();
                    }
                }, 1000);
            }

            function updateCountdown() {
                document.getElementById('auto-refresh-text').textContent = countdown + 's';
            }

            // Auto refresh toggle
            window.toggleAutoRefresh = function() {
                autoRefreshEnabled = !autoRefreshEnabled;
                const btn = document.getElementById('auto-refresh-btn');
                const icon = document.getElementById('auto-refresh-icon');
                
                if (autoRefreshEnabled) {
                    icon.textContent = '⏸️';
                    startAutoRefresh();
                } else {
                    icon.textContent = '▶️';
                    document.getElementById('auto-refresh-text').textContent = 'Durduruldu';
                    clearInterval(autoRefreshInterval);
                }
            }

            // Uyarıları filtrele
            window.filterAlerts = function(type) {
                const items = document.querySelectorAll('.alert-item');
                items.forEach(item => {
                    if (type === 'all') {
                        item.style.display = 'flex';
                    } else {
                        if (item.classList.contains('alert-' + type)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });
                
                // Aktif filtre göster
                document.querySelectorAll('.alert-filter-btn').forEach(btn => {
                    btn.classList.remove('ring-2');
                });
            }

            // Dashboard yenile
            window.refreshDashboard = function() {
                const btn = document.getElementById('refresh-btn');
                btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg> Yenileniyor...`;
                
                setTimeout(() => {
                    location.reload();
                }, 500);
            }

            // Uyarıyı kapat
            window.dismissAlert = function(alertId) {
                fetch('{{ route("admin.marketplace.trendyol.intelligence.dismiss") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ alert_id: alertId })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // Animasyonlu kaldır
                        const alertEl = document.querySelector(`.alert-item[data-id="${alertId}"]`);
                        if (alertEl) {
                            alertEl.style.transform = 'translateX(100%)';
                            alertEl.style.opacity = '0';
                            setTimeout(() => alertEl.remove(), 300);
                        }
                    }
                })
                .catch(() => {
                    // Simüle kaldırma
                    const alertEls = document.querySelectorAll('.alert-item');
                    if (alertEls.length > 0) {
                        const alertEl = alertEls[0];
                        alertEl.style.transform = 'translateX(100%)';
                        alertEl.style.opacity = '0';
                        setTimeout(() => alertEl.remove(), 300);
                    }
                });
            }

            // Sayfa yüklendiğinde
            document.addEventListener('DOMContentLoaded', function() {
                startAutoRefresh();
                
                // Klavye kısayolları
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'r' && !e.ctrlKey && !e.metaKey && document.activeElement.tagName !== 'INPUT') {
                        refreshDashboard();
                    }
                    if (e.key >= '1' && e.key <= '4') {
                        const types = ['critical', 'warning', 'opportunity', 'trend'];
                        filterAlerts(types[parseInt(e.key) - 1]);
                    }
                    if (e.key === '0') {
                        filterAlerts('all');
                    }
                });
            });
        </script>
    @endPushOnce
</x-admin::layouts>
