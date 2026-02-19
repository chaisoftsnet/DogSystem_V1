<?php
if (!isset($visit_id)) exit;

/* ===============================
   LOAD TREATMENT LIST
================================ */
$treatments = mysqli_query($objCon,"
  SELECT *
  FROM treatments
  WHERE visit_id=$visit_id
  ORDER BY treatment_date DESC, treatment_id DESC
");
?>

<?php if(mysqli_num_rows($treatments)==0): ?>
  <p class="text-muted">ยังไม่มีประวัติการรักษา</p>
<?php else: ?>

<table class="datatable" width="100%" border="0">
  <thead>
    <tr>
      <th align="left">วันที่รักษา</th>
      <th align="left">อาการ</th>
      <th align="left">วินิจฉัย</th>
      <th align="left">การรักษา</th>
      <th align="left">นัดถัดไป</th>
      <th align="center">จัดการ</th>
    </tr>
  </thead>
  <tbody>

<?php while($tr=mysqli_fetch_assoc($treatments)): ?>
<tr>
  <td><?=htmlspecialchars($tr['treatment_date'])?></td>
  <td><?=nl2br(htmlspecialchars($tr['symptoms']))?></td>
  <td><?=nl2br(htmlspecialchars($tr['diagnosis']))?></td>
  <td>
    <?=nl2br(htmlspecialchars($tr['treatment']))?>
    <?php if(!empty($tr['file_path'])): ?>
      <br>
      📎 <a href="uploads/treatments/<?=$tr['file_path']?>" target="_blank">
        <?=$tr['file_type']?>
      </a>
    <?php endif; ?>
  </td>
  <td>
    <?= $tr['next_appointment'] ? htmlspecialchars($tr['next_appointment']) : '-' ?>
  </td>

  <td align="center">
    <!-- EDIT -->
    <a class="btn-edit"
       href="<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&tab=treat&service_type=treatment&edit_treatment_id=<?=$tr['treatment_id']?>#treat"
       title="แก้ไขข้อมูล">
       ✏️
    </a>
    &nbsp;|&nbsp;

    <!-- DELETE -->
    <a class="btn-delete"
       href="javascript:if(confirm('ยืนยันลบรายการรักษานี้ ?')){
         window.location='<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&tab=treat&service_type=treatment&delete_treatment_id=<?=$tr['treatment_id']?>';
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
