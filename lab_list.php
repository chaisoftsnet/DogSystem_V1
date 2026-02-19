<?php
$labs = mysqli_query($objCon,"
  SELECT *
  FROM lab_results
  WHERE dog_id={$visit['dog_id']}
  AND clinic_id=$clinic_id
  ORDER BY test_date DESC
");
?>

<?php if(mysqli_num_rows($labs)==0): ?>
  <p class="text-muted">ยังไม่มีประวัติการตรวจแล็บ</p>
<?php else: ?>

<table class="datatable" width="100%">
<tr>
  <th>วันที่ตรวจ</th>
  <th>การตรวจ</th>
  <th>ผลเลือด</th>
  <th>ผลปัสสาวะ</th>
  <th>ไฟล์</th>
  <th width="90">จัดการ</th>
</tr>
<?php while($lb=mysqli_fetch_assoc($labs)): ?>
<tr>
  <td><?=date('d/m/Y',strtotime($lb['test_date']))?></td>
  <td><?=$lb['test_name']?></td>
  <td><?=$lb['blood_result']?></td>  
  <td><?=$lb['urine_result']?></td>  
  <td align="center">
    <?php if($lb['file_path']): ?>
      <a href="uploads/labs/<?=$lb['file_path']?>" target="_blank">📎</a>
    <?php else: ?> - <?php endif; ?>
  </td>
  <td align="center">
    <a class="btn-edit"
       href="<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&service_type=lab&edit_lab_id=<?=$lb['lab_id']?>#treat">✏️</a>
    |
    <a class="btn-delete"
       href="javascript:if(confirm('ยืนยันลบรายการนี้?')){
         window.location='<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&delete_lab_id=<?=$lb['lab_id']?>';
       }">🗑</a>
  </td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>