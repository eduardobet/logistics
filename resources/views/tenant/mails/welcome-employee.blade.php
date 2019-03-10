@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ __('Welcome', [], $lang) }} {{ $employee->full_name }}
        @endcomponent
@endslot

{{ __('Hello :who welcome to :what', ['who' => $employee->full_name, 'what' => $ubranch->name ], $lang ) }}. <br><br>{{ __('Please click the following link to activate your account.', [], $lang) }}: <a href="{{ route('tenant.employee.get.unlock', [$tenant->domain, $employee->email, $employee->token]) }}">{{ route('tenant.employee.get.unlock', [$tenant->domain, $employee->email, $employee->token]) }}</a><br><br>

<h3>{{ __('Some other interesting links', [], $lang) }}:</h3>
* {{ route('tenant.home', $tenant->domain) }}
* {{ route("tenant." . (($employee->isSuperAdmin()||$employee->isAdmin()) ? "admin": "employee") . ".dashboard", $tenant->domain ) }}


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