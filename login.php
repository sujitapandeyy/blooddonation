<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login And Registration</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
        }

        .text-overlay {
            position: relative;
            z-index: 1;
            color: whitesmoke;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            width: 100%;
        } */
    </style>
</head>

<body class="bg-gray-200">

    <?php @include 'header.php'; ?>

    <section id="login-cont" class="flex items-center justify-center min-h-screen bg-gray-100 rounded">
        <div class="bg-main flex w-full max-w-5xl h-full bg-white rounded shadow-lg">
            <!-- Image Section -->
            <div class="flex-1 bg-secmain bg-cover bg-center flex relative">
                <!-- <div class="absolute inset-0 bg-black opacity-50 z-0"></div> -->
                <!-- <div class="absolute inset-0 bg-black opacity-50 z-0"></div> -->
                <!-- <div class="text-overlay "> -->
                    <!-- Welcome to Raktasewa -->
                <!-- </div> -->
            </div>
            <!-- Form Section -->
            <div class="flex-1 p-12 bg-gray-100 flex flex-col items-center justify-center">
                <form action="login_register.php" method="post" class="flex flex-col items-center w-full max-w-lg">
                    <h2 class="text-3xl font-bold text-red-600 mb-6">Login</h2>

                    <?php if (isset($_GET['error'])) : ?>
                        <?php
                        $errorMessage = $_GET['error'];
                        $errorClass = ($errorMessage === 'Registration successful! you can now login!!') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <div class="w-full mb-6 p-4 rounded-md text-center font-semibold <?php echo $errorClass; ?>">
                            <p><?php echo $errorMessage; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="relative w-full mb-6">
                        <i class="fa-solid fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 text-xl"></i>
                        <input type="text" placeholder="Enter E-mail" name="email" required
                            class="pl-12 py-1 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none transition duration-200 ease-in-out text-lg">
                    </div>

                    <div class="relative w-full mb-6">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 text-xl"></i>
                        <input type="password" placeholder="Enter Password" name="password" required
                            class="pl-12 py-1 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none transition duration-200 ease-in-out text-lg">
                    </div>

                    <div class="relative w-full mb-6">
                        <i class="fa-solid fa-tint absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 text-xl"></i>
                        <select name="user_type" id="userType" required
                            class="pl-12 py-1 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none transition duration-200 ease-in-out text-lg">
                            <option value="" disabled selected>Select user Type</option>
                            <option value="User">User</option>
                            <option value="Donor">Donor</option>
                            <option value="BloodBank">BloodBank</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" name="login" class="py-2 px-6 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300 ease-in-out w-full text-xl">Login Now</button>

                    <div class="mt-6 text-center">
                        Don't have an account? <a href="register.php" class="text-blue-500 hover:text-blue-700 hover:underline text-lg">Signup</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Font Awesome CDN -->
</body>

</html>
