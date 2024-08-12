<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous" defer></script>
    <link rel="icon" href="favIcon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Index Page</title>

    <style>
        .campaign-container {
            height: 10vh; /* 10% of the viewport height */
            /* background-image: url('./img/ll.png'); */
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="bg-gray-100">

    <main class="flex flex-col items-center justify-center ">
        <!-- View Campaign Section -->
        <div class="campaign-container w-full flex items-center justify-between px-8 bg-blue-600 bg-opacity-75 shadow-md rounded-md">
            <h2 class="text-white text-2xl font-bold">View Campaign</h2>
            <a href="all_campaign.php" class="bg-blue-500 hover:bg-blue-600 transition-colors duration-300 text-white py-2 px-4 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                View All Campaigns
            </a>
        </div>

        <!-- Other content here -->
    </main>



    <!-- <script src="geolocation.js" defer></script> -->
</body>

</html>
