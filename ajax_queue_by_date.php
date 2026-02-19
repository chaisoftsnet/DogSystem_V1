<?php
@session_start();
include 'dbconnect.php';

if (!isset($_SESSION['clinic_id'])) {
    exit;
}

$clinic_id = intval($_SESSION['clinic_id']);
$date = $_GET['date'] ?? date('Y-m-d');

$sql = "
SELECT 
  v.visit_id,
  v.visit_date,
  v.status,
  d.dog_id,
  d.dog_name,
  d.dog_image_path,
  u.fullname,
  i.invoice_id
FROM visits v
JOIN dogs d ON v.dog_id = d.dog_id
JOIN user u ON d.user_id = u.id
LEFT JOIN invoices i 
  ON i.dog_id = v.dog_id
  AND i.clinic_id = v.clinic_id
  AND i.status = 'ชำระแล้ว'
WHERE v.clinic_id = $clinic_id
AND DATE(v.visit_date) = '$date'
ORDER BY v.visit_date ASC
";

$result = mysqli_query($objCon,$sql);

if(mysqli_num_rows($result)==0){
    echo "<tr><td colspan='7'>ยังไม่มีคิวในวันนี้</td></tr>";
    exit;
}

$i = 1;
while($row = mysqli_fetch_assoc($result)):

$img = !empty($row['dog_image_path'])
        ? $row['dog_image_path']
        : 'images/no-pet.png';      
?>
<tr>
  <td><?=$i++?></td>
  <td><?=date('H:i',strtotime($row['visit_date']))?></td>

  <!-- ✅ รูปสุนัข (คลิกแล้ว popup) -->
<td style="text-align:center;">  
  <img src="<?=$img?>"
       style="width:42px;height:42px;
              border-radius:50%;
              object-fit:cover;
              cursor:pointer;"
       onclick="openDogImagePopup(<?=$row['dog_id']?>)"> 
</td>
  <td>
    <a href="dog_profile_new.php?dog_id=<?=$row['dog_id']?>"
       target="_blank"
       class="link-highlight">🖼
       <?=htmlspecialchars($row['dog_name'])?>
    </a>
  </td>

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
        <button onclick="openReceipt(<?=$row['invoice_id']?>)">🧾 ใบเสร็จ</button>
        <button onclick="openInvoicePrint(<?=$row['invoice_id']?>)">🖨️ พิมพ์</button>
      <?php else: ?>
        <span style="color:#999;">ไม่มีใบเสร็จ</span>
      <?php endif; ?>
    <?php endif; ?>
  </td>
</tr>
<?php endwhile; ?>
<script>
document.getElementById('dogImageForm')
.addEventListener('submit', function(e){
  e.preventDefault();

  let formData = new FormData(this);

  fetch('dog_update_image.php', {
    method:'POST',
    body: formData
  })
  .then(r => r.json())
  .then(res => {
    if(res.status === 'success'){
      editDogModal.hide();
      loadQueue(); // รีโหลดเฉพาะตารางคิว
    }else{
      alert('อัปโหลดรูปไม่สำเร็จ');
    }
  });
});
</script>
