<?php
require_once 'includes/baglanti.php';
include 'includes/header.php';

$sorgu = $db->query("SELECT * FROM kavurma_turleri");
$turler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Kavurma Türleri</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Kavurma Derecesi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($turler as $tur): ?>
            <tr>
                <td><?php echo htmlspecialchars($tur['id']); ?></td>
                <td><?php echo htmlspecialchars($tur['tur_adi']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>