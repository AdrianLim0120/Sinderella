<?php
session_start();
if (!isset($_SESSION['cust_id'])) {
    header("Location: ../login_cust.php");
    exit();
}

// Database connection
require_once '../db_connect.php';

$cust_id = $_SESSION['cust_id'];

// Fetch customer addresses
$address_query = "SELECT * FROM cust_addresses WHERE cust_id = ?";
$stmt = $conn->prepare($address_query);
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$addresses_result = $stmt->get_result();
$stmt->close();

// Fetch active services
$services_query = "SELECT service_id, service_name, service_price, service_duration FROM service_pricing WHERE service_status = 'active'";
$services_result = $conn->query($services_query);

// $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Booking - Customer - Sinderella</title>
    <link rel="icon" href="../img/sinderella_favicon.png"/>
    <link rel="stylesheet" href="../includes/css/styles_user.css">
    <style>
        .booking-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .booking-container h2 {
            margin-top: 0;
        }
        .booking-container label {
            display: block;
            margin-top: 10px;
        }
        .booking-container input[type="text"],
        .booking-container input[type="date"],
        .booking-container input[type="time"],
        .booking-container select {
            width: 300px;
            padding: 5px;
            margin-right: 10px;
        }
        .booking-container button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .booking-container button:hover {
            background-color: #0056b3;
        }
        .addon-container {
            margin-top: 20px;
        }
        .addon-item {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .addon-item input[type="checkbox"] {
            margin-right: 10px;
        }
        .sinderella-container {
            margin-top: 20px;
        }
        .sinderella-item {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: flex;
            align-items: center;
        }
        .sinderella-item img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php include '../includes/menu/menu_cust.php'; ?>
        <div class="content-container">
            <?php include '../includes/header_cust.php'; ?>
            <div class="booking-container">
                <h2>Add Booking</h2>
                <form id="bookingForm" method="POST" action="confirm_booking.php">

                    <label for="address">Select Address</label>
                    <select id="address" name="address" required>
                        <option value="">-- Select Address --</option>
                        <?php while ($addr = $addresses_result->fetch_assoc()): ?>
                            <option 
                                value="<?php echo htmlspecialchars("{$addr['cust_address']}, {$addr['cust_postcode']}, {$addr['cust_area']}, {$addr['cust_state']}"); ?>" 
                                data-area="<?php echo $addr['cust_area']; ?>" 
                                data-state="<?php echo $addr['cust_state']; ?>">
                                <?php echo "{$addr['cust_postcode']}, {$addr['cust_area']}, {$addr['cust_state']}"; ?> - <?php echo $addr['cust_address']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label for="booking_date">Select Date</label>
                    <input type="date" id="booking_date" name="booking_date" required>

                    <label for="service">Select Service</label>
                    <select id="service" name="service" required>
                        <?php while ($service = $services_result->fetch_assoc()): ?>
                            <option value="<?php echo $service['service_id']; ?>" data-duration="<?php echo $service['service_duration']; ?>" data-price="<?php echo $service['service_price']; ?>">
                                <?php echo htmlspecialchars($service['service_name']); ?> (RM <?php echo number_format($service['service_price'], 2); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <div class="addon-container">
                        <h3>Add-ons</h3>
                        <div id="addons">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <div class="sinderella-container">
                        <h3 id="sinderellaTitle">Available Sinderellas</h3>
                        <div id="sinderellaList"></div>
                    </div>

                    <input type="hidden" name="start_time" id="start_time">
                    <input type="hidden" id="full_address" name="full_address">
                    <!-- <input type="hidden" name="address" value="<?php echo htmlspecialchars($selected_address); ?>"> -->

                    <button type="submit">Confirm Booking</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookingDateInput = document.getElementById('booking_date');
            const sinderellaSelect = document.getElementById('sinderella');
            const startTimeInput = document.getElementById('start_time');
            const serviceSelect = document.getElementById('service');
            const addonsContainer = document.getElementById('addons');
            const bookingForm = document.getElementById('bookingForm');
            const addressSelect = document.getElementById('address');
            // document.getElementById('full_address').value = addressSelect.value;
            document.getElementById('full_address').value = addressSelect.options[addressSelect.selectedIndex].value;

            // Fetch add-ons for the first service when the page loads
            fetchAddons(serviceSelect.value);

            bookingDateInput.addEventListener('change', function() {
                const selectedDate = bookingDateInput.value;
                if (selectedDate) {
                    fetchAvailableSinderellas(selectedDate);
                }
            });

            startTimeInput.addEventListener('change', function() {
                correctStartTime();
                calculateEndTime();
            });
            serviceSelect.addEventListener('change', function() {
                fetchAddons(serviceSelect.value);
                calculateEndTime();
            });

            bookingForm.addEventListener('submit', function(event) {
                correctStartTime();
                if (!validateDate()) {
                    event.preventDefault();
                    alert('The selected date cannot be earlier than today.');
                }
            });

            function fetchAvailableSinderellas() {
                const selectedDate = bookingDateInput.value;
                const selectedOption = addressSelect.options[addressSelect.selectedIndex];
                const area = selectedOption.dataset.area;
                const state = selectedOption.dataset.state;

                if (!selectedDate || !area || !state) return;

                fetch(`get_available_sinderellas.php?date=${selectedDate}&area=${encodeURIComponent(area)}&state=${encodeURIComponent(state)}`)
                    .then(response => response.json()) // temp change from "json" to "text"
                    .then(data => { // temp change from "data" to "text"
                        // console.log('Raw response:', text); // Debug output
                        // const json = JSON.parse(text); // Try parsing manually

                        const sinderellaList = document.getElementById('sinderellaList');
                        const title = document.getElementById('sinderellaTitle');
                        sinderellaList.innerHTML = '';

                        if (data.length === 0) {
                            title.textContent = `No Sinderellas available on ${selectedDate} in ${area}, ${state}`;
                            return;
                        }

                        title.textContent = `Sinderellas available on ${selectedDate} in ${area}, ${state}`;
                        
                        data.sort((a, b) => (b.is_previous ? 1 : 0) - (a.is_previous ? 1 : 0));
                        
                        data.forEach(s => {
                            const div = document.createElement('div');
                            div.className = 'sinderella-item';

                            let tag = '';
                            if (s.is_previous) {
                                tag = `<span style="background:#4CAF50;color:#fff;padding:2px 8px;border-radius:5px;font-size:16px;margin-left:8px;">💥Your previous Sinderella</span>`;
                            }

                            let timeOptions = '';
                            if (s.available_times && s.available_times.length > 0) {
                                s.available_times.forEach(time => {
                                    timeOptions += `
                                        <label>
                                            <input type="radio" name="sinderella_time" value="${s.sind_id}|${time}">
                                            ${formatTime(time)}
                                        </label>
                                    `;
                                });
                            }

                            div.innerHTML = `
                                <img src="${s.sind_profile_full_path}" alt="${s.sind_name}">
                                <div>
                                    <strong>${s.sind_name}</strong> ${tag}<br>
                                    ${timeOptions || '<span style="color:red;">No valid time slots</span>'}
                                </div>
                            `;
                            // div.innerHTML = `
                            //     <img src="${s.sind_profile_full_path}" alt="${s.sind_name}">
                            //     <div>
                            //         <strong>${s.sind_name}</strong><br>
                            //         <label>
                            //             <input type="radio" name="sinderella_time" value="${s.sind_id}|${s.available_from1}">
                            //             ${formatTime(s.available_from1)}
                            //         </label>
                            //         ${s.available_from2 ? `
                            //         <label>
                            //             <input type="radio" name="sinderella_time" value="${s.sind_id}|${s.available_from2}">
                            //             ${formatTime(s.available_from2)}
                            //         </label>` : ''}
                            //     </div>
                            // `;
                            sinderellaList.appendChild(div);
                        })
                        .catch(error => {
                            console.error('Error parsing JSON:', error);
                            alert('Failed to fetch available Sinderellas.');
                        });
                    });
            }

            bookingDateInput.addEventListener('change', fetchAvailableSinderellas);
            addressSelect.addEventListener('change', fetchAvailableSinderellas);

            function fetchAddons(serviceId) {
                console.log(`Fetching add-ons for service ID: ${serviceId}`);
                fetch(`get_addons.php?service_id=${serviceId}`)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Error:', data.error);
                            alert('Failed to fetch add-ons.');
                            return;
                        }
                        console.log('Add-ons data:', data);
                        addonsContainer.innerHTML = '';
                        data.forEach(addon => {
                            const addonItem = document.createElement('div');
                            addonItem.className = 'addon-item';
                            addonItem.innerHTML = `
                                <input type="checkbox" name="addons[]" value="${addon.ao_id}" data-duration="${addon.ao_duration}" data-price="${addon.ao_price}">
                                ${addon.ao_desc} (RM ${parseFloat(addon.ao_price).toFixed(2)})
                            `;
                            addonsContainer.appendChild(addonItem);
                        });
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        alert('Failed to fetch add-ons.');
                    });
            }

            function correctStartTime() {
                const startTime = startTimeInput.value;
                const [hours, minutes] = startTime.split(':').map(Number);
                let correctedMinutes = minutes;

                if (minutes < 15) {
                    correctedMinutes = 0;
                } else if (minutes < 45) {
                    correctedMinutes = 30;
                } else {
                    correctedMinutes = 0;
                    hours += 1;
                }

                const correctedTime = `${String(hours).padStart(2, '0')}:${String(correctedMinutes).padStart(2, '0')}`;
                startTimeInput.value = correctedTime;
            }

            function calculateEndTime() {
                const startTime = startTimeInput.value;
                const serviceDuration = parseFloat(serviceSelect.selectedOptions[0].dataset.duration);
                let totalDuration = serviceDuration;

                const addonCheckboxes = document.querySelectorAll('.addon-item input[type="checkbox"]');
                addonCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        totalDuration += parseFloat(checkbox.dataset.duration);
                    }
                });

                const [startHours, startMinutes] = startTime.split(':').map(Number);
                const endMinutes = startMinutes + totalDuration * 60;
                const endHours = startHours + Math.floor(endMinutes / 60);
                const endTime = `${String(endHours).padStart(2, '0')}:${String(endMinutes % 60).padStart(2, '0')}`;

                const selectedRadio = document.querySelector('input[name="sinderella_time"]:checked');
                if (selectedRadio) {
                    const [sindId, availableFrom] = selectedRadio.value.split('|');
                    const availableFromDate = new Date(`1970-01-01T${availableFrom}:00`);
                    const endTimeDate = new Date(`1970-01-01T${endTime}:00`);
                }
            }

            function validateDate() {
                const selectedDate = new Date(bookingDateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Set to the start of today
                return selectedDate >= today;
            }

            function formatTime(time) {
                const [hours, minutes] = time.split(':');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const formattedHours = hours % 12 || 12;
                return `${formattedHours}:${minutes} ${ampm}`;
            }

            // Prevent selecting past dates
            const now = new Date();
            const timezoneOffset = now.getTimezoneOffset() * 60000;
            const localNow = new Date(now.getTime() - timezoneOffset);
            const localNowISO = localNow.toISOString().slice(0, 16);

            const year = localNow.getFullYear();
            const month = localNow.getMonth(); // 0-based
            const nextMonth = new Date(year, month + 2, 0); // Last day of next month

            // Format as YYYY-MM-DD using local date
            const lastAllowedISO = [
                nextMonth.getFullYear(),
                String(nextMonth.getMonth() + 1).padStart(2, '0'),
                String(nextMonth.getDate()).padStart(2, '0')
            ].join('-');

            bookingDateInput.min = localNowISO.split('T')[0];
            bookingDateInput.max = lastAllowedISO;

            bookingForm.addEventListener('submit', function (e) {
                const selectedRadio = document.querySelector('input[name=\"sinderella_time\"]:checked');
                if (!selectedRadio) {
                    e.preventDefault();
                    alert('Please select a Sinderella and time slot.');
                    return;
                }

                const [sindId, time] = selectedRadio.value.split('|');
                document.getElementById('start_time').value = time;
                // document.getElementById('full_address').value = addressSelect.value;
                document.getElementById('full_address').value = addressSelect.options[addressSelect.selectedIndex].value;


                // Optionally, add a hidden input for sind_id too:
                let sindIdInput = document.createElement('input');
                sindIdInput.type = 'hidden';
                sindIdInput.name = 'sinderella';
                sindIdInput.value = sindId;
                bookingForm.appendChild(sindIdInput);
            });
        });
    </script>
</body>
</html>