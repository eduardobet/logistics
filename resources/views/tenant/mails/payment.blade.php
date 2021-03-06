@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ __('Payment', [], $lang) }} #{{ $payment->id }}
        @endcomponent
@endslot

#{{ $ibranch->name }}<br>

{{ __('TAX ID', [], $lang) }} {{ $ibranch->ruc }} {{ __('DV', [], $lang) }} {{ $ibranch->dv }} <br>
{{ $ibranch->address }} <br>
{{ $ibranch->telephones }} <br><br>

<strong>{{ __('Invoice', [], $lang) }} No.</strong>: {{ $ibranch->initial }}-{{ $invoice->manual_id_dsp }} <br>
<strong>{{ __('Payment', [], $lang) }} No.</strong>: {{ $payment->id }}<br>
<strong>{{ __('Payment date', [], $lang) }}</strong>: {{ $payment->created_at->format('d-m-Y') }}<br>
<strong>{{ __('Client', [], $lang) }}</strong>: {{ $client->full_name }} / {{ $client->pid }} / {{ $client->telephones }} <br><br>

<strong>{{ __('Amount', [], $lang) }}</strong>: ${{ number_format($payment->amount_paid, 2) }}<br>
<strong>{{ __('Payment method', [], $lang) }}</strong>: {{ [1 => __('Cash', [], $lang), 2 => __('Wire transfer', [], $lang), 3 => __('Check', [], $lang), ][$payment->payment_method] }}<br>
<strong>{{ __('Concept', [], $lang) }}</strong>: {{ $payment->payment_ref }} <br>


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