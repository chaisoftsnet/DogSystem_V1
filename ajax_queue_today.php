<?php
@session_start();
include 'dbconnect.php';

if (!isset($_SESSION['clinic_id'])) {
    exit;
}

$clinic_id = intval($_SESSION['clinic_id']);

$sql = "
SELECT 
  v.visit_id,
  v.visit_date,
  v.status,
  d.dog_id,d.dog_name,
  u.fullname,

  /* ✅ invoice_id สำหรับพิมพ์ใบเสร็จ */
  i.invoice_id

FROM visits v
JOIN dogs d ON v.dog_id = d.dog_id
JOIN user u ON d.user_id = u.id

/* ✅ LEFT JOIN invoices */
LEFT JOIN invoices i 
  ON i.dog_id = v.dog_id
  AND i.clinic_id = v.clinic_id
  AND i.status = 'ชำระแล้ว'

WHERE v.clinic_id = $clinic_id
AND v.visit_date >= CURDATE()
AND v.visit_date < CURDATE() + INTERVAL 1 DAY

ORDER BY v.visit_date ASC
";

$result = mysqli_query($objCon,$sql);
if(mysqli_num_rows($result)==0){
    echo "<tr><td colspan='5'>ยังไม่มีคิววันนี้</td></tr>";
    exit;
}
while($row=mysqli_fetch_assoc($result)):
?>
<tr>
  <td class="col-time"><?=date('H:i',strtotime($row['visit_date']))?></td>
  <td><a href="dog_profile.php?dog_id=<?=$row['dog_id']?>" class="link-highlight" target="_blank"><?=htmlspecialchars($row['dog_name'])?></a></td>
  <td><?=htmlspecialchars($row['fullname'])?></td>
  <td>
    <span class="status <?=$row['status']?>"><?=$row['status']?></span>
  </td>
  <td>
    <?php if($row['status']!='เสร็จสิ้น'): ?>
      <button onclick="openVisitPopup(<?=$row['visit_id']?>)">
        ▶ เปิดเคส
      </button>
    <?php else: ?>
      <?php if(!empty($row['invoice_id'])): ?>
        <button onclick="openReceipt(<?=$row['invoice_id']?>)">🧾 ใบเสร็จเล็ก </button>
        <button onclick="openInvoicePrint(<?=$row['invoice_id']?>)">🖨️ พิมพ์ใบเสร็จใหญ่ </button>
      <?php else: ?>
        <span style="color:#999;">ไม่มีใบเสร็จ</span>
      <?php endif; ?>
    <?php endif; ?>
  </td>
</tr>
<?php endwhile; ?>
