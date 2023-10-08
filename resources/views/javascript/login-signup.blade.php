<script>
    const signUp = () => {
        let spinner = '<div  class="d-flex justify-content-center text-white"><div class="spinner-border myspinner"role="status"><span class="sr-only">Loading...</span></div></div>';
        let name = $('#name').val();
        let email = $('#email').val();
        let password = $('#password').val();
        let phone = $('#phone').val();
        let company_name = $('#company_name').val();
        let company_phone = $('#company_phone').val();
        let location_text = $('#location_text').val();
        let Address = [];
        let lat = $('input[id="Address[lat]"]').val();
        let lon = $('input[id="Address[lon]"]').val();
        let select_values = $('#select_values').val();
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
                location_text: location_text,
                lat: lat,
                lon: lon,
                select_values: select_values,
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
                        // console.log(response.errors.name[0]);
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
                        if (response.errors.select_values) {
                            $('.select_values').html(response.errors.select_values[0]);
                        }
                    }

                }
            }
        });
    }
</script>
