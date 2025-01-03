<?php 
require_once('db-connect.php');
?>
<div class="col-md-12">
    <div class="card rounded-0 shadow">
        <div class="card-header bg-primary text-light">
            <h3 class="card-title mb-0">Schedule Your Appointment</h3>
        </div>
        <div class="card-body">
            <form action="save_schedule.php" method="post" id="schedule-form">
                <input type="hidden" name="id" value="">
                <div class="row mb-3">
                    <div class="col">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control rounded-0" name="full_name" id="full_name" required>
                    </div>
                    <div class="col">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control rounded-0" name="phone_number" id="phone_number" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control rounded-0" name="email" id="email" required>
                    </div>
                    <div class="col">
                        <label for="type_of_vehicle" class="form-label">Type of Vehicle</label>
                        <select class="form-select rounded-0" name="type_of_vehicle" id="type_of_vehicle" required>
                            <option value="">Select Type</option>
                            <option value="Car">Car</option>
                            <option value="Motorcycle">Motorcycle</option>
                            <option value="Truck">Truck</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="start_datetime" class="form-label">Date and Time</label>
                        <input type="datetime-local" class="form-control rounded-0" name="start_datetime" id="start_datetime" required>
                    </div>
                    <!--<div class="col">
                        <label for="end_datetime" class="form-label">End</label>
                        <input type="datetime-local" class="form-control rounded-0" name="end_datetime" id="end_datetime" required>
                    </div>-->
                </div>
                <div class="text-center">
                    <button class="btn btn-primary rounded-0" type="submit" form="schedule-form"><i class="fa fa-save"></i> Save</button>
                    <button class="btn btn-secondary border rounded-0" type="reset" form="schedule-form"><i class="fa fa-reset"></i> Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php 
$schedules = $conn->query("SELECT * FROM `schedule_list`");
$sched_res = [];
foreach($schedules->fetch_all(MYSQLI_ASSOC) as $row){
    $row['sdate'] = date("F d, Y h:i A",strtotime($row['start_datetime']));
    $row['edate'] = date("F d, Y h:i A",strtotime($row['end_datetime']));
}
?>
<?php 
if(isset($conn)) $conn->close();
?>


</body>
<script>
    var scheds = <?= json_encode($sched_res) ?>;
</script>
<script src="./js/script.js"></script>
