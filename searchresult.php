<?php
require('connection.php');

// Get POST data
$bloodGroup = $_POST['bloodgroup'];
$userLat = $_POST['latitude'];
$userLong = $_POST['longitude'];

// Haversine formula to calculate distance
$sql = "
    SELECT id, fullname AS name, email, phone,gender, address, latitude, longitude,
        ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance
    FROM users
    WHERE user_type = 'Donor' AND donor_blood_type = ?
    HAVING distance < 50 -- Specify max distance in km
    ORDER BY distance
    LIMIT 30
";

$stmt = $con->prepare($sql);
$stmt->bind_param("ddds", $userLat, $userLong, $userLat, $bloodGroup);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nearby Donors</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php @include("header.php"); ?>
    <?php @include("hero.php"); ?>
    <header class="bg-red-600 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold text-center">Nearby Donors of <?php echo htmlspecialchars($bloodGroup); ?></h1>
        </div>
    </header>
    <main class="container mx-auto px-4 py-6">
        <!-- <h2 class="text-2xl font-bold mb-6">Top 30 Nearby Donors</h2> -->
        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6 p-5">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white shadow-md rounded-lg p-4 mb-4 border border-gray-200">
                        <h3 class="text-xl font-bold p-1 text-gray-800"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p class="text-gray-600 p-1 font-semibold">Address: <span class="text-gray-600 font-normal "><?php echo htmlspecialchars($row['address']); ?></span></p>
                        <p class="text-gray-600 p-1 font-semibold">Email: <span class="text-gray-600 font-normal"><?php echo htmlspecialchars($row['email']); ?></span></p>
                        <p class="text-gray-600 p-1 font-semibold">Phone: <span class="text-gray-600 font-normal"><?php echo htmlspecialchars($row['phone']); ?></span></p>
                        <p class="text-gray-600 p-1 font-semibold">Gender: <span class="text-gray-600 font-normal"><?php echo htmlspecialchars($row['gender']); ?></span></p>
                        <p class="text-gray-600 p-1 font-semibold">
                            Distance: <span class="text-blue-600 "><?php echo round($row['distance'], 2); ?> km</span>
                        </p> 
                        <?php
if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] == true) {
    echo '
    <form action="request_donation.php" method="POST">
        <input type="hidden" name="donor_id" value="' . htmlspecialchars($row["id"]) . '">
        <button type="submit" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Request Blood
        </button>
    </form>
    ';
}
?>
                   </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-700">No donors found within the specified distance and bloodtype.</p>
        <?php endif; ?>
    </main>
    <?php @include("footor.php"); ?>

    <?php
    $stmt->close();
    $con->close();
    ?>
</body>
</html>
