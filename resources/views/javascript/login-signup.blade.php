<script>
    const signUp = () => {
        let spinner = '<div class="d-flex justify-content-center text-white"><div class="spinner-border myspinner"role="status"><span class="sr-only">Loading...</span></div></div>';
        let name = $('#name').val();
        let email = $('#email').val();
        let password = $('#password').val();
        let phone = $('#phone').val();
        let company_name = $('#company_name').val();
        let company_phone = $('#company_phone').val();
        let user_address = $('#user_address').val();
        let user_country = $('#user_country').val();
        let user_state = $('#user_state').val();
        let user_city = $('#user_city').val();
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
                company_name: company_name,
                company_phone: company_phone,
                user_address: user_address,
                user_country: user_country,
                user_state: user_state,
                user_city: user_city,
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
                    if (response.errors.company_name) {
                        $('.company_name').html(response.errors.company_name[0]);
                    }
                    if (response.errors.company_phone) {
                        $('.company_phone').html(response.errors.company_phone[0]);
                    }
                    if (response.errors.location_text) {
                        $('.location').html(response.errors.location_text[0]);
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
