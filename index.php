<?php
function areaToSection($area) {
    $map = [
        "الحي الاول" => "1", "الحي التاني" => "2", "الحي التالت" => "3", "الحي الرابع" => "4",
        "الحي الخامس" => "5", "الحي السادس" => "6", "الحي السابع" => "7", "الحي التامن" => "8",
        "الحي التاسع" => "9",
        "دار مصر"=>"darMasr"
        ,"جنة مصر"=>"ganna","سكن مصر"=>"sakan","ريزيدانس"=>"rese",
        "المنطقه الاوله" => "m1", "المنطقه التانيه" => "m2", "المنطقه التالته" => "m3",
        "المنطقه الرابعه" => "m4", "المنطقه الخامسه" => "m5", "المنطقه السادسه" => "m6",
        "المنطقه السابعه" => "m7", "المنطقه التامنه" => "m8", "المنطقه التاسعه" => "m9",
        "100 متر" => "m100", "70 متر" => "m70", "60 متر" => "m60",
        "تعاونيات" => "tawen", "اتحاد تعاوني" => "etihad","العائلي"=>"family"
        ,"السوبر القديم"=>"super","الجهاز"=>"gehaz","المتميز"=>"motamiz"
    ];
    return $map[$area] ?? 'unknown';
}

function normalize($str) {
    return trim(preg_replace('/\s+/', ' ', mb_strtolower($str)));
}

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $area = $_POST['area'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $phoneValue = $_POST['phone'] ?? '';
    $code = trim($_POST['code'] ?? '');

    $phoneMap = [
        'عمار' => '+201029053170',
        'اياد' => '+201029700942'
    ];
    $phone = isset($phoneMap[$phoneValue]) ? $phoneMap[$phoneValue] : '';
    $phoneName = $phoneValue;

    if (!$area) $errors[] = "المنطقة مطلوبة";
    if (!$address) $errors[] = "العنوان الرئيسي مطلوب";
    if (!$details) $errors[] = "باقي التفاصيل مطلوبة";
    if (!$phone) $errors[] = "رقم الهاتف مطلوب";
    if (!$code) $errors[] = "الكود مطلوب";

    if (!$errors) {
        $newListing = [
            'address' => $address,
            'details' => $details,
            'code' => $code,
            'phones' => [
                [
                    'number' => $phone,
                    'name' => $phoneName
                ]
            ]
        ];

        $file = 'listings.json';
        $dataArray = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $sectionId = areaToSection($area);
        $found = false;

        foreach ($dataArray as &$sec) {
            if ($sec['section'] === $sectionId && $sec['area'] === $area) {
                foreach ($sec['listings'] as $item) {
                    $matchCount = 0;
                    if (normalize($item['address']) === normalize($address)) $matchCount++;
                    if (normalize($item['code']) === normalize($code)) $matchCount++;
                    if (normalize($item['details']) === normalize($details)) $matchCount++;

                    if ($matchCount >= 2) {
                        $errors[] = "❗ تم إضافة هذا العقار مسبقًا (تطابق $matchCount من 3).";
                        break 2;
                    }
                }
                $sec['listings'][] = $newListing;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $dataArray[] = [
                'section' => $sectionId,
                'area' => $area,
                'listings' => [$newListing]
            ];
        }

        if (!$errors) {
            file_put_contents($file, json_encode($dataArray, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = "تم حفظ العقار بنجاح ✅";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta charset="UTF-8">
  <title>إضافة عقار جديد</title>
  <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
  <style>
        body {
            background: #eef4ff;
            font-family: 'Tajawal', sans-serif;
            padding: 40px;
            margin: 0;
        }

        form {
            background: #fff;
            padding: 30px;
            margin: auto;
            max-width: 500px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            direction: rtl;
            width: 100%;
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 1rem;
        }

        input[type="submit"] {
            background: linear-gradient(to right, #007BFF, #00C6FF);
            color: #fff;
            border: none;
            padding: 12px;
            margin-top: 20px;
            width: 100%;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .message.success {
            background: #e5ffe5;
            color: #007500;
        }

        .message.error {
            background: #ffe5e5;
            color: #cc0000;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px;
            }

            form {
                padding: 20px;
                border-radius: 15px;
            }

            input[type="submit"] {
                padding: 10px;
                font-size: 0.95rem;
            }

            label,
            input[type="text"],
            select {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>

<form method="post">
  <?php if ($success): ?>
    <div class="message success"><?= $success ?></div>
  <?php elseif (!empty($errors)): ?>
    <div class="message error"><ul><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <label for="area">المنطقة:</label>
  <select name="area" id="area">
    <optgroup label="الأحياء">
      <option>الحي الاول</option><option>الحي التاني</option><option>الحي التالت</option>
      <option>الحي الرابع</option><option>الحي الخامس</option><option>الحي السادس</option>
      <option>الحي السابع</option><option>الحي التامن</option><option>الحي التاسع</option>
    </optgroup>
    <optgroup label="كومباوندات">
    <option>دار مصر</option><option>جنة مصر</option><option>سكن مصر</option><option>ريزيدانس</option>
    </optio>
    
    <optgroup label="المناطق">
      <option>المنطقه الاوله</option><option>المنطقه التانيه</option><option>المنطقه التالته</option>
      <option>المنطقه الرابعه</option><option>المنطقه الخامسه</option><option>المنطقه السادسه</option>
      <option>المنطقه السابعه</option><option>المنطقه التامنه</option><option>المنطقه التاسعه</option>
    </optgroup>
    <optgroup label="مساكن">
      <option>60 متر</option><option>70 متر</option><option>100 متر</option>
      <option>السوبر القديم</option><option>الجهاز</option><option>المتميز</option>

    </optgroup>
    <optgroup label="أخرى">
      <option>تعاونيات</option><option>اتحاد تعاوني</option><option>العائلي</option>
    </optgroup>
  </select>

  <label>العنوان الرئيسي:</label>
  <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">

  <label>باقي التفاصيل:</label>
  <input type="text" name="details" value="<?= htmlspecialchars($_POST['details'] ?? '') ?>">

  <label>الكود:</label>
  <input type="text" name="code" value="<?= htmlspecialchars($_POST['code'] ?? '') ?>">

  <label>رقم الهاتف:</label>
  <select name="phone">
    <option value="عمار">عمار</option>
    <option value="اياد">اياد</option>
  </select>

  <input type="submit" value="حفظ العقار">
</form>

</body>
</html>
