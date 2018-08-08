<script>
    $(function() {
        $(".toggle-text").change(function(e) {
            var $self = $(this);
            var $target = $($self.data('target'));
            var whenValue = $self.data('toogle-when');
            var required = $self.data('required') === 'Y';
            var tmpValue = $self.data('tmp-value');

            if ($self.val() === whenValue) {
                if (required) $target.attr('required', 'required');
                if (tmpValue) $target.val(tmpValue);
                $target.prop('readOnly', false);
            } else {
                $target.removeAttr('required');
                $target.prop('readOnly', true).val('');
            }
        });
    })
</script>