<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Appointment Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container d-flex justify-content-center mt-5">
    <div class="col-md-6">
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
                        <a class="btn btn-secondary rounded-0" href="index.php"> Cancel</a>
                    </div>
                </form>
            </div>
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

<script>
    var scheds = <?= json_encode($sched_res) ?>;
</script>
<script src="./assets/js/script.js"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
