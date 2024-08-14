<?php
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

$donor_email = $_SESSION['donoremail'];

// Fetch donor details for editing
$query = $con->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'Donor'");
$query->bind_param("s", $donor_email);
$query->execute();
$donor = $query->get_result()->fetch_assoc();

// Redirect if donor not found
if (!$donor) {
    header("Location: donors.php?error=Donor not found");
    exit();
}

// Process form submission if any
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the update form
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $donor_blood_type = htmlspecialchars(trim($_POST['donor_blood_type']));

    // Validate and update donor details
    $sql = $con->prepare("UPDATE users SET fullname = ?, phone = ?, address = ?, donor_blood_type = ? WHERE email = ?");
    $sql->bind_param("sssss", $fullname, $phone, $address, $donor_blood_type, $donor_email);

    if ($sql->execute()) {
        header("Location: editDonor.php?success=Profile updated successfully!");
        exit();
    } else {
        header("Location: editDonor.php?error=Update failed");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="">
    <!-- <?php include("donorMenu.php"); ?> -->
    <main class="ml-64 py-2 px-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 text-center">Edit Donor Details</h1>
            <?php if (isset($_GET['error']) || isset($_GET['success'])): ?>
                <?php
                $message = isset($_GET['error']) ? $_GET['error'] : $_GET['success'];
                $messageClass = isset($_GET['error']) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
                ?>
                <div class="w-full mb-6 p-4 rounded-md text-center font-semibold <?php echo $messageClass; ?>">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>
            <form action="editDonor.php" method="POST">
                <div class="mb-4">
                    <label for="fullname" class="block text-gray-700">Full Name</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($donor['fullname']); ?>" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-gray-700">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($donor['phone']); ?>" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-gray-700">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($donor['address']); ?>" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="donor_blood_type" class="block text-gray-700">Blood Type</label>
                    <select name="donor_blood_type" id="donorBloodgroup" required
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="" disabled>Select Blood Group</option>
                        <?php
                        $blood_groups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
                        foreach ($blood_groups as $group) {
                            $selected = ($group == $donor['donor_blood_type']) ? 'selected' : '';
                            echo "<option value=\"$group\" $selected>$group</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-150">Update</button>
            </form>
        </div>
    </main>
</body>
</html>
