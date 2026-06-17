@props(['paginator'])

@if($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2.5 mt-5" aria-label="التنقل بين الصفحات">
        @if($paginator->onFirstPage())
            <span class="flex-none inline-flex items-center justify-center min-h-[40px] px-4 border border-slate-100 dark:border-slate-900 rounded-2xl bg-slate-100 dark:bg-slate-950 text-slate-400 dark:text-slate-600 text-[11px] font-bold cursor-not-allowed">السابق</span>
        @else
            <a href="{{ $paginator->appends(request()->query())->previousPageUrl() }}" class="flex-none inline-flex items-center justify-center min-h-[40px] px-4 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-[11px] font-black text-decoration-none hover:border-primary/20 hover:text-primary transition-all">السابق</a>
        @endif

        <strong class="text-slate-500 dark:text-slate-400 text-xs font-black px-1">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</strong>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->appends(request()->query())->nextPageUrl() }}" class="flex-none inline-flex items-center justify-center min-h-[40px] px-4 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-[11px] font-black text-decoration-none hover:border-primary/20 hover:text-primary transition-all">التالي</a>
        @else
            <span class="flex-none inline-flex items-center justify-center min-h-[40px] px-4 border border-slate-100 dark:border-slate-900 rounded-2xl bg-slate-100 dark:bg-slate-950 text-slate-400 dark:text-slate-600 text-[11px] font-bold cursor-not-allowed">التالي</span>
        @endif
    </nav>
@endif
