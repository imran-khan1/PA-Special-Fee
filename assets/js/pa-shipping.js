jQuery(function($) {
    function togglePAField() {
        let show = false;
        const billingState = $('#billing_state').val();
        const shippingState = $('#shipping_state').val();
        const shippingCountry = $('#shipping_country').val();
        const billingCountry = $('#billing_country').val();
        const shipToDifferent = $('#ship-to-different-address-checkbox').prop('checked');

        if (!shipToDifferent && billingState === 'PA' && billingCountry === 'US') {
            show = true;
        } else if (shipToDifferent && shippingState === 'PA' && shippingCountry === 'US') {
            show = true;
        }

        $('#pa_shipping_notice').toggle(show);
    }

    togglePAField();

    $('#billing_state, #billing_country, #shipping_state, #shipping_country, #ship-to-different-address-checkbox')
        .on('change', togglePAField);
});
