<?php
header("Content-type: image/png");

// เชื่อมต่อฐานข้อมูล
include './connect.php';

// ดึงข้อมูลหมอ
$query = "SELECT name, specialty, phone FROM doctors"; 
$res = mysqli_query($con, $query);

$doctors = [];
while ($row = mysqli_fetch_assoc($res)) {
    $doctors[] = $row;
}

mysqli_close($con);

// สร้างภาพพื้นหลัง
$width = 600;
$height = 300 + (count($doctors) * 40);
$image = imagecreate($width, $height);

// กำหนดสี
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$blue = imagecolorallocate($image, 0, 102, 204);

// เพิ่มหัวข้อ
imagestring($image, 5, 20, 10, "ตารางแพทย์และข้อมูลติดต่อ", $blue);
imageline($image, 10, 40, 590, 40, $black);

// วาดตาราง
$y = 50;
foreach ($doctors as $doctor) {
    imagestring($image, 4, 20, $y, "ชื่อ: " . $doctor['name'], $black);
    imagestring($image, 4, 200, $y, "เชี่ยวชาญ: " . $doctor['specialty'], $black);
    imagestring($image, 4, 400, $y, "โทร: " . $doctor['phone'], $black);
    $y += 40;
}

// ส่งออกเป็น PNG
imagepng($image);
imagedestroy($image);
?>