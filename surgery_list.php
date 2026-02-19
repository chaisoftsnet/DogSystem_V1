<?php
$surgeries = mysqli_query($objCon,"
  SELECT *
  FROM surgeries
  WHERE dog_id={$visit['dog_id']}
  AND clinic_id=$clinic_id
  ORDER BY surgery_date DESC
");
?>
<?php if(mysqli_num_rows($surgeries)==0): ?>
  <p class="text-muted">ยังไม่มีประวัติการผ่าตัด</p>
<?php else: ?>
<table class="datatable" width="100%">
<tr>
  <th>วันที่</th>
  <th>ประเภท</th>
  <th>สัตวแพทย์</th>
  <th>ผลผ่าตัด</th>
  <th>ไฟล์</th>
  <th width="90">จัดการ</th>
</tr>

<?php if(mysqli_num_rows($surgeries)==0): ?>
<tr><td colspan="6" align="center">— ยังไม่มีข้อมูลการผ่าตัด —</td></tr>
<?php endif; ?>

<?php while($s=mysqli_fetch_assoc($surgeries)): ?>
<tr>
  <td><?=date('d/m/Y',strtotime($s['surgery_date']))?></td>
  <td><?=$s['surgery_type']?></td>
  <td><?=$s['doctor_name']?></td>
  <td><?=$s['outcome']?></td>   
  <td align="center">
    <?php if($s['file_path']): ?>
      <a href="uploads/surgeries/<?=$s['file_path']?>" target="_blank">📎</a>
    <?php else: ?> - <?php endif; ?>
  </td>
  <td align="center">
    <a class="btn-edit"
       href="<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&service_type=surgery&edit_surgery_id=<?=$s['surgery_id']?>#treat">✏️</a>
    |
    <a class="btn-delete"
       href="javascript:if(confirm('ยืนยันลบรายการนี้?')){
         window.location='<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&delete_surgery_id=<?=$s['surgery_id']?>';
       }">🗑</a>
  </td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>