<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "login_system"; // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registerMessage = $loginMessage = "";

// Array of greetings
$greetings = [
    "Good Morning! Wishing you a day filled with positive thoughts.",
    "Rise and shine! It's a new day full of opportunities.",
    "Hello! Let's make today productive and successful.",
    "Good day! Keep smiling and have a great day ahead.",
    "Morning! Embrace the challenges of the day with confidence.",
    "Greetings! Remember, today is another chance to shine.",
    "Salutations! Start your day with determination and positivity."
];

// Default greeting
$morningGreeting = $greetings[array_rand($greetings)]; // Initialize with a random greeting

// Register user
if (isset($_POST['register'])) {
    $employer_number = $_POST['employer_number']; // Get employer number from form
    $username = $_POST['reg_username'];
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT); // Hashing the password
    $department = $_POST['department']; // Get department from form

    // Update SQL to include department
    $sql = "INSERT INTO users (employer_number, username, password, department) VALUES ('$employer_number', '$username', '$password', '$department')";
    if ($conn->query($sql) === TRUE) {
        $registerMessage = "Registration successful!";
    } else {
        $registerMessage = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Login user
if (isset($_POST['login'])) {
    $username = trim($_POST['log_username']);
    $password = $_POST['log_password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['username'] = $username;
            $_SESSION['department'] = $row['department']; // Store department in session

            // Generate a random greeting for the user
            $morningGreeting = $greetings[array_rand($greetings)];

            // Redirect based on department
            if ($row['department'] == 'Stores') {
                header("Location: view_needle_stock.php"); // Redirect to needle stock page
                exit();
            } elseif ($row['department'] == 'Production') {
                header("Location: production.php"); // Redirect to production page
                exit();
            } elseif ($row['department'] == 'Admin') {
                // Show modal for Admin to select the page
                echo "
                <script>
                    window.onload = function() {
                        // Create modal
                        var modal = document.createElement('div');
                        modal.style.position = 'fixed';
                        modal.style.top = '0';
                        modal.style.left = '0';
                        modal.style.width = '100%';
                        modal.style.height = '100%';
                        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                        modal.style.display = 'flex';
                        modal.style.alignItems = 'center';
                        modal.style.justifyContent = 'center';
                        modal.style.zIndex = '1000';

                        // Create modal content
                        var modalContent = document.createElement('div');
                        modalContent.style.backgroundColor = 'white';
                        modalContent.style.padding = '20px';
                        modalContent.style.borderRadius = '5px';
                        modalContent.style.textAlign = 'center';

                        var label = document.createElement('label');
                        label.innerHTML = 'Select Page to Access:';
                        modalContent.appendChild(label);

                        var select = document.createElement('select');
                        select.id = 'pageSelect';
                        var option1 = document.createElement('option');
                        option1.value = 'view_needle_stock.php';
                        option1.text = 'View Needle Stock';
                        var option2 = document.createElement('option');
                        option2.value = 'production.php';
                        option2.text = 'Production Page';
                        var option3 = document.createElement('option');
                        option3.value = '';
                        option3.text = 'Select Page';

                        select.appendChild(option3);
                        select.appendChild(option1);
                        select.appendChild(option2);
                        modalContent.appendChild(select);

                        var button = document.createElement('button');
                        button.innerHTML = 'Go';
                        button.onclick = function() {
                            var selectedPage = select.value;
                            if (selectedPage) {
                                window.location.href = selectedPage;
                            } else {
                                alert('Please select a page to enter.');
                            }
                        };
                        modalContent.appendChild(button);

                        var closeButton = document.createElement('button');
                        closeButton.innerHTML = 'Cancel';
                        closeButton.onclick = function() {
                            modal.style.display = 'none';
                        };
                        modalContent.appendChild(closeButton);

                        modal.appendChild(modalContent);
                        document.body.appendChild(modal);
                    };
                </script>";
                exit();
            }
            exit();
        } else {
            $loginMessage = "Invalid password!";
        }
    } else {
        $loginMessage = "No user found with that username!";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link rel="stylesheet" type="text/css" href="login.css">
    <link rel="icon" type="image/png" href="icon.png"/>
</head>
<body>
    <div class="company-name">VT MANUFACTURING [PVT] LIMITED</div>
    <div class="greeting"><?php echo htmlspecialchars($morningGreeting); ?></div>
    <div class="form-container">
        <button class="tab-button active" onclick="showForm('login')">Login</button>
        <button class="tab-button" onclick="checkAdminPassword()">Register</button>
        
        <!-- Login Form -->
        <form method="POST" action="" id="login" class="form active">
            <h2>Login</h2>
            <input type="text" name="log_username" placeholder="Username" required>
            <input type="password" name="log_password" placeholder="Password" required>
            <button type="submit" name="login" id="log">Login</button>
            <?php if ($loginMessage): ?>
                <div class="error"><?php echo htmlspecialchars($loginMessage); ?></div>
            <?php endif; ?>
        </form>

        <!-- Register Form -->
        <form method="POST" action="" id="register" class="form">
            <h2>Register</h2>
            <input type="text" name="employer_number" placeholder="Employer Number" required>
            <input type="text" name="reg_username" placeholder="Username" required>
            <input type="password" name="reg_password" placeholder="Password" required>
            <select name="department" required>
                <option value="">Select Department</option>
                <option value="Admin">Admin</option>
                <option value="Stores">Stores</option>
                <option value="Production">Production</option>
                <option value="Warehouse">Warehouse</option>
            </select>
            <button type="submit" name="register" id="reg">Register</button>
            <?php if ($registerMessage): ?>
                <div class="success"><?php echo htmlspecialchars($registerMessage); ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        const adminPassword = "VTM@star"; // Replace with the actual password

        function showForm(formId) {
            document.getElementById('login').classList.remove('active');
            document.getElementById('register').classList.remove('active');
            document.querySelectorAll('.tab-button').forEach(button => button.classList.remove('active'));
            
            document.getElementById(formId).classList.add('active');
            document.querySelector(`.tab-button[onclick="showForm('${formId}')"]`).classList.add('active');
        }

        function checkAdminPassword() {
            const password = prompt("Enter admin password to access registration:");
            if (password === adminPassword) {
                showForm('register');
            } else {
                alert("Incorrect password! Access denied.");
            }
        }
    </script>
</body>
</html>
