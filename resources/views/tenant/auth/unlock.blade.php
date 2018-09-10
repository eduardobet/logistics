@extends('layouts.tenant')

@section('title')
  {{ __('Account activation') }}  {{ config('app.name', '') }}
@endsection

@section('content')    

      <form method="post" action="{{ route('tenant.employee.post.unlock', $tenant->domain)  }}">
        <div class="signin-wrapper">

         <div class="signin-box" style="width: 500px">

          @include('tenant.common._notifications')
            
            <h2 class="slim-logo"><a href="#!">{{ config('app.name', '') }}</a></h2>
            <h2 class="signin-title-primary">{{ __('Create your password') }}!</h2>

            <div class="form-group">
              <input type="email" class="form-control" placeholder="{{ __('Email') }}" name="email" required="" value="{{ old('email', request('email')) }}" readonly>
            </div><!-- form-group -->

            <div class="form-group mg-b-50">
              <input type="password" class="form-control" placeholder="{{ __('Password') }}" name="password" required="">
            </div><!-- form-group -->

            <button type="submit" class="btn btn-primary btn-block btn-signin">{{ __('Send') }}</button>

          </div><!-- signin-box -->
          <input type="hidden" name="token" value="{{ $employee->token }}">
          {{ csrf_field() }}
        </div><!-- signin-wrapper -->
      </form>  

@endsection
