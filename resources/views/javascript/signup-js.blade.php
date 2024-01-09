<script>
    const signUp = () => {
        let spinner = '<div class="d-flex justify-content-center text-white"><div class="spinner-border myspinner"role="status"><span class="sr-only">Loading...</span></div></div>';
        let name = $('#name').val();
        let email = $('#email').val();
        let password = $('#password').val();
        let phone = $('#phone').val();
        let business_name = $('#business_name').val();
        let business_phone = $('#business_phone').val();
        let address = $('#address').val();
        let unit_address = $('#unit_address').val();
        let postcode = $('#postcode').val();
        let country = $('#country').val();
        let state = $('#state').val();
        let city = $('#city').val();
        let lat = $('input[id="address[lat]"]').val();
        let lon = $('input[id="address[lon]"]').val();
        let parent_store = $('#parent_store').val();
        let checked_value = 0;
        if ($('#chkSelect').is(':checked')) {
            checked_value = 1;
        }
        $('#signup').html(spinner);
        $.ajax({
            url: "{{ route('register') }}",
            type: "post",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                email: email,
                password: password,
                phone: phone,
                business_name: business_name,
                business_phone: business_phone,
                address: address,
                unit_address: unit_address,
                postcode: postcode,
                country: country,
                state: state,
                city: city,
                lat: lat,
                lon: lon,
                parent_store: parent_store,
                checked_value: checked_value
            },
            success: function(response) {
                $('#signup').text('Sign up');
                if (response == "User Created") {
                    Swal.fire({
                        title: 'Success!',
                        text: 'We have received your store details we will contact you soon to verify your store',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    $('.error').html('');
                    if (response.errors.name) {
                        $('.name').html('');
                        $('.name').html(response.errors.name[0]);
                    }
                    if (response.errors.email) {
                        $('.email').html(response.errors.email[0]);
                    }
                    if (response.errors.password) {
                        $('.password').html(response.errors.password[0]);
                    }
                    if (response.errors.phone) {
                        $('.phone').html(response.errors.phone[0]);
                    }
                    if (response.errors.business_name) {
                        $('.business_name').html(response.errors.business_name[0]);
                    }
                    if (response.errors.business_phone) {
                        $('.business_phone').html(response.errors.business_phone[0]);
                    }
                    if (response.errors.address) {
                        $('.location').html(response.errors.address[0]);
                    }
                    if (response.errors.postcode) {
                        $('.location').html(response.errors.postcode[0]);
                    }
                    if (response.errors.country) {
                        $('.location').html(response.errors.country[0]);
                    }
                    if (response.errors.state) {
                        $('.location').html(response.errors.state[0]);
                    }
                    if (response.errors.city) {
                        $('.location').html(response.errors.city[0]);
                    }
                    if ($('#chkSelect').is(":checked")) {
                        if (response.errors.parent_store) {
                            $('.parent_store').html(response.errors.parent_store[0]);
                        }
                    }
                }
            }
        });
    }
</script>
