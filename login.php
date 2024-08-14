<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
   
</head>

<body class="bg-gray-200">

    <?php @include 'header.php'; ?>

    <section id="login-cont" class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-2xl bg-white rounded-lg shadow-lg flex overflow-hidden">
           
            <!-- Form Section -->
            <div class="flex-1 p-8">
            <h2 class="text-3xl font-bold text-center text-red-500 mb-6">Login </h2>
                <form action="login_register.php" method="post" class="space-y-6">
                    <?php if (isset($_GET['error'])) : ?>
                        <?php
                        $errorMessage = $_GET['error'];
                        $errorClass = ($errorMessage === 'Registration successful! you can now login!!') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <div class="p-4 rounded-md text-center font-semibold <?php echo $errorClass; ?>">
                            <p><?php echo $errorMessage; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="email">Email</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="email" placeholder="Enter Email" name="email" required>
                        </div>
                    <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="email">Password</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="password" placeholder="Enter password" name="password" required>
                        </div>
                      

                    <div class="relative">
                    <label class="block text-gray-700 font-bold mb-2" for="email">User Type</label>
                        <select name="user_type" id="userType" required
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        <option value="" disabled selected>Select user Type</option>
                            <option value="User">User</option>
                            <option value="Donor">Donor</option>
                            <option value="BloodBank">BloodBank</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" name="login"
                        class="w-full py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300">Login
                        Now</button>

                    <div class="text-center">
                        Don't have an account? <a href="register.php" class="text-blue-500 hover:underline">Signup</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

</body>

</html>
