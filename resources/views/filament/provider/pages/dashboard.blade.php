@php
    $widgets = $this->getWidgets();
@endphp

<div class="fi-section-content">
    <div class="grid gap-6">
        @forelse($widgets as $widget)
            <livewire:{{ $widget }} :key="$widget" />
        @empty
            <div class="text-center py-12">
                <p class="text-gray-500">لا توجد بيانات</p>
            </div>
        @endforelse
    </div>
</div>
