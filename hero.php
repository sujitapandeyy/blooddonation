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
        .custom-shadoww {
            filter: drop-shadow(-9px 9px 0px pink);
        }
    </style>
</head>

<body>
    <main class="relative flex bg-white">
        <div class="flex items-center justify-between mt-20 rounded-tl-lg w-full">
            <div class="w-full mt-14 ml-28">
                <div class="rounded-lg p-4 w-1/2">
                    <h2 class="text-xl font-semibold mb-4 text-center font-serif">Select your Blood Types</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <?php foreach ($bloodTypes as $bloodType): ?>
                            <form method="POST" action="bloodTypeResult.php" class="border-2 border-red-500 bg-white text-red-500 font-bold rounded hover:bg-red-500 hover:text-white transition duration-300">
                                <input type="hidden" name="blood_type" value="<?= htmlspecialchars($bloodType) ?>">
                                <button type="submit" class="w-full h-12 text-red-500 text-lg font-bold flex items-center hover:bg-red-500 hover:text-white justify-center">
                                    <?= htmlspecialchars($bloodType) ?>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Left Text Section -->
                <div class="w-full">
                    <h1 class="text-5xl font-bold text-gray-800 font-serif mt-10 mb-4">Donate blood, save life!&nbsp;<i class="fas fa-syringe text-3xl"></i></h1>
                    <h1 class="text-md font-semibold text-gray-800 font-serif mb-4">Be a lifesaver today. Donate blood at the Blood Bank, where every donation is a lifeline.</h1>
                    <a href="donorregister.php" class="inline-flex items-center mt-6 px-6 py-3 bg-white text-red-500 font-semibold rounded-full shadow hover:bg-red-200 transition ease-in-out duration-300 mb-5">Donate Now &nbsp;<span class="text-red-500 font-bold text-2xl">+</span></a>
                    <a href="searchresult.php" class="ml-2 mb-20 inline-flex items-center px-6 py-3 bg-red-500 text-white font-semibold rounded-full shadow hover:bg-red-300 transition ease-in-out duration-300 ">Search Blood <i class="fas fa-search ml-3 text-white font-bold text-xl"></i></a>
                </div>
            </div>

            <!-- Right Image Section -->
            <div class="w-full relative mt-10 ">
                <div class="custom-shadoww">
                    <img src="img/bgremove.png" alt="Donor Image" class="w-full h-auto object-cover">
                </div>
            </div>
        </div>
    </main>

    <section class="bg-red-400 relative text-white py-8 px-6 mr-6  rounded-r-full">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-2xl font-bold font-serif">"Support our cause - join the campaign and help save lives!"</div>
            <a href="#Available Campaigns
" class="flex font-serif items-center bg-white text-blue-500 font-semibold rounded-full py-2 px-4 hover:bg-blue-100">campaign <span class="ml-2 text-xl">➔</span></a>
        </div>
    </section>
</body>

</html>
