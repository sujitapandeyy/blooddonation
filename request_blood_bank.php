<?php
require('connection.php');
session_start();

// Check if blood bank ID is provided
if (!isset($_GET['id'])) {
    die("No blood bank selected.");
}

$bloodBankId = intval($_GET['id']);

// Fetch blood details
$sql = "SELECT bloodgroup, SUM(bloodqty) AS total_qty FROM blood_details WHERE bloodbank_id = ? GROUP BY bloodgroup";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $bloodBankId);
$stmt->execute();
$bloodDetailsResult = $stmt->get_result();

// Fetch blood bank details
$bankSql = "SELECT * FROM users WHERE id = ?";
$bankStmt = $con->prepare($bankSql);
$bankStmt->bind_param('i', $bloodBankId);
$bankStmt->execute();
$bankResult = $bankStmt->get_result();
$bloodBank = $bankResult->fetch_assoc();

// Initialize success and error message variables
$success_message = '';
$error_message = '';

// Handle POST request to submit a donation request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['useremail'])) {
        $donor_email = $_SESSION['useremail'];

        $donor_sql = "SELECT id, fullname, phone FROM users WHERE email = ? AND user_type = 'Donor'";
        $donor_stmt = $con->prepare($donor_sql);
        $donor_stmt->bind_param('s', $donor_email);
        $donor_stmt->execute();
        $donor_result = $donor_stmt->get_result();
        $donor = $donor_result->fetch_assoc();

        if ($donor) {
            $donor_id = $donor['id'];
            $donor_name = $donor['fullname'];
            $donor_phone = $donor['phone'];

            $donation_address = $_POST['donation_address'];
            $quantity = intval($_POST['quantity']);
            $message = $_POST['message'] ?? '';

            // Insert request into blood_requests table
            $request_stmt = $con->prepare("INSERT INTO blood_requests (blood_bank_id, donor_id, requester_email, requester_name, requester_phone, donation_address, quantity, message, request_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')");
            $request_stmt->bind_param("iissssii", $bloodBankId, $donor_id, $donor_email, $donor_name, $donor_phone, $donation_address, $quantity, $message);
            $request_stmt->execute();

            if ($request_stmt->affected_rows > 0) {
                $success_message = 'Request submitted successfully.';
            } else {
                $error_message = 'Error submitting request.';
            }
        } else {
            $error_message = 'Donor not found.';
        }
    } else {
        $error_message = 'You must be logged in to request blood.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Details for <?= htmlspecialchars($bloodBank['fullname']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .hero-image {
            background-image: url('img/land2.png'); /* Update the path to your image */
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="bg-gray-200 font-sans">
    <?php include('header.php'); ?>

    <section class="pt-24 pb-16">
        <div class="hero-image w-full h-60 bg-gray-300 relative">
            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <h1 class="text-4xl font-extrabold text-white">Blood Collection of <?= htmlspecialchars($bloodBank['fullname']); ?></h1>
            </div>
        </div>
        
        <div class="container mx-auto px-4 mt-10">
            <div class="flex flex-col lg:flex-row lg:space-x-10">
                <!-- Blood Details Table -->
                <div class="flex-1 bg-white p-6 rounded-lg shadow-md mb-6 lg:mb-0">
                    <h2 class="text-2xl font-semibold mb-4">Available Blood Details</h2>
                    <?php if ($bloodDetailsResult->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100 text-gray-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-md font-medium">Blood Group</th>
                                        <th class="px-6 py-3 text-left text-md font-medium">Total Blood Quantity (ml)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($row = $bloodDetailsResult->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($row['bloodgroup']); ?></td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($row['total_qty']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                            <!-- Request Form -->
                            <h2 class="text-2xl font-semibold mt-6 mb-4">Request Blood</h2>
                            <?php if ($success_message): ?>
                                <div class="p-4 rounded-md text-center font-semibold bg-green-100 text-green-800">
                                    <p><?php echo $success_message; ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($error_message): ?>
                                <div class="p-4 rounded-md text-center font-semibold bg-red-100 text-red-800">
                                    <p><?php echo $error_message; ?></p>
                                </div>
                            <?php endif; ?>
                            <form action="request_blood_bank.php?id=<?= htmlspecialchars($bloodBankId) ?>" method="POST">
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-bold mb-2" for="donation_address">Donation Address</label>
                                    <input
                                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                        type="text" id="donation_address" name="donation_address" placeholder="Enter donation Address" required>
                                    <div id="userSuggestions" class="suggestions"></div>
                                    <input type="hidden" id="userLat" name="latitude">
                                    <input type="hidden" id="userLong" name="longitude">
                                    <div>
                                        <p id="displayUserLat"></p>
                                        <p id="displayUserLong"></p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="quantity" class="block text-gray-700 font-bold mb-2">Blood Quantity</label>
                                    <input type="number" id="quantity" name="quantity" placeholder="Enter request blood quantity" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" required>
                                </div>

                                <div class="mb-4">
                                    <label for="message" class="block text-gray-700 font-bold mb-2">Message (Optional)</label>
                                    <textarea id="message" name="message" rows="4" placeholder="Enter your message to the blood bank" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"></textarea>
                                </div>

                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Submit Request
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-lg font-semibold text-red-600">Currently no blood available for this blood bank.</p>
                    <?php endif; ?>
                </div>

                <!-- Blood Bank Information -->
                <div class="flex-1 bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-2xl font-semibold mb-4">Blood Bank Information</h2>
                    <p class="text-gray-700 mb-2"><strong>Name:</strong> <?= htmlspecialchars($bloodBank['fullname']); ?></p>
                    <p class="text-gray-700 mb-2"><strong>Email:</strong> <?= htmlspecialchars($bloodBank['email']); ?></p>
                    <p class="text-gray-700 mb-2"><strong>Phone:</strong> <?= htmlspecialchars($bloodBank['phone']); ?></p>
                    <p class="text-gray-700 mb-2"><strong>Address:</strong> <?= htmlspecialchars($bloodBank['address']); ?></p>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Function to initialize address input and Google Maps API
        function initializeAddressInput() {
            const addressInput = document.getElementById('donation_address');
            const options = {
                types: ['address'],
                componentRestrictions: { country: 'us' } // Change country code as needed
            };

            const autocomplete = new google.maps.places.Autocomplete(addressInput, options);

            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                const place = autocomplete.getPlace();
                if (place.geometry) {
                    document.getElementById('userLat').value = place.geometry.location.lat();
                    document.getElementById('userLong').value = place.geometry.location.lng();
                    document.getElementById('displayUserLat').innerText = 'Latitude: ' + place.geometry.location.lat();
                    document.getElementById('displayUserLong').innerText = 'Longitude: ' + place.geometry.location.lng();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initializeAddressInput();
        });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script> <!-- Add your Google Maps API key -->
</body>

</html>

<?php
$con->close();
?>
