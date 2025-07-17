<?php
session_start();
if (!isset($_SESSION['adm_id'])) {
    header("Location: ../login_adm.php");
    exit();
}

// Database connection
require_once '../db_connect.php';

$service_id = isset($_POST['service_id']) ? $_POST['service_id'] : 0;
$service_name = ucfirst(strtolower(trim($_POST['service_name'])));
$service_price = number_format((float)$_POST['service_price'], 2, '.', '');
$service_duration = round((float)$_POST['service_duration'], 2);
$pr_platform = number_format((float)$_POST['pr_platform'], 2, '.', '');
$pr_sind = number_format((float)$_POST['pr_sind'], 2, '.', '');
$pr_lvl1 = number_format((float)$_POST['pr_lvl1'], 2, '.', '');
$pr_lvl2 = number_format((float)$_POST['pr_lvl2'], 2, '.', '');
$pr_lvl3 = number_format((float)$_POST['pr_lvl3'], 2, '.', '');
$pr_lvl4 = number_format((float)$_POST['pr_lvl4'], 2, '.', '');
$pr_br_basic = number_format((float)$_POST['pr_br_basic'], 2, '.', '');
$pr_br_rate = number_format((float)$_POST['pr_br_rate'], 2, '.', '');
$pr_br_perf = number_format((float)$_POST['pr_br_perf'], 2, '.', '');

$addon_id = isset($_POST['addon_id']) ? $_POST['addon_id'] : [];
$addon_desc = isset($_POST['addon_desc']) ? $_POST['addon_desc'] : [];
$addon_price = isset($_POST['addon_price']) ? $_POST['addon_price'] : [];
$addon_duration = isset($_POST['addon_duration']) ? $_POST['addon_duration'] : [];

// Validate totals
if (($pr_platform + $pr_sind + $pr_lvl1 + $pr_lvl2 + $pr_lvl3 + $pr_lvl4) != $service_price) {
    echo "Platform + Sinderella + Level 1-4 Referral must equal Total Price";
    exit();
}

if (($pr_br_basic + $pr_br_rate + $pr_br_perf) != $pr_sind) {
    echo "Basic + Rating + Performance must equal Sinderella";
    exit();
}

if ($service_id) {
    // Update existing service
    $stmt = $conn->prepare("UPDATE service_pricing SET service_name = ?, service_price = ?, service_duration = ? WHERE service_id = ?");
    $stmt->bind_param("sddi", $service_name, $service_price, $service_duration, $service_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE pricing SET pr_platform = ?, pr_sind = ?, pr_lvl1 = ?, pr_lvl2 = ?, pr_lvl3 = ?, pr_lvl4 = ?, pr_br_basic = ?, pr_br_rate = ?, pr_br_perf = ? WHERE service_id = ?");
    $stmt->bind_param("dddddddddi", $pr_platform, $pr_sind, $pr_lvl1, $pr_lvl2, $pr_lvl3, $pr_lvl4, $pr_br_basic, $pr_br_rate, $pr_br_perf, $service_id);
    $stmt->execute();
    $stmt->close();
} else {
    // Insert new service
    $stmt = $conn->prepare("INSERT INTO service_pricing (service_name, service_price, service_duration) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $service_name, $service_price, $service_duration);
    $stmt->execute();
    $service_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO pricing (service_id, pr_platform, pr_sind, pr_lvl1, pr_lvl2, pr_lvl3, pr_lvl4, pr_br_basic, pr_br_rate, pr_br_perf) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iddddddddd", $service_id, $pr_platform, $pr_sind, $pr_lvl1, $pr_lvl2, $pr_lvl3, $pr_lvl4, $pr_br_basic, $pr_br_rate, $pr_br_perf);
    $stmt->execute();
    $stmt->close();
}

// Update or insert add-ons
foreach ($addon_id as $index => $id) {
    $addon_desc_item = ucfirst(strtolower(trim($addon_desc[$index])));
    $addon_price_item = number_format((float)$addon_price[$index], 2, '.', '');
    $addon_platform_item = number_format((float)$_POST['addon_platform'][$index], 2, '.', '');
    $addon_sind_item = number_format((float)$_POST['addon_sind'][$index], 2, '.', '');
    $addon_duration_item = round((float)$addon_duration[$index], 2);

    if ($id) {
        // Update existing add-on
        $stmt = $conn->prepare("UPDATE addon SET ao_desc = ?, ao_price = ?, ao_platform = ?, ao_sind = ?, ao_duration = ? WHERE ao_id = ?");
        $stmt->bind_param("sdddii", $addon_desc_item, $addon_price_item, $addon_platform_item, $addon_sind_item, $addon_duration_item, $id);
    } else {
        // Insert new add-on
        $stmt = $conn->prepare("INSERT INTO addon (service_id, ao_desc, ao_price, ao_platform, ao_sind, ao_duration) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdddd", $service_id, $addon_desc_item, $addon_price_item, $addon_platform_item, $addon_sind_item, $addon_duration_item);
    }
    $stmt->execute();
    $stmt->close();
}

// $conn->close();

header("Location: manage_pricing.php");
exit();
?>