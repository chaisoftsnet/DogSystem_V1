<?php
$dewormings = mysqli_query($objCon,"
  SELECT *
  FROM dewormings
  WHERE dog_id={$visit['dog_id']}
  AND clinic_id=$clinic_id
  ORDER BY treatment_date DESC
");
?>
<?php if(mysqli_num_rows($dewormings)==0): ?>
  <p class="text-muted">ยังไม่มีประวัติการถ่ายพยาธิ</p>
<?php else: ?>

<table class="datatable" width="100%">
<tr>
  <th>วันที่ถ่าย</th>
  <th>ยา</th>
  <th>วันครบถัดไป</th>
  <th>หมายเหตุ</th>
  <th width="90">จัดการ</th>
</tr>
<?php while($dw=mysqli_fetch_assoc($dewormings)): ?>
<tr>
  <td><?=date('d/m/Y',strtotime($dw['treatment_date']))?></td>
  <td><?=$dw['drug_name']?></td>
  <td><?=$dw['next_due_date'] ? date('d/m/Y',strtotime($dw['next_due_date'])) : '-'?></td>
  <td><?=$dw['note']?></td>
  <td align="center">
    <a class="btn-edit"
       href="<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&service_type=deworming&edit_deworming_id=<?=$dw['deworming_id']?>#treat">
       ✏️
    </a>
    &nbsp;|&nbsp;   
    <a class="btn-delete"
       href="javascript:if(confirm('ยืนยันลบข้อมูลนี้?')){
         window.location='<?=$_SERVER['SCRIPT_NAME']?>?visit_id=<?=$visit_id?>&delete_deworming_id=<?=$dw['deworming_id']?>';
       }">🗑</a>
  </td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>