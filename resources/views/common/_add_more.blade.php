 <script>
    var ecCache = {};
    $(function() {
        var $container = $("#details-container");
        var index = $container.find(".det-row").length + 1;
        var doAjax = true;

        $(".btn-add-more").click(function() {
            var $self = $(this);
            var url = $self.data('url');
            var loadingText = $self.data('loading-text');

            if (view = ecCache.data) {
                index++;
                add(view, index)
                doAjax = false;
            }

            if (doAjax) {

                if ($(this).html() !== loadingText) {
                    $self.data('original-text', $(this).html());
                    $self.prop('disabled', true).html(loadingText);
                }
                
                $.getJSON(url, function(data) {
                    $self.prop('disabled', false).html($self.data('original-text'));
                    ecCache['data'] = data.view;
                    add(data.view, index)
                });
            }
        });

        // removing
        $container.on("click", ".rem-row", function() {
            var $self = $(this);
            var id = $self.data('id');
            var delUrl = $self.data('del-url');
            var params = $self.data('params') || {};
            if (id && id !== ':id:') {

                swal({
                    title: "{{ __('Are you sure?') }}",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "{{ _('Yes, delete it') }}!",
                    cancelButtonText: "{{ _('Cancel') }}!",
                    closeOnConfirm: false
                },
                function(){
                    
                    var request = $.ajax({
                        method: 'post',
                        url: delUrl,
                        data: $.extend({
                            _token	: $("input[name='_token']").val(),
                            '_method': 'DELETE',

                        }, JSON.parse(JSON.stringify(params)) )
                    });

                    request.done(function(data){
                        if (data.error == false) {
                            $self.closest('.det-row').remove();
                            swal(data.msg, "", "success");
                        } else {
                            swal(data.msg, "", "error");
                        }
                    })
                    .fail(function( jqXHR, textStatus ) {
                        swal(textStatus, "", "error");
                    });
                    
                });

            } else {
                $self.closest('.det-row').remove();
            }
        });

        function add(view, index) {
            view = view.replace(/:index:/g, index);
            $container.append(view);
        }
    });
</script>