<?php
require_once 'includes/baglanti.php';
include 'includes/header.php';

$sorgu = $db->query("SELECT * FROM markalar");
$markalar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Kahve Markaları ve Kavurucular</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Marka Adı</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($markalar as $marka): ?>
            <tr>
                <td><?php echo htmlspecialchars($marka['id']); ?></td>
                <td><?php echo htmlspecialchars($marka['marka_adi']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>