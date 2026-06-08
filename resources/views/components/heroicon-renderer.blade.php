@props(['icon' => '', 'class' => ''])

@if($icon)
    @try
        <x-dynamic-component :component="$icon" :class="$class" />
    @catch(\Exception $e)
        <span class="{{ $class }}">📦</span>
    @endcatch
@else
    <span class="{{ $class }}">📦</span>
@endif
