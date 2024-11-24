<?php
require_once 'db-connect.php';

// Function to fetch options from the database
function fetchOptions($conn, $table, $orderByColumn) {
    $sql = "SELECT * FROM $table ORDER BY $orderByColumn ASC";
    $result = $conn->query($sql);
    $options = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
    }
    return $options;
}

// Fetch options from the database for different vehicle variables
$bodyTypes = fetchOptions($conn, 'body_types', 'type_name');
$vehicleBrands = fetchOptions($conn, 'vehicle_brands', 'brand_name');
$vehicleColors = fetchOptions($conn, 'vehicle_colors', 'color_name');
$vehicleFuels = fetchOptions($conn, 'vehicle_fuels', 'fuel_name');

?>

<!-- Body Types Tab -->
<div class="tab-pane fade show active" id="bodyType" role="tabpanel" aria-labelledby="bodyTypeTab">
    <table class="table table-bordered" id="bodyTypeTable">
        <thead>
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addOptionModal" data-table="body_types" data-column="type_name">Add Body Type</button>
            <tr>
                <th>ID</th>
                <th>Body Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bodyTypes as $bodyType): ?>
            <tr>
                <td><?= htmlspecialchars($bodyType['id']); ?></td>
                <td><?= htmlspecialchars($bodyType['type_name']); ?></td>
                <td>
                    <button class='btn btn-warning btn-sm edit-btn' data-id='<?= $bodyType['id']; ?>' data-name='<?= $bodyType['type_name']; ?>' data-table='body_types' data-column='type_name'>Edit</button>
                    <button class='btn btn-danger btn-sm delete-btn' data-id='<?= $bodyType['id']; ?>' data-table='body_types' data-column='type_name'>Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Vehicle Brands Tab -->
<div class="tab-pane fade" id="vehicleBrand" role="tabpanel" aria-labelledby="vehicleBrandTab">
    <table class="table table-bordered" id="vehicleBrandTable">
        <thead>
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addOptionModal" data-table="vehicle_brands" data-column="brand_name">Add Vehicle Brand</button>
            <tr>
                <th>ID</th>
                <th>Vehicle Brand</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vehicleBrands as $brand): ?>
            <tr>
                <td><?= htmlspecialchars($brand['id']); ?></td>
                <td><?= htmlspecialchars($brand['brand_name']); ?></td>
                <td>
                    <button class='btn btn-warning btn-sm edit-btn' data-id='<?= $brand['id']; ?>' data-name='<?= $brand['brand_name']; ?>' data-table='vehicle_brands' data-column='brand_name'>Edit</button>
                    <button class='btn btn-danger btn-sm delete-btn' data-id='<?= $brand['id']; ?>' data-table='vehicle_brands' data-column='brand_name'>Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Vehicle Colors Tab -->
<div class="tab-pane fade" id="vehicleColor" role="tabpanel" aria-labelledby="vehicleColorTab">
    <table class="table table-bordered" id="vehicleColorTable">
        <thead>
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addOptionModal" data-table="vehicle_colors" data-column="color_name">Add Vehicle Color</button>
            <tr>
                <th>ID</th>
                <th>Vehicle Color</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vehicleColors as $color): ?>
            <tr>
                <td><?= htmlspecialchars($color['id']); ?></td>
                <td><?= htmlspecialchars($color['color_name']); ?></td>
                <td>
                    <button class='btn btn-warning btn-sm edit-btn' data-id='<?= $color['id']; ?>' data-name='<?= $color['color_name']; ?>' data-table='vehicle_colors' data-column='color_name'>Edit</button>
                    <button class='btn btn-danger btn-sm delete-btn' data-id='<?= $color['id']; ?>' data-table='vehicle_colors' data-column='color_name'>Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Vehicle Fuels Tab -->
<div class="tab-pane fade" id="vehicleFuel" role="tabpanel" aria-labelledby="vehicleFuelTab">
    <table class="table table-bordered" id="vehicleFuelTable">
        <thead>
        <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addOptionModal" data-table="vehicle_fuels" data-column="fuel_name">Add Vehicle Fuel</button>
            <tr>
                <th>ID</th>
                <th>Vehicle Fuel Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vehicleFuels as $fuel): ?>
            <tr>
                <td><?= htmlspecialchars($fuel['id']); ?></td>
                <td><?= htmlspecialchars($fuel['fuel_name']); ?></td>
                <td>
                    <button class='btn btn-warning btn-sm edit-btn' data-id='<?= $fuel['id']; ?>' data-name='<?= $fuel['fuel_name']; ?>' data-table='vehicle_fuels' data-column='fuel_name'>Edit</button>
                    <button class='btn btn-danger btn-sm delete-btn' data-id='<?= $fuel['id']; ?>' data-table='vehicle_fuels' data-column='fuel_name'>Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Reattach event listeners to new buttons
document.querySelectorAll('.add-btn').forEach(button => {
    button.addEventListener('click', function () {
        const table = this.getAttribute('data-table');
        const column = this.getAttribute('data-column');
        document.getElementById('optionTable').value = table;
        document.getElementById('optionColumn').value = column;
        document.getElementById('addOptionForm').reset();
        new bootstrap.Modal(document.getElementById('addOptionModal')).show();
    });
});

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const table = this.getAttribute('data-table');
        const column = this.getAttribute('data-column');
        document.getElementById('editOptionId').value = id;
        document.getElementById('editOptionName').value = name;
        document.getElementById('editOptionTable').value = table;
        document.getElementById('editOptionColumn').value = column;
        new bootstrap.Modal(document.getElementById('editOptionModal')).show();
    });
});

document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const table = this.getAttribute('data-table');
        const column = this.getAttribute('data-column');
        document.getElementById('deleteOptionId').value = id;
        document.getElementById('deleteOptionTable').value = table;
        document.getElementById('deleteOptionColumn').value = column;
        new bootstrap.Modal(document.getElementById('deleteOptionModal')).show();
    });
});
</script>
