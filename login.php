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

$sql = "SELECT * FROM register WHERE name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $name);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($pass, $row['password'])) {
        echo "أهلا يا " . htmlspecialchars($row['name']) . "، تم تسجيل الدخول بنجاح.";
    } else {
        echo "كلمة المرور غير صحيحة.";
    }
} else {
    echo "المستخدم غير موجود.";
}

$stmt->close();
$conn->close();
?>
