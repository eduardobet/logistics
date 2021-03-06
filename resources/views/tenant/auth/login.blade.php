@extends('layouts.tenant')

@section('title')
  {{ __('Connection') }}  {{ config('app.name', '') }}
@endsection

@section('content')    

      <form method="post" action="{{ route('tenant.auth.post.login', $tenant->domain)  }}">
        <div class="signin-wrapper">

         <div class="signin-box" style="width: 500px">

          @include('tenant.common._notifications')
            
            <h2 class="slim-logo"><a href="#!">{{ config('app.name', '') }}</a></h2>
            <h2 class="signin-title-primary">{{ __('Welcome') }}!</h2>

            <div class="form-group">
              <input type="email" class="form-control" placeholder="{{ __('Email') }}" name="email" id="email" required="" value="{{ old('email') }}">
            </div><!-- form-group -->

            <div class="form-group mg-b-50">
              <input type="password" class="form-control" placeholder="{{ __('Password') }}" name="password" required="">
            </div><!-- form-group -->

            <button class="btn btn-primary btn-block btn-signin">{{ __('Login') }}</button>


            <a href="{{ route('tenant.user.password.request', $tenant->domain) }}" class="button is-tomato">{{__('Forgot password')}}</a>

          </div><!-- signin-box -->

          {{ csrf_field() }}
        </div><!-- signin-wrapper -->
      </form>  

@endsection


@section('xtra_scripts')
  <script>
    $(function() {
      $("#email").blur(function(e) {
        if (email = $.trim(this.value)) {
          var at = '@';
          if (!email.includes('@')) this.value = `${email}${at}{{ $tenant->domain }}`
        }
      });
    })
  </script>
@stop
