<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login And Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="javascript\addressInput.js"></script>
    <style>
        .bg-secmain {
            background-image: url('./img/login1.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
        /* .bg-secmain::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            filter: blur(3px);
            z-index: 0;
        } */

        .formerror {
            color: red;
            text-align: center;
            margin-bottom: -10px;
            margin-top: 5px;
            font-size: 0.875rem;
            /* Smaller font size for error messages */
        }

        .input-field {
            font-size: 1rem;
            /* Smaller font size for input fields */
        }

        /* .text-overlay {
            position: relative;
            z-index: 1;
            color: whitesmoke;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            width: 100%;
        } */

        .suggestions {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            position: absolute;
            z-index: 1000;
            background: #fff;
            width: 200px;
        }
        .suggestion {
            padding: 10px;
            cursor: pointer;
        }
        .suggestion:hover {
            background: #f0f0f0;
        }
    </style>
    <script>
        function showUserRegistration() {
            document.getElementById('userRegistrationForm').style.display = 'block';
            document.getElementById('donorRegistrationForm').style.display = 'none';
        }

        function showDonorRegistration() {
            document.getElementById('donorRegistrationForm').style.display = 'block';
            document.getElementById('userRegistrationForm').style.display = 'none';
        }

        function setDefaultForm() {
            document.getElementById('userRegistrationForm').style.display = 'block';
            document.getElementById('donorRegistrationForm').style.display = 'none';
        }

        window.onload = setDefaultForm;
    </script>
</head>

<body class="bg-gray-200">
    <?php @include 'header.php'; ?>

    <section id="popup" class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="flex mt-20  w-full max-w-6xl bg-gray-100 shadow-lg overflow-hidden">
        <div class="flex-1 bg-secmain bg-cover bg-center flex relative">
                <div class="absolute inset-0 z-0"></div>
              
            </div>
            <div class="w-1/2 p-6">
                <div class="flex item-center justify-center mb-2">
                    <button class="bg-indigo-600 text-white ml-1 px-4 py-2 rounded-lg w-1/3 text-sm"
                        onclick="showUserRegistration()">User</button>
                    <button class="bg-indigo-600 text-white ml-1 px-4 py-2 rounded-lg w-1/3 text-sm"
                        onclick="showDonorRegistration()">Donor</button>
                  
                </div>
                <div class="flex-1 bg-gray-100 p-6 flex flex-col items-center justify-center">
                    <div id="userRegistrationForm" class="w-full h-full">
                        <form action="login_register.php" name="userRegistrationForm" onsubmit="return validateForm()"
                            method="post" class="">
                            <h2 class="text-3xl font-bold text-center text-red-500 mb-6">User Register</h2>
                            <?php if (isset($_GET['error'])) { ?>
                                <p class="formerror">*<?php echo $_GET['error']; ?></p>
                            <?php } ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-user  absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="text" placeholder="Enter Full Name" name="fullname" id="userFullname"
                                        required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-envelope absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="email" placeholder="Enter Email" name="email" id="userEmail" required
                                    class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                    </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-lock absolute left-0 top-1/2 transform -translate-y-1/2 pl-4  text-xl"></i>
                                    <input type="password" placeholder="Enter Password" name="password"
                                        id="userPassword" required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-lock absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="password" placeholder="ReEnter Password" name="cpassword"
                                        id="userCpassword" required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-phone absolute left-0 top-1/2 transform -translate-y-1/2 pl-4  text-xl"></i>
                                    <input type="text" placeholder="Enter Phone Number" name="phone" id="userPhone"
                                        required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-home absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="text" placeholder="Enter Address" name="address" id="userAddress"
                                        required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
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
                                class="bg-red-500 text-white px-4 my-5 py-2 rounded-lg w-full text-lg hover:bg-red-600">Register
                                Now</button>
                            <div id="Register_signup" class="text-center mt-4 text-lg">Already have an account? <a
                                    href="login.php" class="text-blue-500 hover:underline">Login now</a></div>
                        </form>
                    </div>
               

                    <div id="donorRegistrationForm" style="display: none;" class="w-full">
                        <form action="login_register.php" name="donorRegistrationForm" onsubmit="return validateForm()"
                            method="post" class="space-y-6">
                            <h2 class="text-3xl font-bold text-center mb-6 text-red-500">Donor Register</h2>
                            <?php if (isset($_GET['error'])) { ?>
                                <p class="formerror">*<?php echo $_GET['error']; ?></p>
                            <?php } ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-user absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="text" placeholder="Enter Full Name" name="fullname" id="donorFullname"
                                        required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-envelope absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="email" placeholder="Enter Email" name="email" id="donorEmail" required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-lock absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="password" placeholder="Enter Password" name="password"
                                        id="donorPassword" required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-lock absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="password" placeholder="ReEnter Password" name="cpassword"
                                        id="donorCpassword" required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-phone absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="text" placeholder="Enter Phone Number" name="phone" id="donorPhone"
                                        required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-home absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="text" placeholder="Enter Address" name="address" id="donorAddress"
                                        required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <!-- <div class="relative">
                                    <i
                                        class="fa-solid fa-calendar-day absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <input type="date" placeholder="Enter Date of Birth" name="dob" id="donorDob"
                                        required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                </div>
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-venus-mars absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <select name="gender" id="donorGender" required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                                        <option value="" disabled selected>Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div> -->
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-tint absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                                    <select name="bloodgroup" id="donorBloodgroup" required
                                        class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
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
                            </div>
                            <input type="hidden" name="user_type" value="Donor">
                            <button type="submit" id="RegButton" name="register"
                                class="bg-red-500 text-white px-4 py-2 rounded-lg my-5 w-full text-lg hover:bg-red-600">Register
                                Now</button>
                            <div id="Register_signup" class="text-center mt-4 text-lg">Already have an account? <a
                                    href="login.php" class="text-blue-500 hover:underline">Login now</a></div>
                        </form>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function () {
            initializeAddressInput('userAddress', 'userSuggestions', 'userLat', 'userLong', 'displayUserLat', 'displayUserLong');
        });
    </script>
</body>

</html>
