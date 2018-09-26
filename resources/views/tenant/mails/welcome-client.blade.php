@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ __('Welcome', [], $lang) }} {{ $client->full_name }}
        @endcomponent
@endslot

{{ __('Hello :who welcome to :what', ['who' => $client->full_name, 'what' => $tenant->name ] , [], $lang) }}. {{ __('Below, your box information', [], $lang) }}: <br>
{{ __('Box number', [], $lang) }}: <b>{{ $box_code }}{{ $client->id }}</b> <br><br>

{{ __('This is the address you should use when making your purchases:', [], $lang) }} <br>

<h3>{{ __('For Aerial Shipments', [], $lang) }}:</h3>
{{ $client->first_name }} {{ $box_code }}{{ $client->id }} {{ $client->last_name }} <br>
{{ $air->address }} <br>
{{ $air->telephones }} <br> <br>

<h3>{{ __('For Maritime Shipments', [], $lang) }}:</h3>
{{ $client->first_name }} {{ $box_code }}{{ $client->id }} {{ $client->last_name }} <br>
{{ $maritime->address }} <br>
{{ $maritime->telephones }} <br><br><br>

{{ __('Remember your purchases must always contain your box', [], $lang) }}: <b>{{ $box_code }}{{ $client->id }}</b>

@isset($subcopy)
    @slot('subcopy')
        @component('mail::subcopy')
            {!! $subcopy !!}
        @endcomponent
    @endslot
@endisset

@slot('footer')
    @component('mail::footer')
        &copy; {{ date('Y') }} {{ $tenant->name }}
    @endcomponent
@endslot

@endcomponent