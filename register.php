<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
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
            <label class="block text-gray-700 font-bold mb-2" for="address">Address</label>
            <input
                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                type="text" placeholder="Enter Address" name="address" id="userAddress" required>
            <div id="userSuggestions" class="suggestions"></div>
            <input type="hidden" id="userLat" name="latitude">
            <input type="hidden" id="userLong" name="longitude">
            <div>
                <p id="displayUserLat"></p>
                <p id="displayUserLong"></p>
            </div>
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
                            <label class="block text-gray-700 font-bold mb-2" for="phone">Password</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="password" placeholder="Enter Passwordr" name="password" id="donorpassword" required>
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
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="bloodgroup">Blood Group</label>
                            <select
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                name="bloodgroup" id="donorBloodgroup" required>
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
                            <label class="block text-gray-700 font-bold mb-2" for="bloodPressure">Confirm Password</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="password" placeholder="ReEnter Password" name="Cpassword"
                                id="donorCpassword" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="height">Height (in cm)</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="text" placeholder="Enter Height" name="height" id="donorHeight" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="height">Weight (in kg)</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="text" placeholder="Enter weight" name="weight" id="donorWeight" required>
                        </div>

                      
                    </div>
                </div>

                <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2" for="address">Address</label>
            <input
                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                type="text" placeholder="Enter Address" name="address" id="userAddress" required>
            <div id="userSuggestions" class="suggestions"></div>
            <input type="hidden" id="userLat" name="latitude">
            <input type="hidden" id="userLong" name="longitude">
            <div>
                <p id="displayUserLat"></p>
                <p id="displayUserLong"></p>
            </div>
        </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="lastDonationDate">Last Donation Date (Optional)</label>
                    <input
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="date" name="lastDonationDate" id="lastDonationDate">
                </div>

                <input type="hidden" name="user_type" value="Donor">
                <button type="submit" id="RegButton" name="register"
                    class="bg-red-500 text-white px-4 py-2 rounded-lg w-full text-lg hover:bg-red-600">Register
                    Now</button>

                <div id="Register_signup" class="text-center mt-4 text-lg">
                    Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Login now</a>
                </div>
            </form>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('toggleFormBtn').addEventListener('click', function () {
            var userForm = document.getElementById('userRegistrationForm');
            var donorForm = document.getElementById('donorRegistrationForm');
            var toggleButton = document.getElementById('toggleFormBtn');
            
            if (userForm.classList.contains('hidden')) {
                userForm.classList.remove('hidden');
                donorForm.classList.add('hidden');
                toggleButton.textContent = 'Donor Registration';
            } else {
                userForm.classList.add('hidden');
                donorForm.classList.remove('hidden');
                toggleButton.textContent = 'User Registration';
            }
        });

        $(document).ready(function () {
            initializeAddressInput('userAddress', 'userSuggestions', 'userLat', 'userLong', 'displayUserLat', 'displayUserLong');
        });
    </script>
    </script>
</body>

</html>

