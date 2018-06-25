@extends('layouts.tenant')

@section('title')
  {{ __('Reset Password') }} | {{ config('app.name', '') }}
@endsection

@section('content')    

      <form method="post" action="{{ route('tenant.user.password.email', $tenant->domain)  }}">
        <div class="signin-wrapper">

         <div class="signin-box" style="width: 500px">

          @include('tenant.common._notifications')
            
            <h2 class="slim-logo"><a href="#!">{{ config('app.name', '') }}</a></h2>
            <h2 class="signin-title-primary">{{ __('Reset Password') }}!</h2>

            <div class="form-group">
              <input class="form-control" type="email" name="email" placeholder="{{ __('Email') }}" autofocus="" required="">
            </div><!-- form-group -->

            <button class="btn btn-primary btn-block btn-signin">{{ __('Send Link') }}</button>


          </div><!-- signin-box -->

          {{ csrf_field() }}
        </div><!-- signin-wrapper -->
      </form>  

@endsection
