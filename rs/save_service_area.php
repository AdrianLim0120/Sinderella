<?php
session_start();
if (!isset($_SESSION['sind_id'])) {
    header("Location: ../login_sind.php");
    exit();
}

// Database connection
require_once '../db_connect.php';

$sind_id = $_SESSION['sind_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Delete service areas
    if (!empty($_POST['deleted_service_areas'])) {
        $deletedServiceAreas = explode(',', $_POST['deleted_service_areas']);
        foreach ($deletedServiceAreas as $service_area_id) {
            $stmt = $conn->prepare("DELETE FROM sind_service_area WHERE service_area_id = ?");
            $stmt->bind_param("i", $service_area_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update existing service areas
    if (isset($_POST['service_areas'])) {
        foreach ($_POST['service_areas'] as $service_area_id => $service_area) {
            $stmt = $conn->prepare("UPDATE sind_service_area SET area = ?, state = ? WHERE service_area_id = ?");
            $stmt->bind_param("ssi", $service_area['area'], $service_area['state'], $service_area_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Insert new service areas
    if (isset($_POST['new_service_areas'])) {
        foreach ($_POST['new_service_areas'] as $new_service_area) {
            $stmt = $conn->prepare("INSERT INTO sind_service_area (sind_id, area, state) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $sind_id, $new_service_area['area'], $new_service_area['state']);
            $stmt->execute();
            $stmt->close();
        }
    }

    // $conn->close();
    header("Location: manage_profile.php?success=1");
    exit();
}
?>