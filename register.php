<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Makkah";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$name = $_POST['name'];
$pass = $_POST['password'];
$phone = $_POST['phone'];

$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO register (name, password, phone) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $hashed_pass, $phone);

if ($stmt->execute()) {
    echo "تم التسجيل بنجاح! <a href='login.html'>سجّل الدخول</a>";
} else {
    echo "خطأ أثناء التسجيل: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
