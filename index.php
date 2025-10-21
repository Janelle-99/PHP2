<?php

$host = 'localhost';
$db   = 'school';
$user = 'root';          
$pass = '';             
$charset = 'utf8mb4';


$apiKey = '958ab87098adc14de47b310a2da112cf';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $stmt = $pdo->query('SELECT * FROM student');
    $students = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}


function getWeather($city, $apiKey) {
    $cityWithCountry = urlencode($city . ',PH');
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$cityWithCountry}&units=metric&appid={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return 'N/A';
    }

    $data = json_decode($response, true);

   
    if (isset($data['cod']) && $data['cod'] != 200) {
        return "Error: " . $data['message'];
    }

    if (isset($data['weather'][0]['description']) && isset($data['main']['temp'])) {
        $desc = ucfirst($data['weather'][0]['description']);
        $temp = round($data['main']['temp']);
        return "{$desc}, {$temp}°C";
    } else {
        return 'N/A';
    }
}


$weatherCache = [];

foreach ($students as &$student) {
    $city = $student['city'];
    if (!isset($weatherCache[$city])) {
        $weatherCache[$city] = getWeather($city, $apiKey);
    }
    $student['current_weather'] = $weatherCache[$city];
}
unset($student);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Information System with Weather</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="container">
        <h1>Student Information System With Live Weather</h1>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Course</th>
                    <th>City</th>
                    <th>Current Weather</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                    <td><?= htmlspecialchars($student['age']) ?></td>
                    <td><?= htmlspecialchars($student['course']) ?></td>
                    <td><?= htmlspecialchars($student['city']) ?></td>
                    <td><?= htmlspecialchars($student['current_weather']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="footer">Weather data provided by OpenWeather API</p>
    </div>
</body>
</html>
