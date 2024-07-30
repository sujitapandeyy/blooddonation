<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <link rel="icon" href="favIcon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Index page</title>
    <style>
        .overlay {
            position: relative;
            z-index: 1;
            color: whitesmoke;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            width: 100%;
        }

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

        .bg-main {
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent background to enhance text readability */
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php @include 'header.php'; ?>
    <section class=" relative flex pt-32 ">
        <div class="bg-container">
            <iframe src="abc.php" class="bg-iframe"></iframe>
        </div>
        <div class="container mx-auto px-4 content">
            <div class="rounded-lg w-full h-96 flex items-center justify-center">
                <div class="text-white text-center p-8 bg-black bg-opacity-50 rounded-lg">
                    <h1 class="text-3xl font-bold mb-7">We are here to save life</h1>
                    <h2 class="text-xl font-bold mb-4">Search Nearby Donor</h2>
                    <div class="flex flex-col md:flex-row items-center justify-center">
                        <input type="text" placeholder="Blood Type" class="w-full md:w-60 text-gray-800  px-4 py-2 mb-2 md:mb-0 md:mr-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="text" placeholder="Address" class="w-full md:w-60 px-4 text-gray-800 py-2 mb-2 md:mb-0 md:mr-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button class="w-full md:w-30 px-4 py-2 text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-blue-600 flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php @include 'bloodtype.php'; ?>
    <?php @include 'bloodbanks.php'; ?>
    <?php @include 'footor.php'; ?>


</body>

</html>
