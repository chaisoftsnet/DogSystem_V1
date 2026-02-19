<?php
if (!isset($visit_id)) exit;

/* ===============================
   LOAD VACCINATION LIST
================================ */
$vaccinations = mysqli_query($objCon,"
  SELECT *
  FROM vaccinations
  WHERE dog_id={$visit['dog_id']}
  AND clinic_id=$clinic_id
  ORDER BY vaccine_date DESC, vaccine_id DESC
");
?>

<?php if(mysqli_num_rows($vaccinations)==0): ?>
  <p class="text-muted">ยังไม่มีประวัติการฉีดวัคซีน</p>
<?php else: ?>

<table class="datatable" width="100%" border="0">
  <thead>
    <tr>
      <th align="left">วันที่ฉีด</th>
      <th align="left">ชื่อวัคซีน</th>
      <th align="left">ประเภท</th>
      <th align="left">นัดถัดไป</th>
      <th align="left">สัตวแพทย์</th>
      <th align="center">จัดการ</th>
    </tr>
  </thead>
  <tbody>

<?php while($vc=mysqli_fetch_assoc($vaccinations)): ?>
<tr>
  <td><?=htmlspecialchars($vc['vaccine_date'])?></td>
  <td><?=htmlspecialchars($vc['vaccine_name'])?></td>
  <td><?=htmlspecialchars($vc['vaccine_type'])?></td>
  <td>
    <?= $vc['next_due_date'] ? htmlspecialchars($vc['next_due_date']) : '-' ?>
  </td>
  <td><?=htmlspecialchars($vc['doctor_name'])?></td>

  <td align="center">
    <!-- EDIT -->
    <a class="btn-edit"
       href="<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&tab=treat&service_type=vaccination&edit_vaccine_id=<?=$vc['vaccine_id']?>#treat"
       title="แก้ไขข้อมูล">
       ✏️
    </a>
    &nbsp;|&nbsp;

    <!-- DELETE -->
    <a class="btn-delete"
       href="javascript:if(confirm('ยืนยันลบข้อมูลวัคซีนนี้ ?')){
         window.location='<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&tab=treat&service_type=vaccination&delete_vaccine_id=<?=$vc['vaccine_id']?>';
       }"
       title="ลบข้อมูล">
       🗑
    </a>
  </td>
</tr>

<?php endwhile; ?>

  </tbody>
</table>

<?php endif; ?>
