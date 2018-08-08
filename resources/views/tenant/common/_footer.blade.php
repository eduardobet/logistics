<div class="slim-footer">
    <div class="container">
        &copy; {{ __('Copyright :year | :company', [
            'year' => date('Y'),
            'company' => config('app.name')
        ])  }}
    </div><!-- container -->
</div>