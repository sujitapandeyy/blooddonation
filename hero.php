<?php
require('connection.php');
session_start();

$bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$donorCounts = [];

// Prepare the SQL statement
$stmt = $con->prepare("
    SELECT donor_blood_type, COUNT(*) as count
    FROM donor
    GROUP BY donor_blood_type
");

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Fetch donor counts
while ($row = $result->fetch_assoc()) {
    $donorCounts[$row['donor_blood_type']] = $row['count'];
}

// Check if the user is logged in as a donor
$isDonor = false;
if (isset($_SESSION['user_id'])) {
    // Fetch user details from the database
    $user_id = $_SESSION['user_id'];
    $stmt = $con->prepare("SELECT * FROM donor WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $donorResult = $stmt->get_result();

    if ($donorResult->num_rows > 0) {
        $isDonor = true; // User is a donor
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous" defer></script>
    <link rel="icon" href="favIcon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>Index Page</title>
    <style>
        /* .custom-shadoww {
            filter: drop-shadow(-9px 9px 0px pink);
        } */
        .bg-imgg {
            background-image: url('img/heroimg.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        .secimg {
            background-image: url('img/secondhero.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
    </style>
</head>

<body>
    <main class="relative flex bg-imgg ">
        <div class="flex items-center justify-between rounded-tl-lg w-full  h-screen">

            <!-- Left Text Section -->
            <div class="w-full mt-14 ml-28">

                <!-- Text and Buttons -->
                <div class="w-full mt-24">
                    <h1 class="text-7xl text-black font-serif mb-4 font-thin">Donate blood,<br /> save life!&nbsp;<i
                            class="fas fa-hand-holding-heart text-red-500 text-3xl "></i>
                    </h1>
                    <h1 class="text-md font-semibold text-gray-800 font-serif  mt-10">Be a lifesaver today. Donate blood
                        at the Blood Bank,

                        where every donation is a lifeline. Be a hero.</h1>
                    <div class="mt-20">
                        <?php if (isset($_SESSION['Dloggedin']) && $_SESSION['Dloggedin'] === true): ?>
                            <a href="Donordashboard/donaterequest.php"
                                class="inline-flex items-center mt-6 px-6 py-3 bg-white text-red-500 font-semibold rounded-full shadow hover:bg-red-200 transition ease-in-out duration-300 mb-5">Donate
                                Now<span class="ml-2 text-xl">➔</span></a>
                        <?php else: ?>
                            <a href="donorregister.php"
                                class="inline-flex  items-center mt-6 px-6 py-3 bg-white text-red-500 font-semibold rounded-full shadow hover:font-extrabold hover:bg-red-100 transition ease-in-out duration-300 mb-5">Donate
                                Now &nbsp;<span class="text-red-500 hover:text-white font-bold text-2xl">+</span></a>
                        <?php endif; ?>

                        <a href="searchresult.php"
                            class="ml-2 mb-20 inline-flex items-center px-6 py-3 bg-red-500 text-white font-semibold rounded-full shadow hover:bg-red-300  transition ease-in-out duration-300 ">Search
                            Blood <i class="fas fa-search ml-3 text-white font-bold text-xl"></i></a>
                    </div>
                </div>
            </div>

            <!-- Right Slanted GIF Section -->
            <div class="w-full relative mt-10">
                <div class="custom-shadoww">
                    <img src="img/heroo.png" alt="Donor Image" class="w-4/5 h-full ">
                </div>
            </div>
        </div>
    </main>
    <div class=" p-4 w-full secimg">
        <h2 class="text-2xl font-thin mb-8 mt-5 text-center font-serif text-white">Select your Blood Types</h2>
        <div class="grid grid-cols-2 sm:grid-cols-8 gap-8 mx-60">
            <?php foreach ($bloodTypes as $bloodType): ?>
                <form method="POST" action="bloodTypeResult.php"
                    class="border-2 border-red-500 bg-white font-serif text-red-500 font-thin rounded transition duration-300 ">
                    <input type="hidden" name="blood_type" value="<?= htmlspecialchars($bloodType) ?>">
                    <button type="submit"
                        class="w-full h-12 text-red-500 text-lg font-bold flex items-center hover:bg-red-500 hover:text-white justify-center">
                        <?= htmlspecialchars($bloodType) ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

</body>

</html>