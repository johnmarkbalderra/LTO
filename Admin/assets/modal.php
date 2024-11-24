<!-- Appointment Form Modal -->
    <!-- <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-0 shadow">
                <div class="modal-header bg-gradient bg-primary text-light">
                    <h5 class="modal-title" id="appointmentModalLabel">Appointment Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <form action="save_schedule.php" method="post" id="schedule-form">
                            <input type="hidden" name="id" value="">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control rounded-0" name="full_name" id="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" class="form-control rounded-0" name="phone_number" id="phone_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control rounded-0" name="email" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="type_of_vehicle" class="form-label">Type of Vehicle</label>
                                <select class="form-select rounded-0" name="type_of_vehicle" id="type_of_vehicle" required>
                                    <option value="">Select Type</option>
                                    <option value="Motorcycle">Motorcycle w/out Side Car</option>
                                    <option value="Motorcycle">Motorcycle w/ Side Car</option>
                                    <option value="Utility Vehicle(UV)">Utility Vehicle(UV)</option>
                                    <option value="Sports Utility Vehicle(SUV)">Sports Utility Vehicle(SUV)</option>
                                    <option value="Car">Car</option>
                                    <option value="Truck">Truck</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="start_datetime" class="form-label">Date and Time</label>
                                <input type="datetime-local" class="form-control rounded-0" name="start_datetime" id="start_datetime" required>
                            </div>
                            <div class="text-center">
                                <button class="btn btn-primary rounded-0" type="submit"><i class="fa fa-save"></i> Save</button>
                                <button class="btn btn-secondary border rounded-0" type="reset"><i class="fa fa-reset"></i> Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Modal for moving appointment to Waitlist -->
<div class="modal fade" id="waitlistModal" tabindex="-1" aria-labelledby="waitlistModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="waitlistModalLabel">Move Appointment to Waitlist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="waitlistForm">
          <input type="hidden" id="schedule_id" name="schedule_id">
          <p>Are you sure you want to move this appointment to the waitlist?</p>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmWaitlistBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>


<!-- Add Appointment Button
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#appointmentModal">
                        <i class="fas fa-plus"></i> Add Appointment
                    </button> -->