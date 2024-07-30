<?php
require('connection.php');
session_start();

function validate($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Handle login
    if (isset($_POST['login'])) {
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

            // Debugging: Log the query and parameters
            error_log("Login attempt - Email: $email, User Type: $user_type");

            if ($result && $result->num_rows == 1) {
                $result_fetch = $result->fetch_assoc();
                if (password_verify($password, $result_fetch['password'])) {
                    if (strtolower($result_fetch['user_type']) == "admin") {
                        $_SESSION['Aloggedin'] = true;
                        $_SESSION['Adminname'] = $result_fetch['fullname'];
                        $_SESSION['Adminemail'] = $result_fetch['email'];
                        header("Location: AdminDashboard\dashboard.php");
                    } else if (strtolower($result_fetch['user_type']) == "donor") {
                        $_SESSION['Dloggedin'] = true;
                        $_SESSION['donorname'] = $result_fetch['fullname'];
                        $_SESSION['donoremail'] = $result_fetch['email'];
                        header("Location: donordashboard.php");
                    } else if (strtolower($result_fetch['user_type']) == "bloodbank") {
                        $_SESSION['Bloggedin'] = true;
                        $_SESSION['bankname'] = $result_fetch['fullname'];
                        $_SESSION['bankemail'] = $result_fetch['email'];
                        header("Location: Bankdashboard\Bbankdashboard.php");
                    } else {
                        $_SESSION['Uloggedin'] = true;
                        $_SESSION['username'] = $result_fetch['fullname'];
                        $_SESSION['useremail'] = $result_fetch['email'];
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    header("Location: login.php?error=Incorrect password");
                    exit();
                }
            } else {
                // Debugging: Log error message
                error_log("Login failed - No matching user found. Email: $email, User Type: $user_type");
                header("Location: login.php?error=Email not registered for this user typeEmail: $email, User Type: $user_type" );
                exit();
            }
        }
    }


    // Handle registration
    if (isset($_POST['register'])) {
        $fullname = validate($_POST['fullname']);
        $email = validate($_POST['email']);
        $password = validate($_POST['password']);
        $confirm_password = validate($_POST['cpassword']);
        $phone = validate($_POST['phone']);
        $address = validate($_POST['address']);
        $latitude = validate($_POST['latitude']);
        $longitude = validate($_POST['longitude']);
        $user_type = validate($_POST['user_type']);

        if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($address) || empty($user_type)) {
            header("Location: register.php?error=Please fill all the fields!!");
            exit();
        
        }else if(empty($latitude) || empty($longitude)){
            header("Location: register.php?error=Enter correct address ");
            exit();
        
        }elseif (!preg_match('/^[a-zA-Z]+(?: [a-zA-Z]+)*$/', $fullname)) {
            header("Location: register.php?error=Name must contain only letters");
            exit();
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: register.php?error=Invalid email format!!");
            exit();
        } elseif ($password != $confirm_password) {
            header("Location: register.php?error=Password and confirm password do not match!!");
            exit();
        } else {
            // Check if the email already exists for the same user type
            $user_exist_query = $con->prepare("SELECT * FROM users WHERE email = ? AND user_type = ?");
            $user_exist_query->bind_param("ss", $email, $user_type);
            $user_exist_query->execute();
            $result = $user_exist_query->get_result();

            if ($result && $result->num_rows > 0) {
                header("Location: register.php?error=Email already exists for this user type");
                exit();
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                if ($user_type == 'Donor') {
                    $donor_blood_type = validate($_POST['donor_blood_type']);
                    $sql = $con->prepare("INSERT INTO users (fullname, email, password, phone, address, user_type, latitude,longitude,donor_blood_type) VALUES (?, ?, ?, ?, ?, ?, ?,?,?)");
                    $sql->bind_param("sssssssss", $fullname, $email, $hashed_password, $phone, $address, $user_type,$latitude,$longitude, $donor_blood_type);
                } elseif ($user_type == 'BloodBank') {
                    $sql = $con->prepare("INSERT INTO users (fullname, email, password, phone, address,latitude,longitude, user_type) VALUES (?, ?, ?, ?, ?, ?,?,?)");
                    $sql->bind_param("ssssssss", $fullname, $email, $hashed_password, $phone, $address,$latitude,$longitude, $user_type);
                } elseif ($user_type == 'Admin') {
                    $admin_code = validate($_POST['admin_code']);
                    $sql = $con->prepare("INSERT INTO users (fullname, email, password, phone, address, user_type) VALUES (?, ?, ?, ?, ?, ?)");
                    $sql->bind_param("ssssss", $fullname, $email, $hashed_password, $phone, $address, $user_type);
                } else {
                    $sql = $con->prepare("INSERT INTO users (fullname, email, password, phone, address,latitude,longitude, user_type) VALUES (?, ?, ?, ?,?,?, ?, ?)");
                    $sql->bind_param("ssssssss", $fullname, $email, $hashed_password, $phone, $address,$latitude,$longitude, $user_type);
                }

                if ($sql->execute()) {
                    header("Location: login.php?error=Registration successful! You can now login!!");
                } else {
                    header("Location: register.php?error=Registration failed");
                }
                exit();
            }
        }
    }
}
?>
