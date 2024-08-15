<?php
require('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['Adminemail'])) {
    header("Location: login.php?error=Login first");
    exit(); // Ensure script execution stops after redirection
}

// Handle BloodBank registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['cpassword']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $user_type = htmlspecialchars(trim($_POST['user_type']));

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($address) || empty($user_type)) {
        header("Location: bankregister.php?error=Please fill all the fields!!");
        exit();
    } elseif (!preg_match('/^[a-zA-Z]+(?: [a-zA-Z]+)*$/', $fullname)) {
        header("Location: bankregister.php?error=Name must contain only letters");
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: bankregister.php?error=Invalid email format!!");
        exit();
    } elseif ($password != $confirm_password) {
        header("Location: bankregister.php?error=Password and confirm password do not match!!");
        exit();
    } else {
        // Check if the email already exists for the BloodBank user type
        $user_exist_query = $con->prepare("SELECT * FROM users WHERE email = ? AND user_type = ?");
        $user_exist_query->bind_param("ss", $email, $user_type);
        $user_exist_query->execute();
        $result = $user_exist_query->get_result();

        if ($result && $result->num_rows > 0) {
            header("Location: bankregister.php?error=Email already exists for this user type");
            exit();
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = $con->prepare("INSERT INTO users (fullname, email, password, phone, address, user_type) VALUES (?, ?, ?, ?, ?, ?)");
            $sql->bind_param("ssssss", $fullname, $email, $hashed_password, $phone, $address, $user_type);

            if ($sql->execute()) {
                header("Location: admindashboard.php?error=Registration successful! bloodBank can login now!!");
            } else {
                header("Location: bankregister.php?error=Registration failed");
            }
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BloodBank Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gray-100">
    <section id="popup" class="flex justify-center h-auto bg-gray-100">
        <div class="flex w-full max-w-xl bg-white shadow-lg rounded-lg overflow-hidden">
            <div id="bloodBankRegistrationForm" class="w-full p-6">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" name="bloodBankRegistrationForm" method="post">
                    <h2 class="text-3xl font-bold text-center mb-6 text-red-500">BloodBank Register</h2>
                    <?php if (isset($_GET['error'])) { ?>
                        <p class="text-red-500 mb-4 text-center">*<?php echo htmlspecialchars($_GET['error']); ?></p>
                    <?php } ?>
                    <div class="space-y-4">
                        <div class="relative">
                            <i class="fa-solid fa-hospital absolute left-3 top-1/2 transform -translate-y-1/2 text-xl text-gray-600"></i>
                            <input type="text" placeholder="Enter BloodBank Name" name="fullname" id="bankname" required
                                class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none text-lg">
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-xl text-gray-600"></i>
                            <input type="email" placeholder="Enter Email" name="email" id="bloodBankEmail" required
                                class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none text-lg">
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-xl text-gray-600"></i>
                            <input type="password" placeholder="Enter Password" name="password" id="bloodBankPassword" required
                                class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none text-lg">
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-xl text-gray-600"></i>
                            <input type="password" placeholder="Re-enter Password" name="cpassword" id="bloodBankCpassword" required
                                class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none text-lg">
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-xl text-gray-600"></i>
                            <input type="text" placeholder="Enter Phone Number" name="phone" id="bloodBankPhone" required
                                class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none text-lg">
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-home absolute left-3 top-1/2 transform -translate-y-1/2 text-xl text-gray-600"></i>
                            <input type="text" placeholder="Enter Address" name="address" id="bloodBankAddress" required
                                class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none text-lg">
                        </div>
                    </div>
                    <input type="hidden" name="user_type" value="BloodBank">
                    <button type="submit" id="RegButton" name="register"
                        class="bg-red-500 text-white my-5 px-4 py-2 rounded-lg w-full text-lg hover:bg-red-600">Register Now</button>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
