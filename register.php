<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="javascript/addressInput.js"></script>
    <style>
        .formerror {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<body class="bg-gray-200">
    <?php @include 'header.php'; ?>

    <section id="registration" class="flex items-center justify-center min-h-screen bg-gray-100 pt-32">
        <div class="w-full max-w-4xl p-8 bg-white shadow-lg rounded-lg">
            <div class="text-right mb-6">
                <button id="toggleFormBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Donor Registration
                </button>
            </div>

            <!-- User Registration Form -->
            <div id="userRegistrationForm" class="w-full">
                <form action="login_register.php" name="userRegistrationForm" method="post" class="space-y-6">
                    <h2 class="text-3xl font-bold text-center text-red-500 mb-6">User Register</h2>
                    <?php if (isset($_GET['error'])) { ?>
                        <p class="formerror text-red-500 text-center mb-4">*<?php echo $_GET['error']; ?></p>
                    <?php } ?>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-1">
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="fullname">Full Name</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="text" placeholder="Enter Full Name" name="fullname" id="userFullname" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="email">Email</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="email" placeholder="Enter Email" name="email" id="userEmail" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="password">Password</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="password" placeholder="Enter Password" name="password" id="userPassword" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="cpassword">Re-enter Password</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="password" placeholder="ReEnter Password" name="cpassword" id="userCpassword" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="phone">Phone Number</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="text" placeholder="Enter Phone Number" name="phone" id="userPhone" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">Campaign Location</label>
                            <input
                                id="location"
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="text" placeholder="Enter Address" name="address" required aria-label="Address">
                                <div id="suggestions" class="suggestions"></div>
                                <input type="hidden" id="userLat" name="latitude">
                                <input type="hidden" id="userLong" name="longitude">
                                <div>
                                    <p id="display-lat"></p>
                                    <p id="display-long"></p>
                                </div>
                        </div>
                        
                    <input type="hidden" name="user_type" value="User">
                    <button type="submit" id="RegButton" name="register"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg w-full text-lg hover:bg-red-600">Register Now</button>
                    <div id="Register_signup" class="text-center mt-4 text-lg">Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Login now</a></div>
                </form>
            </div>

            <!-- Donor Registration Form -->
            <div id="donorRegistrationForm" class="w-full hidden">
                <form action="login_register.php" name="donorRegistrationForm" method="post" class="space-y-6">
                    <h2 class="text-3xl font-bold text-center text-red-500 mb-6">Donor Register</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Section: Personal Information -->
                        <div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="fullname">Full Name</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="text" placeholder="Enter Full Name" name="fullname" id="donorFullname" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="email">Email</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="email" placeholder="Enter Email" name="email" id="donorEmail" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="password">Password</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="password" placeholder="Enter Password" name="password" id="donorPassword" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="phone">Phone Number</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="text" placeholder="Enter Phone Number" name="phone" id="donorPhone" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="dob">Date of Birth</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="date" name="dob" id="donorDob" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="weight">Weight (kg)</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="number" step="0.1" placeholder="Enter Weight in kg" name="weight" id="donorWeight" required>
                            </div>
                        </div>

                        <!-- Right Section: Health Information -->
                        <div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="gender">Gender</label>
                                <select
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    name="gender" id="donorGender" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="blood_group">Blood Group</label>
                                <select
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    name="blood_group" id="donorBloodGroup" required>
                                    <option value="" disabled selected>Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="height">Height (cm)</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="number" step="0.1" placeholder="Enter Height in cm" name="height" id="donorHeight" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="blood_pressure">Blood Pressure</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="text" placeholder="Enter Blood Pressure" name="blood_pressure" id="donorBloodPressure" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="last_donation_date">Last Donation Date (optional)</label>
                                <input
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="date" name="last_donation_date" id="donorLastDonationDate">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="address"> Location</label>
                                <input
                                    id="donorAddress"
                                    class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                    type="text" placeholder="Enter Address" name="address" required>
                                <div id="donorSuggestions" class="suggestions"></div>
                                <input type="hidden" id="donorLat" name="latitude">
                                <input type="hidden" id="donorLong" name="longitude">
                            </div>
                            <input id="location"
                                    class="w-full md:w-60 text-gray-800 px-4 py-2 mb-2 md:mb-0 md:mr-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    type="text" placeholder="Enter Address" name="address" required aria-label="Address">
                                <div id="suggestions" class="suggestions"></div>
                                <input type="hidden" id="userLat" name="latitude">
                                <input type="hidden" id="userLong" name="longitude">
                                <div>
                                    <p id="display-lat"></p>
                                    <p id="display-long"></p>
                                </div>
                        </div>
                    </div>
                    <input type="hidden" name="user_type" value="Donor">
                    <button type="submit" id="RegButton" name="register"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg w-full text-lg hover:bg-red-600">Register Now</button>
                    <div id="Register_signup" class="text-center mt-4 text-lg">Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Login now</a></div>
                </form>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const userForm = document.getElementById('userRegistrationForm');
            const donorForm = document.getElementById('donorRegistrationForm');
            const toggleFormBtn = document.getElementById('toggleFormBtn');

            toggleFormBtn.addEventListener('click', function () {
                if (userForm.classList.contains('hidden')) {
                    userForm.classList.remove('hidden');
                    donorForm.classList.add('hidden');
                    toggleFormBtn.textContent = 'Donor Registration';
                } else {
                    userForm.classList.add('hidden');
                    donorForm.classList.remove('hidden');
                    toggleFormBtn.textContent = 'User Registration';
                }
            });

          
   
        $(document).ready(function () {
            $('#location').on('input', function () {
                var address = $(this).val().trim();
                // Show suggestions for each word or space input
                if (address.length > 0) {
                    var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address) + "&countrycodes=NP";

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function (data) {
                            $('#suggestions').empty();
                            if (data.length > 0) {
                                data.forEach(function (place) {
                                    $('#suggestions').append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
                                });
                            }
                        },
                        error: function (error) {
                            console.log('Error:', error);
                        }
                    });
                } else {
                    $('#suggestions').empty();
                }
            });
        });

            $(document).on('click', '.suggestion', function () {
                var placeName = $(this).text();
                var lat = $(this).data('lat');
                var lon = $(this).data('lon');

                $('#location').val(placeName);
                $('#userLat').val(lat);
                $('#userLong').val(lon);
                $('#display-lat').text('Latitude: ' + lat);
                $('#display-long').text('Longitude: ' + lon);
                $('#suggestions').empty();
            });

            $('#location').on('keypress', function (e) {
                if (e.which == 13) { // Enter key pressed
                    e.preventDefault();
                    var firstSuggestion = $('#suggestions .suggestion').first();
                    if (firstSuggestion.length > 0) {
                        var placeName = firstSuggestion.text();
                        var lat = firstSuggestion.data('lat');
                        var lon = firstSuggestion.data('lon');

                        $('#location').val(placeName);
                        $('#userLat').val(lat);
                        $('#userLong').val(lon);
                        $('#display-lat').text('Latitude: ' + lat);
                        $('#display-long').text('Longitude: ' + lon);
                        $('#suggestions').empty();
                    }
                }
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#location').length) {
                    $('#suggestions').empty();
                }
            });
        });

    </script>
</body>

</html>
