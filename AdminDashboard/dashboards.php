<?php
include '../connection.php';

// Handle delete operation
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE id=$id"; 
    if ($con->query($sql) === TRUE) {
        header("Location: AdminDashboard.php");
        exit();
    } else {
        echo "Error deleting user: " . $con->error;
    }
}

// Fetch all users
$sql = "SELECT * FROM users"; 
$result = $con->query($sql);

// Fetch count of users grouped by user_type
$count_sql = "SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type"; 
$count_result = $con->query($count_sql);

$user_counts = [];
if ($count_result->num_rows > 0) {
    while ($row = $count_result->fetch_assoc()) {
        $user_counts[$row['user_type']] = $row['count'];
    }
}

// Define colors and icons for each user type
$user_type_info = [
    'Admin' => ['color' => 'bg-red-500', 'icon' => 'fa-user-shield'],
    'Donor' => ['color' => 'bg-green-500', 'icon' => 'fa-user-tie'],
    'Blood Bank' => ['color' => 'bg-blue-500', 'icon' => 'fa-hospital']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-7xl mx-auto p-6">

         <!-- Display User Counts by Type  -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <?php foreach ($user_counts as $type => $count): 
                $color = isset($user_type_info[$type]) ? $user_type_info[$type]['color'] : 'bg-gray-500';
                $icon = isset($user_type_info[$type]) ? $user_type_info[$type]['icon'] : 'fa-users';
            ?>
                <div class="<?php echo $color; ?> p-6 rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center">
                        <i class="fas <?php echo $icon; ?> text-white text-4xl mr-4"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-white uppercase"><?php echo $type; ?></h3>
                            <p class="text-4xl font-bold text-white mt-2"><?php echo $count; ?></p>
                            <p class="text-sm text-white opacity-75">Users of type <?php echo $type; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- User Management Table -->
      
    </div>
</body>
</html>
