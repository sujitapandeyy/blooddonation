<?php
require ('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['bankemail'])) {
    header("Location: login.php?error=Login first");
    exit(); // Ensure script execution stops after redirection
}

// Get the blood bank ID from the session
$bankemail = $_SESSION['bankemail'];
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $bankemail);
$stmt->execute();
$stmt->bind_result($bloodbank_id);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $weight = $_POST['weight'];
    $bloodgroup = $_POST['bloodgroup'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $bloodqty = $_POST['bloodqty'];
    $collection = $_POST['collection'];

    // Calculate expiry date (42 days after collection date)
    $collectionDate = new DateTime($collection);
    $collectionDate->add(new DateInterval('P42D'));
    $expire = $collectionDate->format('Y-m-d');

    $sql = "INSERT INTO blood_details (name, gender, dob, weight, bloodgroup, address, contact, bloodqty, collection,expire, bloodbank_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssssssissi", $name, $gender, $dob, $weight, $bloodgroup, $address, $contact, $bloodqty, $collection, $expire, $bloodbank_id);

    if ($stmt->execute()) {
        header("Location: addBlood.php?error=New record created successfully!!");
        echo "New record created successfully!!";
    } else {
        // echo "Error: " . $sql . "<br>" . $con->error;
        header("Location: addBlood.php?error= failed try again");

    }

    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Add Blood Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-200">
    <?php @include ("bloodbankmenu.php"); ?>
    <section class="ml-72 p-8 max-w-4xl">

        <div class="bg-white p-8 rounded-lg shadow-lg">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header text-3xl font-bold text-center justify-center mb-4">Add Blood
                        Details</h1>
                </div>
            </div>
            <!-- <?php if (isset($_GET['error'])) { ?>
                <p class="bg-red-500 mb-4 text-center rounded">*<?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php } ?> -->
            <?php if (isset($_GET['error'])) : ?>
                        <?php
                        $errorMessage = $_GET['error'];
                        $errorClass = ($errorMessage === 'New record created successfully!!') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <div class="w-full mb-6 p-4 rounded-md text-center font-semibold <?php echo $errorClass; ?>">
                            <p><?php echo $errorMessage; ?></p>
                        </div>
                    <?php endif; ?>
            <form role="form" action="" method="post">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="name">Enter Donor
                        Name</label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="text" placeholder="Donor name" name="name" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="gender">Gender </label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="text" placeholder="Male/Female/other" name="gender" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="dob">Enter Date of
                        birth</label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="date" name="dob" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="weight">Enter Weight</label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="number" name="weight" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="bloodgroup">Select Blood
                        Group</label>
                    <select name="bloodgroup" id="donorBloodgroup" required
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="" disabled selected>Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="bloodqty">Blood
                        Quantity</label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="number" name="bloodqty" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="address">Enter
                        Address</label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="text" name="address" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="contact">Enter Contact
                        Number</label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="number" name="contact" required>
                </div>



                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="collection">Collection
                        Date</label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="date" name="collection" required>
                </div>
                <div class="">
                    <input type="hidden" name="expire" required>
                </div>

                <div class="flex items-center justify-center ">
                    <button type="submit"
                        class="px-20 rounded-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 focus:outline-none focus:shadow-outline">Submit</button>
                </div>
            </form>
        </div>
    </section>
</body>

</html>