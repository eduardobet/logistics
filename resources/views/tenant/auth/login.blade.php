@extends('layouts.tenant')

@section('content')

      <form method="post" action="{{ route('tenant.auth.post.login')  }}">
    <div class="signin-wrapper">

          
        
      <div class="signin-box">
        <h2 class="slim-logo"><a href="#!">Seal<span>.</span>Log</a></h2>
        <h2 class="signin-title-primary">Bienvenido!</h2>

        <div class="form-group">
          <input type="email" class="form-control" placeholder="Nombre de Usuario" name="email" required="">
        </div><!-- form-group -->

        <div class="form-group mg-b-50">
          <input type="password" class="form-control" placeholder="Contraseña" name="password" required="">
        </div><!-- form-group -->

        <button class="btn btn-primary btn-block btn-signin">Entrar</button>
      </div><!-- signin-box -->

      {{ csrf_field() }}
    </div><!-- signin-wrapper -->
      </form>  

@endsection
