<?php
require('connection.php');
session_start();

function validate($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = validate($_POST['email']);
    $password = validate($_POST['password']);
    $user_type = validate($_POST['user_type']);

    if (empty($email) || empty($password) || empty($user_type)) {
        header("Location: login.php?error=Please fill all the fields!!");
        exit();
    } else {
        $query = $con->prepare("SELECT * FROM users WHERE email = ? AND user_type = ?");
        $query->bind_param("ss", $email, $user_type);
        $query->execute();
        $result = $query->get_result();

        if ($result && $result->num_rows == 1) {
            $result_fetch = $result->fetch_assoc();
            if (password_verify($password, $result_fetch['password'])) {
                switch (strtolower($result_fetch['user_type'])) {
                    case 'admin':
                        $_SESSION['Aloggedin'] = true;
                        $_SESSION['Adminname'] = $result_fetch['fullname'];
                        $_SESSION['Adminemail'] = $result_fetch['email'];
                        header("Location: AdminDashboard/admindashboard.php");
                        break;
                    case 'donor':
                        $_SESSION['Dloggedin'] = true;
                        $_SESSION['donorname'] = $result_fetch['fullname'];
                        $_SESSION['donoremail'] = $result_fetch['email'];
                        header("Location: Donordashboard/dashboard.php");
                        break;
                    case 'bloodbank':
                        $_SESSION['Bloggedin'] = true;
                        $_SESSION['bankid'] = $result_fetch['id'];
                        $_SESSION['bankname'] = $result_fetch['fullname'];
                        $_SESSION['bankemail'] = $result_fetch['email'];
                        header("Location: Bankdashboard/Bbankdashboard.php");
                        break;
                    default:
                        $_SESSION['Uloggedin'] = true;
                        $_SESSION['username'] = $result_fetch['fullname'];
                        $_SESSION['useremail'] = $result_fetch['email'];
                        header("Location: index.php");
                        break;
                }
                exit();
            } else {
                header("Location: login.php?error=Incorrect password");
                exit();
            }
        } else {
            header("Location: login.php?error=Email not registered for this user type");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js" integrity="sha512-kQfUkAq5Bc+0WZsbR4m5bJb2nVUwTxkKiZgLttgFyInD2nKN/LyLo3Z2/0lNKl8LZwr7k9D5XG1vFRJ8A4KZg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body class="bg-gray-200">

    <?php @include 'header.php'; ?>

    <main id="login-cont" class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-2xl bg-white rounded-lg shadow-lg flex overflow-hidden">
           
            <!-- Form Section -->
            <div class="flex-1 p-8">
                <h2 class="text-3xl font-bold text-center text-red-500 mb-6">Login</h2>
                <form action="login.php" method="post" class="space-y-6">
                    <?php if (isset($_GET['error'])): ?>
                        <?php
                        $errorMessage = htmlspecialchars($_GET['error']);
                        $errorClass = ($errorMessage === 'Registration successful! You can now login!!') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <div class="p-4 rounded-md text-center font-semibold <?php echo $errorClass; ?>">
                            <p><?php echo $errorMessage; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="email">Email</label>
                        <input
                            id="email"
                            class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                            type="email" placeholder="Enter Email" name="email" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="password">Password</label>
                        <input
                            id="password"
                            class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                            type="password" placeholder="Enter Password" name="password" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="userType">User Type</label>
                        <select name="user_type" id="userType" required
                            class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                            <option value="" disabled selected>Select User Type</option>
                            <option value="User">User</option>
                            <option value="Donor">Donor</option>
                            <option value="BloodBank">BloodBank</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" name="login"
                        class="w-full py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300">Login
                        Now</button>

                    <div class="text-center mt-4">
                        Don't have an account? <a href="userregister.php" class="text-blue-500 hover:underline">Signup</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>

</html>
