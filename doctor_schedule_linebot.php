<?php
include 'connect.php'; // ไฟล์เชื่อมต่อฐานข้อมูล

$access_token = "YOUR_CHANNEL_ACCESS_TOKEN"; // ใส่ Channel Access Token ของคุณ
$image_path = "doctor_schedule.png"; // ชื่อไฟล์ภาพที่สร้าง
$image_url = "https://yourserver.com/" . $image_path; // URL ที่ให้ LINE Bot ใช้

// สร้างรูปภาพตารางหมอ
generateDoctorScheduleImage($image_path);

// รับ JSON Data จาก LINE Webhook
$content = file_get_contents("php://input");
$events = json_decode($content, true);

if (!empty($events['events'])) {
    foreach ($events['events'] as $event) {
        if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
            $user_message = strtolower(trim($event['message']['text']));

            if ($user_message == "ตารางหมอ") { // ถ้าผู้ใช้พิมพ์ว่า "ตารางหมอ"
                replyToUser($event['replyToken'], $image_url);
            }
        }
    }
}

// ฟังก์ชันสร้างรูปภาพตารางหมอจากฐานข้อมูล
function generateDoctorScheduleImage($output_path) {
    global $con;
    
    // ดึงข้อมูลตารางหมอจากฐานข้อมูล
    $query = "SELECT doctor_name, available_date, available_time FROM doctor_schedule ORDER BY available_date, available_time";
    $res = mysqli_query($con, $query);

    // กำหนดขนาดของรูปภาพ
    $width = 600;
    $height = 400;
    $image = imagecreatetruecolor($width, $height);
    $background_color = imagecolorallocate($image, 255, 255, 255);
    imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

    // ตั้งค่าฟอนต์และสี
    $text_color = imagecolorallocate($image, 0, 0, 0);
    $font_path = './THSarabunNew.ttf'; // ใช้ฟอนต์ภาษาไทย (ต้องมีไฟล์ฟอนต์)

    // เขียนหัวข้อ "ตารางหมอ"
    imagettftext($image, 20, 0, 200, 30, $text_color, $font_path, "📅 ตารางหมอ");

    // วาดเส้นตาราง
    imageline($image, 20, 50, 580, 50, $text_color);
    imagettftext($image, 16, 0, 40, 80, $text_color, $font_path, "👨‍⚕️ ชื่อหมอ      📆 วันที่        ⏰ เวลา");

    $y = 110;
    while ($row = mysqli_fetch_assoc($res)) {
        $text = $row['doctor_name'] . "  |  " . $row['available_date'] . "  |  " . $row['available_time'];
        imagettftext($image, 14, 0, 40, $y, $text_color, $font_path, $text);
        $y += 30;
    }

    // บันทึกไฟล์รูปภาพ
    imagepng($image, $output_path);
    imagedestroy($image);
}

// ฟังก์ชันส่งรูปไปยัง LINE User
function replyToUser($replyToken, $image_url) {
    global $access_token;

    $messages = [
        "type" => "image",
        "originalContentUrl" => $image_url,
        "previewImageUrl" => $image_url
    ];

    $data = [
        "replyToken" => $replyToken,
        "messages" => [$messages]
    ];

    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $access_token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
}
?>