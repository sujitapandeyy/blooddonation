<?php
require('connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['blood_type'])) {
    $selectedBloodType = $_POST['blood_type'];
    $stmt = $con->prepare("SELECT fullname, email, phone, address FROM users WHERE donor_blood_type = ?");
    $stmt->bind_param("s", $selectedBloodType);
    $stmt->execute();
    $result = $stmt->get_result();
    $donors = [];
    while ($row = $result->fetch_assoc()) {
        $donors[] = $row;
    }
} else {
    header('Location: index.php'); // Redirect to the main page if accessed directly
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donors List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php
    @include("header.php")?>
    <section class="container max-w-7xl mx-auto pt-32">
        <h2 class="text-4xl font-extrabold text-center mb-12 text-red-600">Donors with blood type <?= htmlspecialchars($selectedBloodType) ?></h2>
        <div id="donor-list" class="mt-12">
            <?php if (count($donors) > 0): ?>
                <?php foreach ($donors as $donor): ?>
                    <div class="bg-white shadow-md rounded-lg p-4 mb-4">
                        <p><strong>Name:</strong> <?= htmlspecialchars($donor['fullname']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($donor['email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($donor['phone']) ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($donor['address']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No donors found for this blood type.</p>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>
