// Form Reset Listener
    $('#schedule-form').on('reset', function () {
        $(this).find('input:hidden').val('');
        $(this).find('input:visible').first().focus();
    });

    // Handle Edit Button Click
    $('#edit').click(function () {
        var id = $(this).attr('data-id');
        if (!!scheds[id]) {
            populateFormForEdit(id);
        } else {
            alert("Event is undefined");
        }
    });

    // Populate Form for Editing
    function populateFormForEdit(id) {
        var _form = $('#schedule-form');
        _form.find('[name="id"]').val(id);
        _form.find('[name="full_name"]').val(scheds[id].full_name);
        _form.find('[name="phone_number"]').val(scheds[id].phone_number);
        _form.find('[name="email"]').val(scheds[id].email);
        _form.find('[name="vehicle_type"]').val(scheds[id].vehicle_type);
        _form.find('[name="start_datetime"]').val(String(scheds[id].start_datetime).replace(" ", "T"));

        $('#event-details-modal').modal('hide');
        _form.find('[name="full_name"]').focus();
    }

    // Handle Delete Button Click
    $('#delete').click(function () {
        var id = $(this).attr('data-id');
        if (!!scheds[id]) {
            if (confirm("Are you sure you want to delete this scheduled event?")) {
                deleteEvent(id);
            }
        } else {
            alert("Event is undefined");
        }
    });

    // Delete Event Function
    function deleteEvent(id) {
        location.href = "./delete_schedule.php?id=" + id;
    }