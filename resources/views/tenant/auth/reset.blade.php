@extends('layouts.tenant')

@section('content')
  <form method="post" action="{{ route('tenant.user.password.post.reset', $tenant->domain)  }}">
      {{ csrf_field() }}
      <div class="signin-wrapper">

          <div class="signin-box" style="width: 500px">

              @include('tenant.common._notifications')

              <input type="hidden" name="token" value="{{  request('token') }}">

              <h2 class="slim-logo"><a href="#!">{{ config('app.name', '') }}</a></h2>
              <h2 class="signin-title-primary">{{ __('Reset Password') }}!</h2>

              <div class="form-group">
                  <input class="form-control" type="email" name="email" placeholder="{{ __('Email') }}" required="" value="{{ request('e') }}" {{request('e') ? ' readonly' : null }}>
              </div>

              <div class="form-group">
                  <input class="form-control" type="password" name="password" placeholder="{{ __('Password') }}" required="" autocomplete="off" autofocus="">
              </div>

              <div class="form-group">
                  <input class="form-control" type="password" name="password_confirmation" placeholder="{{ __('Password confirmation') }}" required="" autocomplete="off">
              </div>

              <button type="submit" class="btn btn-primary btn-block btn-signin">
                  {{ __('Send') }}
              </button>

              <a href="{{ route('tenant.auth.get.login', $tenant->domain) }}" class="button is-tomato">{{__('Login')}}</a>

          </div>


        </div>
    </form>
  @endsection

  @section('title')
  {{__('Reset Password')}} | {{ $tenant->name }} @ {{ config('app.name', '') }}
  @endsection