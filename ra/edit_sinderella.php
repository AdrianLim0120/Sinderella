<?php
session_start();
if (!isset($_SESSION['adm_id'])) {
    header("Location: ../login_adm.php");
    exit();
}

// Database connection
require_once '../db_connect.php';

$sind_id = isset($_GET['sind_id']) ? $_GET['sind_id'] : 0;
$error_message = '';

// Get all possible uplines for dropdown
$uplines = [];
// Sinderella (id 0)
$uplines[] = [
    'sind_id' => 0,
    'sind_name' => 'Sinderella',
    'sind_status' => 'active'
];

// Get all sinderellas except current one, grouped by status
$all_uplines = [
    'active' => [],
    'pending' => [],
    'inactive' => []
];
$stmt = $conn->prepare("SELECT sind_id, sind_name, sind_status FROM sinderellas WHERE sind_id != ? ORDER BY sind_status = 'active' DESC, sind_status = 'pending' DESC, sind_status = 'inactive' DESC, sind_name ASC");
$stmt->bind_param("i", $sind_id);
$stmt->execute();
$stmt->bind_result($up_id, $up_name, $up_status);
while ($stmt->fetch()) {
    $all_uplines[$up_status][] = [
        'sind_id' => $up_id,
        'sind_name' => $up_name,
        'sind_status' => $up_status
    ];
}
$stmt->close();

// Get current upline id for default selection
$stmt = $conn->prepare("SELECT sind_upline_id FROM sinderellas WHERE sind_id = ?");
$stmt->bind_param("i", $sind_id);
$stmt->execute();
$stmt->bind_result($current_upline_id);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sind_name = ucwords(strtolower(trim($_POST['sind_name'])));
    $sind_phno = $_POST['sind_phno'];
    $sind_address = ucwords(strtolower(trim($_POST['sind_address'])));
    $sind_postcode = $_POST['sind_postcode'];
    $sind_area = $_POST['sind_area'];
    $sind_state = $_POST['sind_state'];
    $sind_icno = $_POST['sind_icno'];
    $sind_status = $_POST['sind_status'];
    $sind_labels = isset($_POST['sind_labels']) ? $_POST['sind_labels'] : [];
    $selected_upline_id = isset($_POST['sind_upline_id']) ? intval($_POST['sind_upline_id']) : 0;

    // Check for duplicate phone number
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sinderellas WHERE sind_phno = ? AND sind_id != ?");
    $stmt->bind_param("si", $sind_phno, $sind_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $error_message = 'The phone number is already used by another Sinderella.';
    } else {
        // $existing_icphoto_path = $_POST['existing_icphoto_path'] ?? '';
        // $existing_profile_path = $_POST['existing_profile_path'] ?? '';

        // $sind_ic_photo = $existing_icphoto_path;
        // $sind_profile_photo = $existing_profile_path;

        $upload_success = true;

        // IC Photo Upload
        $target_dir_ic = "../img/ic_photo/";
        $target_file_ic = $target_dir_ic . str_pad($sind_id, 4, '0', STR_PAD_LEFT) . ".jpg";
        if (isset($_FILES['sind_ic_photo']) && $_FILES['sind_ic_photo']['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($_FILES['sind_ic_photo']['tmp_name'], $target_file_ic)){
                $upload_success = false;
            }
        } else {
            // Keep existing photo if not updated
            $query = $conn->prepare("SELECT sind_icphoto_path FROM sinderellas WHERE sind_id = ?");
            $query->bind_param("i", $sind_id);
            $query->execute();
            $query->bind_result($target_file_ic);
            $query->fetch();
            $query->close();
        }

        // Profile Photo Upload
        $target_dir_profile = "../img/profile_photo/";
        $target_file_profile = $target_dir_profile . str_pad($sind_id, 4, '0', STR_PAD_LEFT) . ".jpg";
        if (isset($_FILES['sind_profile_photo']) && $_FILES['sind_profile_photo']['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($_FILES['sind_profile_photo']['tmp_name'], $target_file_profile)){
                $upload_success = true;
            }
        } else {
            // Keep existing photo if not updated
            $query = $conn->prepare("SELECT sind_profile_path FROM sinderellas WHERE sind_id = ?");
            $query->bind_param("i", $sind_id);
            $query->execute();
            $query->bind_result($target_file_profile);
            $query->fetch();
            $query->close();
        }

        // echo "<script>alert('Upload Success: " . ($upload_success ? "true" : "false") . "');</script>";

        if ($upload_success) {
            $stmt = $conn->prepare("UPDATE sinderellas SET sind_name = ?, sind_phno = ?, sind_address = ?, sind_postcode = ?, sind_area = ?, sind_state = ?, sind_icno = ?, sind_status = ?, sind_icphoto_path = ?, sind_profile_path = ?, sind_upline_id = ? WHERE sind_id = ?");
            $stmt->bind_param("ssssssssssii", $sind_name, $sind_phno, $sind_address, $sind_postcode, $sind_area, $sind_state, $sind_icno, $sind_status, $target_file_ic, $target_file_profile, $selected_upline_id, $sind_id);

            if (!$stmt->execute()) {
                die("Error updating record: " . $stmt->error);
            }
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM sind_id_label WHERE sind_id = ?");
            $stmt->bind_param("i", $sind_id);
            $stmt->execute();
            $stmt->close();

            foreach ($sind_labels as $label_id) {
                $stmt = $conn->prepare("INSERT INTO sind_id_label (sind_id, slbl_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $sind_id, $label_id);
                $stmt->execute();
                $stmt->close();
            }

            header("Location: view_sinderellas.php");
            exit();
        }
    }
}

$stmt = $conn->prepare("SELECT sind_name, sind_phno, sind_address, sind_postcode, sind_area, sind_state, sind_icno, sind_status, sind_icphoto_path, sind_profile_path FROM sinderellas WHERE sind_id = ?");
$stmt->bind_param("i", $sind_id);
$stmt->execute();
$stmt->bind_result($sind_name, $sind_phno, $sind_address, $sind_postcode, $sind_area, $sind_state, $sind_icno, $sind_status, $sind_ic_photo, $sind_profile_photo);
$stmt->fetch();
$stmt->close();

$labels = $conn->query("SELECT slbl_id, slbl_name FROM sind_label WHERE slbl_status = 'Active'");
$selected_labels = [];
$stmt = $conn->prepare("SELECT slbl_id FROM sind_id_label WHERE sind_id = ?");
$stmt->bind_param("i", $sind_id);
$stmt->execute();
$stmt->bind_result($slbl_id);
while ($stmt->fetch()) {
    $selected_labels[] = $slbl_id;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sinderella - Admin - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/styles_user.css">
    <!-- Add Select2 for searchable dropdown -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .profile-container {
            display: flex;
        }
        .profile-container .left, .profile-container .right {
            flex: 1;
            padding: 20px;
        }
        .profile-container label {
            display: block;
            margin-top: 10px;
        }
        .profile-container input[type="text"],
        .profile-container input[type="number"],
        .profile-container select,
        .profile-container textarea {
            width: calc(100% - 10px);
            padding: 5px;
            margin-right: 10px;
        }
        .profile-container button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .profile-container button:hover {
            background-color: #0056b3;
        }
        .button-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
        .profile-photo-container {
            text-align: center;
        }
        .invalid-postcode {
            color: red;
            margin-top: 5px;
        }
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 6px 0px;
            font-size: 1rem;
        }
    </style>
    <script>
        const postcodeData = <?php echo file_get_contents('../data/postcode.json'); ?>;

        function updateAreaAndState() {
            const postcode = document.getElementById('sind_postcode').value;
            let area = '';
            let state = '';
            let validPostcode = false;

            postcodeData.state.forEach(stateObj => {
                stateObj.city.forEach(cityObj => {
                    if (cityObj.postcode.includes(postcode)) {
                        area = cityObj.name;
                        state = stateObj.name;
                        validPostcode = true;
                    }
                });
            });

            if (validPostcode) {
                document.getElementById('sind_area').value = area;
                document.getElementById('sind_state').value = state;
                document.getElementById('invalid-postcode').style.display = 'none';
            } else {
                document.getElementById('sind_area').value = '';
                document.getElementById('sind_state').value = '';
                document.getElementById('invalid-postcode').style.display = 'block';
            }
        }

        function validateForm() {
            const area = document.getElementById('sind_area').value;
            const state = document.getElementById('sind_state').value;
            if (!area || !state) {
                alert('Please enter a valid postcode to populate the area and state fields.');
                return false;
            }
            return true;
        }

        function showError(message) {
            alert(message);
        }

        <?php if (!empty($error_message)): ?>
            showError('<?php echo $error_message; ?>');
        <?php endif; ?>
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#sind_upline_id').select2({
            placeholder: "Select or search upline",
            allowClear: false,
            width: 'resolve'
        });
    });
    </script>
</head>
<body>
    <div class="main-container">
        <?php include '../includes/menu/menu_adm.php'; ?>
        <div class="content-container">
            <?php include '../includes/header_adm.php'; ?>
            <div class="profile-container">
                <div class="left">
                    <h2>Edit Sinderella</h2>
                    <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm()">
                        <label for="sind_name">Name</label>
                        <input type="text" id="sind_name" name="sind_name" value="<?php echo htmlspecialchars($sind_name); ?>" required>

                        <label for="sind_phno">Phone Number</label>
                        <input type="text" id="sind_phno" name="sind_phno" value="<?php echo htmlspecialchars($sind_phno); ?>" required>

                        <label for="sind_upline_id">Upline</label>
                        <select id="sind_upline_id" name="sind_upline_id" style="width:100%;" required>
                            <!-- Sinderella option -->
                            <option value="0" <?php if ($current_upline_id == 0) echo 'selected'; ?>>Sinderella</option>
                            <?php foreach (['active', 'pending', 'inactive'] as $status): ?>
                                <?php if (count($all_uplines[$status]) > 0): ?>
                                    <optgroup label="<?php echo ucfirst($status); ?>">
                                        <?php foreach ($all_uplines[$status] as $up): ?>
                                            <option value="<?php echo $up['sind_id']; ?>"
                                                <?php if ($current_upline_id == $up['sind_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($up['sind_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>

                        <label for="sind_address">Address</label>
                        <textarea id="sind_address" name="sind_address" required><?php echo htmlspecialchars($sind_address); ?></textarea>

                        <label for="sind_postcode">Postcode</label>
                        <input type="text" id="sind_postcode" name="sind_postcode" value="<?php echo htmlspecialchars($sind_postcode); ?>" required oninput="updateAreaAndState()">
                        <div id="invalid-postcode" class="invalid-postcode" style="display: none;">Invalid postcode. Please enter a valid postcode.</div>

                        <label for="sind_area">Area</label>
                        <input type="text" id="sind_area" name="sind_area" value="<?php echo htmlspecialchars($sind_area); ?>" readonly required>

                        <label for="sind_state">State</label>
                        <input type="text" id="sind_state" name="sind_state" value="<?php echo htmlspecialchars($sind_state); ?>" readonly required>

                        <label for="sind_icno">IC Number</label>
                        <input type="text" id="sind_icno" name="sind_icno" value="<?php echo htmlspecialchars($sind_icno); ?>" required>

                        <label for="sind_status">Status</label>
                        <select id="sind_status" name="sind_status" required>
                            <option value="active" <?php if ($sind_status == 'active') echo 'selected'; ?>>Active</option>
                            <option value="inactive" <?php if ($sind_status == 'inactive') echo 'selected'; ?>>Inactive</option>
                            <option value="pending" <?php if ($sind_status == 'pending') echo 'selected'; ?>>Pending</option>
                        </select>

                        <label for="sind_labels">Labels</label>
                        <div id="sind_labels">
                            <?php while ($label = $labels->fetch_assoc()): ?>
                                <input type="checkbox" name="sind_labels[]" value="<?php echo $label['slbl_id']; ?>" <?php if (in_array($label['slbl_id'], $selected_labels)) echo 'checked'; ?>>
                                <?php echo htmlspecialchars($label['slbl_name']); ?><br>
                            <?php endwhile; ?>
                        </div>

                        <div class="button-container">
                            <button type="submit">Save Changes</button>
                            <button type="button" onclick="window.location.href='view_sinderellas.php'">Back</button>
                        </div>
                    </form>
                </div>
                <div class="right profile-photo-container">
                    <h2>Photos</h2>
                    <label for="sind_ic_photo"><b>IC Photo</b></label>
                    <img src="<?php echo htmlspecialchars($sind_ic_photo); ?>" alt="IC Photo" style="max-width: 200px;"><br>
                    <input type="file" id="sind_ic_photo" name="sind_ic_photo">
                    <input type="hidden" name="existing_icphoto_path" value="<?php echo htmlspecialchars($sind_ic_photo); ?>">
                    <br><br>
                    <label for="sind_profile_photo"><b>Profile Photo</b></label>
                    <img src="<?php echo htmlspecialchars($sind_profile_photo); ?>" alt="Profile Photo" style="max-width: 200px;"><br>
                    <input type="file" id="sind_profile_photo" name="sind_profile_photo">
                    <input type="hidden" name="existing_profile_path" value="<?php echo htmlspecialchars($sind_profile_photo); ?>">
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// // $conn->close();
?>