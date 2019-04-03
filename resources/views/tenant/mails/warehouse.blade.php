@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ __('Warehouse', [], $lang) }} #{{ $ibranch->initial }}-{{ $warehouse->manual_id_dsp }}
        @endcomponent
@endslot

{{ __('Hello', [], $lang) }} {{ $box }} / {{ $client->full_name }} <br>
{{ __('Your packages have been received. You may pick them up in 24 hours.', [], $lang) }} <br> <br>
{{ __('Details', [], $lang) }}:<br>

{{ __("Total packages", [], $lang) }}: {{ $warehouse->tot_packages }}<br>
{{ __("Total weight", [], $lang) }}: {{ $warehouse->tot_weight }}<br>
{{ __("Trackings", [], $lang) }}: {!! $warehouse->trackings !!}

@isset($subcopy)
    @slot('subcopy')
        @component('mail::subcopy')
            {!! $subcopy !!}
        @endcomponent
    @endslot
@endisset

@slot('footer')
    @component('mail::footer')
        &copy; {{ date('Y') }} {{ config('app.name') }}
    @endcomponent
@endslot

@endcomponent