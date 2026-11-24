<?php
require_once 'baglanti.php';

$sorgu = $db->query("SELECT * FROM kahveler");
$kahveler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Dünya Kahveleri Veritabanı</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        table { border-collapse: collapse; width: 65%; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Dünya Kahveleri Listesi</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Kahve Adı</th>
                <th>Köken (Ülke)</th>
                <th>Kavrum Derecesi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kahveler as $kahve): ?>
                <tr>
                    <td><?= htmlspecialchars($kahve['id']) ?></td>
                    <td><?= htmlspecialchars($kahve['kahve_adi']) ?></td>
                    <td><?= htmlspecialchars($kahve['koken']) ?></td>
                    <td><?= htmlspecialchars($kahve['kavrum_derecesi']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
