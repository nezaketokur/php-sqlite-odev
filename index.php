<?php
require_once 'includes/baglanti.php';
include 'includes/header.php';

$sorgu = $db->query("SELECT * FROM kahveler");
$kayitlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Tüm Kahveler</h2>

<?php if (count($kayitlar) > 0): ?>
    <table>
        <thead>
            <tr>
                <?php foreach (array_keys($kayitlar[0]) as $sutun): ?>
                    <th><?php echo htmlspecialchars(ucfirst($sutun)); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kayitlar as $satir): ?>
                <tr>
                    <?php foreach ($satir as $deger): ?>
                        <td><?php echo htmlspecialchars($deger); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Henüz kayıtlı kahve bulunmuyor.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>