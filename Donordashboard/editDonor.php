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
$query = $con->prepare("
    SELECT u.*, d.donor_blood_type, d.dob, d.weight, d.gender, d.last_donation_date, d.profile_image
    FROM users u 
    JOIN donor d ON u.id = d.id
    WHERE u.email = ? AND u.user_type = 'Donor'
");
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
    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../upload/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check file size and format
        if ($_FILES["profile_image"]["size"] > 1000000) {
            header("Location: editDonor.php?error=File too large");
            exit();
        }
        
        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            header("Location: editDonor.php?error=Invalid file format");
            exit();
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // Update profile image in database
            $sql = $con->prepare("UPDATE donor SET profile_image = ? WHERE id = (SELECT id FROM users WHERE email = ?)");
            $sql->bind_param("ss", $target_file, $donor_email);
            $sql->execute();
        } else {
            header("Location: editDonor.php?error=Image upload failed");
            exit();
        }
    }

    // Process other form fields
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $donor_blood_type = htmlspecialchars(trim($_POST['donor_blood_type']));
    $latitude = htmlspecialchars(trim($_POST['latitude']));
    $longitude = htmlspecialchars(trim($_POST['longitude']));
    $dob = htmlspecialchars(trim($_POST['dob']));
    // $dob = htmlspecialchars(trim($_POST['dob']));
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($dobDate)->y;

    if ($age < 18 || $age > 60) {
        header("Location: editDonor.php?error=Age must be between (18-60) years.");
        exit();
    }
    $weight = htmlspecialchars(trim($_POST['weight']));
    $gender = htmlspecialchars(trim($_POST['gender']));
    // $height = htmlspecialchars(trim($_POST['height']));
    $last_donation_date = htmlspecialchars(trim($_POST['last_donation_date']));

    // Update users table
    $sql = $con->prepare("
        UPDATE users 
        SET fullname = ?, phone = ?, address = ?, latitude = ?, longitude = ? 
        WHERE email = ?
    ");
    $sql->bind_param("ssssss", $fullname, $phone, $address, $latitude, $longitude, $donor_email);

    if ($sql->execute()) {
        // Update donor details as well
        $sql = $con->prepare("
            UPDATE donor 
            SET donor_blood_type = ?, dob = ?, weight = ?, gender = ?, last_donation_date = ?
            WHERE id = (SELECT id FROM users WHERE email = ?)
        ");
        $sql->bind_param("ssssss", $donor_blood_type, $dob, $weight, $gender, $last_donation_date, $donor_email);

        if ($sql->execute()) {
            header("Location: editDonor.php?success=Profile updated successfully!");
            exit();
        } else {
            header("Location: editDonor.php?error=Update failed");
            exit();
        }
    } else {
        header("Location: editDonor.php?error=Update failed");
        exit();
    }
}

// Prepare default image path
$default_image_path = '../upload/defaultimage.png'; // Adjust as necessary
if (!file_exists($default_image_path)) {
    // Fallback if default image does not exist
    $default_image_path = '../path/to/another/default/image.png'; // Provide an alternative if needed
}

// Use donor's profile image or default image if not set
$profile_image = !empty($donor['profile_image']) ? htmlspecialchars($donor['profile_image']) : $default_image_path;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <script src="../javascript/addressInput.js"></script>
</head>
<body>
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

            <!-- Display existing profile image -->
            <form action="editDonor.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4 text-center">
                    <img src="<?php echo $profile_image; ?>" alt="Profile Image" class="w-32 h-32 rounded-full mx-auto mb-4">
                </div>

                <div class="mb-4">
                    <label for="profile_image" class="block text-gray-700">Profile Image</label>
                    <input type="file" id="profile_image" name="profile_image" class="w-full p-2 border border-gray-300 rounded">
                </div>
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
                    <input id="location" type="text" name="address" value="<?php echo htmlspecialchars($donor['address']); ?>" class="w-full p-2 border border-gray-300 rounded">
                    <div id="suggestions" class="suggestions"></div>
                    <input type="hidden" id="userLat" name="latitude" value="<?php echo htmlspecialchars($donor['latitude']); ?>">
                    <input type="hidden" id="userLong" name="longitude" value="<?php echo htmlspecialchars($donor['longitude']); ?>">
                </div>
                <div class="mb-4">
                    <label for="donor_blood_type" class="block text-gray-700">Blood Type</label>
                    <select name="donor_blood_type" id="donorBloodgroup" required class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="" disabled>Select Blood Group</option>
                        <?php
                        $blood_groups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
                        foreach ($blood_groups as $group) {
                            $selected = ($group === $donor['donor_blood_type']) ? 'selected' : '';
                            echo "<option value='$group' $selected>$group</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="dob" class="block text-gray-700">Date of Birth</label>
                    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($donor['dob']); ?>" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="gender" class="block text-gray-700">Gender</label>
                    <select name="gender" id="gender" required class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="" disabled>Select Gender</option>
                        <option value="Male" <?php echo ($donor['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($donor['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="weight" class="block text-gray-700">Weight(kg)</label>
                    <input type="number" id="weight" name="weight" min="45" max="150" value="<?php echo htmlspecialchars($donor['weight']); ?>" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <!-- <div class="mb-4">
                    <label for="height" class="block text-gray-700">Height</label>
                    <input type="text" id="height" name="height" value="<?php echo htmlspecialchars($donor['height']); ?>" class="w-full p-2 border border-gray-300 rounded">
                </div> -->
                <div class="mb-4">
                    <label for="last_donation_date" class="block text-gray-700">Last Donation Date</label>
                    <input type="date" id="last_donation_date" name="last_donation_date" value="<?php echo htmlspecialchars($donor['last_donation_date']); ?>" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <div class="text-center">
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Update Profile</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<script>
        $(document).ready(function () {
            $('#location').on('input', function () {
                var address = $(this).val().trim();
                if (address.length > 0) {
                    var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address) + "&countrycodes=NP";

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function (data) {
                            $('#suggestions').empty();
                            if (data.length > 0) {
                                data.forEach(function (place) {
                                    $('#suggestions').append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
                                });
                            }
                        },
                        error: function (error) {
                            console.log('Error:', error);
                        }
                    });
                } else {
                    $('#suggestions').empty();
                }
            });

            $(document).on('click', '.suggestion', function () {
                var placeName = $(this).text();
                var lat = $(this).data('lat');
                var lon = $(this).data('lon');

                $('#location').val(placeName);
                $('#userLat').val(lat);
                $('#userLong').val(lon);
                $('#suggestions').empty();
            });

            $('#location').on('keypress', function (e) {
                if (e.which == 13) {
                    e.preventDefault();
                    var firstSuggestion = $('#suggestions .suggestion').first();
                    if (firstSuggestion.length > 0) {
                        var placeName = firstSuggestion.text();
                        var lat = firstSuggestion.data('lat');
                        var lon = firstSuggestion.data('lon');

                        $('#location').val(placeName);
                        $('#userLat').val(lat);
                        $('#userLong').val(lon);
                        $('#suggestions').empty();
                    }
                }
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#location').length) {
                    $('#suggestions').empty();
                }
            });
        });
    </script>

