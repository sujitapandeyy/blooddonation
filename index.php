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
        .bg-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .bg-iframe {
            width: 100%;
            height: 100%;
            border: none;
            position: absolute;
            top: 0;
            left: 0;
        }

        .content {
            position: relative;
            z-index: 2;
        }

        .custom-carousel-height {
            height: 500px;
        }
    </style>
</head>

<body class="">
    <?php @include 'header.php'; ?>
    <main class="relative flex pt-32 bg-gray-100">
        <div class="bg-container">
            <iframe src="abc.php" class="bg-iframe"></iframe>
        </div>
        <div class="container mx-auto px-4 content">
            <div class="rounded-lg w-full custom-carousel-height flex items-center justify-center">
                <div class="text-white text-center p-4 mb-28 bg-black bg-opacity-50 rounded-lg">
                    <h1 class="text-3xl font-bold mb-7">We are here to save life</h1>
                    <h2 class="text-xl font-bold mb-4">Search Nearby Donor</h2>
                    <div class="flex flex-col md:flex-row items-center justify-center">
                        <input type="text" placeholder="Blood Type" class="w-full md:w-60 text-gray-800 px-4 py-2 mb-2 md:mb-0 md:mr-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Blood Type">
                        <input type="text" placeholder="Address" class="w-full md:w-60 px-4 text-gray-800 py-2 mb-2 md:mb-0 md:mr-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Address">
                        <button class="w-full md:w-30 px-4 py-2 text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-blue-600 flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php @include 'bloodtype.php'; ?>
    <?php @include 'bloodbanks.php'; ?>
    <?php @include 'footor.php'; ?>
    <script src="javascript/geolocation.js" defer></script>

</body>

</html>
