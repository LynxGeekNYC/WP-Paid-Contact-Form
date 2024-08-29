jQuery(document).ready(function($) {
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '5.00' // Amount to be paid
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                $('#payment_status').val('paid');
                $('#submit-button').removeAttr('disabled');
            });
        }
    }).render('#paypal-button-container');

    $('#paid-contact-form').on('submit', function(e) {
        if ($('#payment_status').val() !== 'paid') {
            e.preventDefault();
            alert('Please complete the payment before submitting the form.');
        }
    });
});
