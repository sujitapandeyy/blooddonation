<?php
include '../connection.php';



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

$sql = "SELECT * FROM users"; 
$result = $con->query($sql);

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
        <div class="bg-indigo-600 text-white p-4 rounded-t-lg shadow-md flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">User Management</h2>
            <div class="flex items-center bg-white p-2 rounded-lg shadow-md">
                <i class="fas fa-search text-gray-400 mr-2"></i>
                <input type="text" placeholder="Search users..." class="border-none outline-none p-2 text-gray-800">
            </div>
        </div>
        <table class="w-full border-collapse bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="py-3 px-4 text-left uppercase text-sm font-semibold">ID</th>
                    <th class="py-3 px-4 text-left uppercase text-sm font-semibold">Full Name</th>
                    <th class="py-3 px-4 text-left uppercase text-sm font-semibold">Email</th>
                    <th class="py-3 px-4 text-left uppercase text-sm font-semibold">Phone</th>
                    <th class="py-3 px-4 text-left uppercase text-sm font-semibold">User Type</th>
                    <th class="py-3 px-4 text-left uppercase text-sm font-semibold">Address</th>
                    <th class="py-3 px-4 text-left uppercase text-sm font-semibold">Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='hover:bg-gray-200'>
                                <td class='py-3 px-4'>" . $row["id"] . "</td>
                                <td class='py-3 px-4'>" . $row["fullname"] . "</td>
                                <td class='py-3 px-4'>" . $row["email"] . "</td>
                                <td class='py-3 px-4'>" . $row["phone"] . "</td>
                                <td class='py-3 px-4'>" . $row["user_type"] . "</td>
                                <td class='py-3 px-4'>" . $row["address"] . "</td>
                                <td class='py-3 px-4'><a href='manageUser.php?delete=" . $row["id"] . "' class='bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600'>Delete</a></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='py-3 px-4 text-center'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
