<script>
     const closed = (day) => {
            let listOfClasses = document.getElementById("time[" + day + "][open]").className;
            if (listOfClasses.search("disabled-input-field") < 0) {
                // To disable the input fields
                document.getElementById("time[" + day + "][open]").value = null;
                document.getElementById("time[" + day + "][close]").value = null;
                // To disable the input fields
                document.getElementById("time[" + day + "][open]").classList.add('disabled-input-field');
                document.getElementById("time[" + day + "][close]").classList.add('disabled-input-field');
                // To remove the required attribute from the input fields
                document.getElementById("time[" + day + "][open]").required = false;
                document.getElementById("time[" + day + "][close]").required = false;
            } else {
                // To enable the input fields
                document.getElementById("time[" + day + "][open]").classList.remove('disabled-input-field');
                document.getElementById("time[" + day + "][close]").classList.remove('disabled-input-field');
                // To add the required attribute from the input fields
                document.getElementById("time[" + day + "][open]").required = true;
                document.getElementById("time[" + day + "][close]").required = true;
            }
        }
</script>

<script !src="">
    $('.stimepicker').timepicker({
        timeFormat: 'h:mm p',
        interval: 30,
        startTime: '10:00',
        dynamic: true,
        dropdown: true,
        scrollbar: true
    });

    $('.etimepicker').timepicker({
        timeFormat: 'h:mm p',
        interval: 30,
        startTime: '10:00',
        dynamic: true,
        dropdown: true,
        scrollbar: true
    });
</script>