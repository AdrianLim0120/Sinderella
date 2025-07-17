<?php
session_start();
if (!isset($_SESSION['adm_id'])) {
    header("Location: ../login_adm.php");
    exit();
}

// Database connection
require_once '../db_connect.php';

$service_id = isset($_GET['service_id']) ? $_GET['service_id'] : 0;
$service = [];
$pricing = [];
$addons = [];

if ($service_id) {
    // Fetch service details
    $service_query = "SELECT service_name, service_price, service_duration FROM service_pricing WHERE service_id = ?";
    $stmt = $conn->prepare($service_query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $service_result = $stmt->get_result();
    $service = $service_result->fetch_assoc();

    // Fetch pricing details
    $pricing_query = "SELECT * FROM pricing WHERE service_id = ?";
    $stmt = $conn->prepare($pricing_query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $pricing_result = $stmt->get_result();
    $pricing = $pricing_result->fetch_assoc();

    // Fetch add-ons
    $addons_query = "SELECT ao_id, ao_desc, ao_price, ao_platform, ao_sind, ao_duration, ao_status FROM addon WHERE service_id = ?";
    $stmt = $conn->prepare($addons_query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $addons_result = $stmt->get_result();
    while ($addon = $addons_result->fetch_assoc()) {
        $addons[] = $addon;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $service_id ? 'Modify' : 'Add'; ?> Service - Admin - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/styles_user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .profile-container label {
            display: block;
            margin-top: 10px;
        }
        .profile-container input[type="text"],
        .profile-container input[type="number"] {
            width: calc(50% - 10px);
            padding: 5px;
            margin-right: 10px;
        }
        .profile-container .addon-container {
            margin-top: 20px;
        }
        .profile-container .addon-item {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .profile-container .addon-item input[type="text"] {
            width: calc(25% - 10px);
        }
        .profile-container .addon-item input[type="number"] {
            width: calc(15% - 10px);
        }
        .profile-container .addon-item button {
            margin-left: 10px;
        }
        .profile-container .addon-container button {
            /* margin-top: 10px; */
            margin: 5px 0px;
        }
        .profile-container .addon-container input[type="text"],
        .profile-container .addon-container input[type="number"] {
            width: 90%;
            margin: 5px 1px;
            /* margin-right: 10px; */
        }

        /* for add on section - activate & deactivate button */
        #activate-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        #activate-button:hover {
            background-color: #45a049;
        }
        #deactivate-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        #deactivate-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php include '../includes/menu/menu_adm.php'; ?>
        <div class="content-container">
            <?php include '../includes/header_adm.php'; ?>
            <div class="profile-container">
                <h2><?php echo $service_id ? 'Modify' : 'Add'; ?> Service</h2>
                <form id="serviceForm" method="POST" action="save_service.php">
                    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">

                    <label for="service_name">Service Name</label>
                    <input type="text" id="service_name" name="service_name" value="<?php echo htmlspecialchars($service['service_name'] ?? ''); ?>" required>

                    <label for="service_duration">Service Duration (Hours)</label>
                    <input type="number" step="0.01" id="service_duration" name="service_duration" value="<?php echo htmlspecialchars($service['service_duration'] ?? ''); ?>" required>

                    <h3>Service Pricing</h3>

                    <label for="service_price">Total Price</label>
                    <input type="number" step="0.01" id="service_price" name="service_price" value="<?php echo htmlspecialchars($service['service_price'] ?? ''); ?>" required>

                    <label for="pr_platform">Platform</label>
                    <input type="number" step="0.01" id="pr_platform" name="pr_platform" value="<?php echo htmlspecialchars($pricing['pr_platform'] ?? ''); ?>" required>

                    <label for="pr_sind">Sinderella</label>
                    <input type="number" step="0.01" id="pr_sind" name="pr_sind" value="<?php echo htmlspecialchars($pricing['pr_sind'] ?? ''); ?>" required>

                    <label for="pr_lvl1">Level 1 Referral</label>
                    <input type="number" step="0.01" id="pr_lvl1" name="pr_lvl1" value="<?php echo htmlspecialchars($pricing['pr_lvl1'] ?? ''); ?>" required>

                    <label for="pr_lvl2">Level 2 Referral</label>
                    <input type="number" step="0.01" id="pr_lvl2" name="pr_lvl2" value="<?php echo htmlspecialchars($pricing['pr_lvl2'] ?? ''); ?>" required>

                    <label for="pr_lvl3">Level 3 Referral</label>
                    <input type="number" step="0.01" id="pr_lvl3" name="pr_lvl3" value="<?php echo htmlspecialchars($pricing['pr_lvl3'] ?? ''); ?>" required>

                    <label for="pr_lvl4">Level 4 Referral</label>
                    <input type="number" step="0.01" id="pr_lvl4" name="pr_lvl4" value="<?php echo htmlspecialchars($pricing['pr_lvl4'] ?? ''); ?>" required>

                    <h3>Sinderella Income Breakdown</h3>
                    <!-- <table border="1" style="width: 100%; border-collapse: collapse; text-align: center;">
                        <thead style="background-color: #0c213b; color: white;">
                            <tr>
                                <th>Basic</th>
                                <th>Rating</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="number" step="0.01" id="pr_br_basic" name="pr_br_basic" value="<?php echo htmlspecialchars($pricing['pr_br_basic'] ?? ''); ?>" required></td>
                                <td><input type="number" step="0.01" id="pr_br_rate" name="pr_br_rate" value="<?php echo htmlspecialchars($pricing['pr_br_rate'] ?? ''); ?>" required></td>
                                <td><input type="number" step="0.01" id="pr_br_perf" name="pr_br_perf" value="<?php echo htmlspecialchars($pricing['pr_br_perf'] ?? ''); ?>" required></td>
                            </tr>
                        </tbody>
                    </table> -->
                    <label for="pr_br_basic">Basic</label>
                    <input type="number" step="0.01" id="pr_br_basic" name="pr_br_basic" value="<?php echo htmlspecialchars($pricing['pr_br_basic'] ?? ''); ?>" required>

                    <label for="pr_br_rate">Rating</label>
                    <input type="number" step="0.01" id="pr_br_rate" name="pr_br_rate" value="<?php echo htmlspecialchars($pricing['pr_br_rate'] ?? ''); ?>" required>

                    <label for="pr_br_perf">Performance</label>
                    <input type="number" step="0.01" id="pr_br_perf" name="pr_br_perf" value="<?php echo htmlspecialchars($pricing['pr_br_perf'] ?? ''); ?>" required>

                    <!-- <div class="addon-container">
                        <h3>Add-ons</h3>
                        <div id="addons">
                            <?php foreach ($addons as $addon): ?>
                                <div class="addon-item">
                                    <input type="hidden" name="addon_id[]" value="<?php echo htmlspecialchars($addon['ao_id']); ?>">
                                    <input type="text" name="addon_desc[]" value="<?php echo htmlspecialchars($addon['ao_desc']); ?>" required>
                                    <input type="number" step="0.01" name="addon_price[]" value="<?php echo htmlspecialchars($addon['ao_price']); ?>" required>
                                    <input type="number" step="0.01" name="addon_duration[]" value="<?php echo htmlspecialchars($addon['ao_duration']); ?>" required>
                                    <button type="button" onclick="removeAddon(this)">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" onclick="addAddon()">Add Add-on</button>
                    </div> -->

                    <div class="addon-container">
                        <h3>Add-ons</h3>
                        <table id="addonsTable" border="1" style="width: 100%; border-collapse: collapse;">
                            <thead style="background-color: #0c213b; color: white;">
                                <tr>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Platform</th>
                                    <th>Sinderella</th>
                                    <th>Duration (Hours)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($addons as $addon): ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="addon_id[]" value="<?php echo htmlspecialchars($addon['ao_id']); ?>">
                                            <input type="text" name="addon_desc[]" value="<?php echo htmlspecialchars($addon['ao_desc']); ?>" required>
                                        </td>
                                        <td><input type="number" step="0.01" name="addon_price[]" value="<?php echo htmlspecialchars($addon['ao_price']); ?>" required></td>
                                        <td><input type="number" step="0.01" name="addon_platform[]" value="<?php echo htmlspecialchars($addon['ao_platform']); ?>" required></td>
                                        <td><input type="number" step="0.01" name="addon_sind[]" value="<?php echo htmlspecialchars($addon['ao_sind']); ?>" required></td>
                                        <td><input type="number" step="0.01" name="addon_duration[]" value="<?php echo htmlspecialchars($addon['ao_duration']); ?>" required></td>
                                        <!-- <td><button type="button" onclick="removeAddonRow(this)">Remove</button></td> -->
                                        <td>
                                            <?php if ($addon['ao_status'] == 'active'): ?>
                                                <button type="button" id="deactivate-button" onclick="toggleAddonStatus(<?php echo $addon['ao_id']; ?>, 'inactive')">Deactivate</button>
                                            <?php else: ?>
                                                <button type="button" id="activate-button" onclick="toggleAddonStatus(<?php echo $addon['ao_id']; ?>, 'active')">Activate</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" onclick="addAddonRow()">Add Add-on</button>
                    <br>
                    <button type="submit">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function toggleAddonStatus(addonId, status) {
            if (confirm("Are you sure you want to " + (status === 'inactive' ? "deactivate" : "activate") + " this add-on?")) {
                $.ajax({
                    url: 'toggle_addon_status.php',
                    type: 'POST',
                    data: {
                        addon_id: addonId,
                        status: status
                    },
                    success: function(response) {
                        if (response === 'success') {
                            alert("Add-on " + (status === 'inactive' ? "deactivated" : "activated") + " successfully.");
                            location.reload();
                        } else {
                            alert("Failed to update add-on status.");
                        }
                    }
                });
            }
        }

        function addAddon() {
            var addonContainer = document.getElementById('addons');
            var addonItem = document.createElement('div');
            addonItem.className = 'addon-item';
            addonItem.innerHTML = `
                <input type="hidden" name="addon_id[]" value="">
                <input type="text" name="addon_desc[]" placeholder="Add-on Description" required>
                <input type="number" step="0.01" name="addon_price[]" placeholder="Add-on Price" required>
                <input type="number" step="0.01" name="addon_duration[]" placeholder="Add-on Duration (Hours)" required>
                <button type="button" onclick="removeAddon(this)">Remove</button>
            `;
            addonContainer.appendChild(addonItem);
        }

        function addAddonRow() {
            var table = document.getElementById('addonsTable').getElementsByTagName('tbody')[0];
            var newRow = table.insertRow();
            newRow.innerHTML = `
                <tr>
                    <td>
                        <input type="hidden" name="addon_id[]" value="">
                        <input type="text" name="addon_desc[]" placeholder="Add-on Description" required>
                    </td>
                    <td><input type="number" step="0.01" name="addon_price[]" placeholder="Add-on Price" required></td>
                    <td><input type="number" step="0.01" name="addon_platform[]" placeholder="Platform" required></td>
                    <td><input type="number" step="0.01" name="addon_sind[]" placeholder="Sinderella" required></td>
                    <td><input type="number" step="0.01" name="addon_duration[]" placeholder="Duration (Hours)" required></td>
                    <td><button type="button" onclick="removeAddonRow(this)">Remove</button></td>
                </tr>
            `;
        }

        function removeAddon(button) {
            button.parentElement.remove();
        }

        function removeAddonRow(button) {
            button.closest('tr').remove();
        }

        document.getElementById('serviceForm').addEventListener('submit', function(event) {
            event.preventDefault();

            var serviceName = document.getElementById('service_name').value.trim();
            serviceName = serviceName.charAt(0).toUpperCase() + serviceName.slice(1).toLowerCase();
            document.getElementById('service_name').value = serviceName;

            document.querySelectorAll('input[name="addon_desc[]"]').forEach(function(input) {
                var desc = input.value.trim();
                desc = desc.charAt(0).toUpperCase() + desc.slice(1).toLowerCase();
                input.value = desc;
            });

            var totalPrice = parseFloat(document.getElementById('service_price').value);
            var prPlatform = parseFloat(document.getElementById('pr_platform').value);
            var prSind = parseFloat(document.getElementById('pr_sind').value);
            var prLvl1 = parseFloat(document.getElementById('pr_lvl1').value);
            var prLvl2 = parseFloat(document.getElementById('pr_lvl2').value);
            var prLvl3 = parseFloat(document.getElementById('pr_lvl3').value);
            var prLvl4 = parseFloat(document.getElementById('pr_lvl4').value);

            var sumCategories = (prPlatform + prSind + prLvl1 + prLvl2 + prLvl3 + prLvl4).toFixed(2);
            if (sumCategories != totalPrice.toFixed(2)) {
                alert('Platform + Sinderella + Level 1-4 Referral must equal Total Price\nTotal Price: ' + totalPrice.toFixed(2) + '\nSum of Categories: ' + sumCategories);
                return;
            }

            var prBrBasic = parseFloat(document.getElementById('pr_br_basic').value);
            var prBrRate = parseFloat(document.getElementById('pr_br_rate').value);
            var prBrPerf = parseFloat(document.getElementById('pr_br_perf').value);

            var sumBreakdown = (prBrBasic + prBrRate + prBrPerf).toFixed(2);
            if (sumBreakdown != prSind.toFixed(2)) {
                alert('Basic + Rating + Performance must equal Sinderella\nSinderella: ' + prSind.toFixed(2) + '\nSum of Breakdown: ' + sumBreakdown);
                return;
            }

            var addonRows = document.querySelectorAll('#addonsTable tbody tr');
            for (var i = 0; i < addonRows.length; i++) {
                var addonPrice = parseFloat(addonRows[i].querySelector('input[name="addon_price[]"]').value);
                var addonPlatform = parseFloat(addonRows[i].querySelector('input[name="addon_platform[]"]').value);
                var addonSind = parseFloat(addonRows[i].querySelector('input[name="addon_sind[]"]').value);

                if ((addonPlatform + addonSind).toFixed(2) != addonPrice.toFixed(2)) {
                    alert('For Add-on ' + (i + 1) + ': Add-on Price must equal Platform + Sinderella\nAdd-on Price: ' + addonPrice.toFixed(2) + '\nSum of Platform + Sinderella: ' + (addonPlatform + addonSind).toFixed(2));
                    return;
                }
            }

            this.submit();
        });
    </script>
</body>
</html>

<?php
// // $conn->close();
?>