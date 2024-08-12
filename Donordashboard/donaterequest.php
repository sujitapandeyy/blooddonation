<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: login.php?error=Login first");
    exit();
}

$donor_email = $_SESSION['donoremail'];

// Fetch available blood banks for dropdown
$query = $con->prepare("SELECT id, fullname FROM users WHERE user_type = 'BloodBank'");
$query->execute();
$blood_banks = $query->get_result();

// Fetch donor details
$query = $con->prepare("SELECT fullname, phone, address FROM users WHERE email = ? AND user_type = 'Donor'");
$query->bind_param("s", $donor_email);
$query->execute();
$donor = $query->get_result()->fetch_assoc();

if (!$donor) {
    header("Location: donors.php?error=Donor not found");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $blood_bank_id = intval($_POST['blood_bank']);
    $request_message = htmlspecialchars(trim($_POST['message']));

    // Insert donation request into the database
    $query = $con->prepare("INSERT INTO donation_requests (donor_email, blood_bank_id, message, request_date) VALUES (?, ?, ?, NOW())");
    $query->bind_param("sis", $donor_email, $blood_bank_id, $request_message);

    if ($query->execute()) {
        header("Location: donateRequest.php?success=Request submitted successfully!");
        exit();
    } else {
        header("Location: donateRequest.php?error=Request submission failed");
        exit();
    }
}

// Fetch previous donation requests
$query = $con->prepare("SELECT dr.id, dr.message,dr.request_date, dr.status, dr.appointment_time, bb.fullname as blood_bank 
                        FROM donation_requests dr 
                        JOIN users bb ON dr.blood_bank_id = bb.id 
                        WHERE dr.donor_email = ? 
                        ORDER BY dr.request_date DESC");
$query->bind_param("s", $donor_email);
$query->execute();
$previous_requests = $query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>

</head>
<body class="bg-gray-100">
    <!-- <?php include("donorMenu.php"); ?> -->

    <main class="ml-64 py-2 px-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 text-center">Request Blood Donation</h1>
            <?php if (isset($_GET['error']) || isset($_GET['success'])): ?>
                <?php
                $message = isset($_GET['error']) ? $_GET['error'] : $_GET['success'];
                $messageClass = isset($_GET['error']) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
                ?>
                <div class="w-full mb-6 p-4 rounded-md text-center font-semibold <?php echo $messageClass; ?>">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>
            <form action="donateRequest.php" method="POST">
                <div class="mb-4">
                    <label for="blood_bank" class="block text-gray-700">Select Blood Bank</label>
                    <select name="blood_bank" id="blood_bank" required
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="" disabled selected>Select Blood Bank</option>
                        <?php while ($row = $blood_banks->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['fullname']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="message" class="block text-gray-700">Message</label>
                    <textarea id="message" name="message" rows="4" class="w-full p-2 border border-gray-300 rounded"></textarea>
                </div>
                <button type="submit" class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-150">Submit Request</button>
            </form>
        </div>

        <!-- Display previous donation requests -->
        <div class="bg-white p-6 mt-8 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Previous Donation Requests</h2>
            <table class="min-w-full bg-white border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-500 text-white">
                        <th class="px-4 py-2 border border-gray-300">Request Date</th>
                        <th class="px-4 py-2 border border-gray-300">Blood Bank</th>
                        <th class="px-4 py-2 border border-gray-300">Message</th>
                        <th class="px-4 py-2 border border-gray-300">Status</th>
                        <th class="px-4 py-2 border border-gray-300">Appointment Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = $previous_requests->fetch_assoc()): ?>
                        <tr>_
                            <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['request_date']); ?></td>
                            <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['blood_bank']); ?></td>
                            <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['message']); ?></td>
                            <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['status']); ?></td>
                            <td class="px-4 py-2 border border-gray-300"><?php echo htmlspecialchars($request['appointment_time']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
