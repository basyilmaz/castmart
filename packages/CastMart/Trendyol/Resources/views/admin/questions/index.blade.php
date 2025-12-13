<x-admin::layouts>
    <x-slot:title>
        Müşteri Soruları
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <span class="text-2xl">❓</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Müşteri Soruları</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Trendyol müşteri sorularını cevaplayın</p>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 p-4 text-green-700 dark:bg-green-900 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded bg-red-100 p-4 text-red-700 dark:bg-red-900 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <!-- Questions List -->
    <div class="space-y-4">
        @forelse($questions as $question)
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="mb-2 flex items-start justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $question->asked_at?->format('d.m.Y H:i') ?? '-' }}
                </span>
                @if($question->status === 'pending')
                    <span class="rounded bg-yellow-100 px-2 py-1 text-xs text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">
                        Bekliyor
                    </span>
                @else
                    <span class="rounded bg-green-100 px-2 py-1 text-xs text-green-700 dark:bg-green-900 dark:text-green-300">
                        Cevaplandı
                    </span>
                @endif
            </div>
            
            <p class="mb-3 font-medium text-gray-800 dark:text-white">{{ $question->question_text }}</p>
            
            @if($question->status === 'answered')
                <div class="rounded bg-gray-100 p-3 dark:bg-gray-800">
                    <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Cevabınız:</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $question->answer_text }}</p>
                </div>
            @else
                <form action="{{ route('admin.marketplace.trendyol.questions.answer', $question) }}" method="POST">
                    @csrf
                    <x-admin::form.control-group class="mb-2">
                        <x-admin::form.control-group.control
                            type="textarea"
                            name="answer"
                            rows="2"
                            rules="required|min:10"
                            :label="'Cevap'"
                            placeholder="Cevabınızı yazın..."
                        />
                    </x-admin::form.control-group>
                    <button type="submit" class="primary-button text-sm">
                        Cevapla
                    </button>
                </form>
            @endif
        </div>
        @empty
        <div class="box-shadow rounded bg-white p-8 text-center dark:bg-gray-900">
            <p class="text-gray-500 dark:text-gray-400">Henüz müşteri sorusu yok.</p>
        </div>
        @endforelse
    </div>

    @if($questions->hasPages())
    <div class="mt-4">
        {{ $questions->links() }}
    </div>
    @endif
</x-admin::layouts>
