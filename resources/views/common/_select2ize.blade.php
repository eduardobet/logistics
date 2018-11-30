<script>
    var cache = {};
    $(function() {
        $(".select2ize").each(function() {
            var $self = $(this);
            var $child = $($self.data('child'));
            $child.select2({width: 'resolve', allowClear: true,});
        });

        $(".select2ize").change(function() {
            var $self = $(this);
            var value = $self.val();
            var apiurl = $self.data('apiurl');
            var $child = $($self.data('child'));
            var childId = $child.attr('id');
            var $loader = $("#loader-"+childId);

            if (!apiurl) {
                console.error("Api url is not defined");
                return;
            }

            if (value && value != "0") {
                
                if ( items = cache[childId + '.' + value ] ) {
                    select2ize($child, items);
                    return;
                }

                $loader.html('<i class="fa fa-spinner fa-spin"></i>');
                $child.prop("disabled", true).select2({allowClear: true});
                apiurl = apiurl.replace(":parentId:", value)

                $.getJSON(apiurl, function(items) {
                    $loader.empty();
                    select2ize($child, items);
                    cache[childId + '.' + value] = items;
                });
            } else {
                select2ize($child, []);
            }
        });
    });

    function select2ize($child, items) {
        var newOptions = '<option value="0">---</option>';
        for(var key in items) {
            newOptions += '<option value="'+ key +'">'+ items[key] +'</option>';
        }
        
        $child.select2('destroy').html(newOptions).prop("disabled", false)
        .select2({width: 'resolve', allowClear: true});
    }
</script>