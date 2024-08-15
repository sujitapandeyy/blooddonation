<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous" defer></script>
    <link rel="icon" href="favIcon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="javascript/addressInput.js"></script>
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
        /* Styles for address suggestions */
        .suggestions {
            position: absolute;
            top: 100%; /* Aligns the suggestions container below the input */
            left: 0;
            width: 100%;
            z-index: 10; /* Ensures suggestions appear above other content */
            background: gray;
            border: 1px solid #ddd;
            border-radius: 0 0 0.375rem 0.375rem; /* Tailwind border-radius class */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            max-height: 200px; /* Optional: limits height for scrolling */
            overflow-y: auto; /* Optional: enables scrolling if content exceeds max height */
        }
        .suggestions div {
            padding: 8px;
            cursor: pointer;
        }
        .suggestions div:hover {
            background-color: ##808080;
        }
    </style>
</head>
<body>
    <main class="relative flex pt-32 bg-gray-100">
        <div class="bg-container">
            <iframe src="abc.php" class="bg-iframe"></iframe>
        </div>
        <div class="container mx-auto px-4 content">
            <div class="rounded-lg w-full custom-carousel-height flex items-center justify-center">
                <div class="text-white text-center p-4 mb-28 bg-black bg-opacity-50 rounded-lg">
                    <h1 class="text-3xl font-bold mb-7">We are here to save life</h1>
                    <h2 class="text-xl font-bold mb-4">Search Nearby Donor</h2>
                    <form action="searchresult.php" method="POST">
                        <div class="flex flex-col md:flex-row items-center justify-center relative">
                            <select name="bloodgroup" id="donorBloodgroup" required
                                class="w-full md:w-60 text-gray-800 px-4 py-2 mb-2 md:mb-0 md:mr-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Blood Type">
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
                            
                            <div class="relative">
                                <input
                                    id="userAddress"
                                    class="w-full md:w-60 text-gray-800 px-4 py-2 mb-2 md:mb-0 md:mr-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    type="text" placeholder="Enter Address" name="address" required>
                                <div id="userSuggestions" class="suggestions"></div>
                                <input type="hidden" id="userLat" name="latitude">
                                <input type="hidden" id="userLong" name="longitude">
                            </div>
                            <button type="submit" class="w-full md:w-30 px-4 py-2 text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-blue-600 flex items-center justify-center">
                                <i class="fas fa-search mr-2"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
   
    <script>
    // Initialize address input
    initializeAddressInput('userAddress', 'userSuggestions', 'userLat', 'userLong', 'displayUserLat', 'displayUserLong');
    </script>
</body>
</html>
