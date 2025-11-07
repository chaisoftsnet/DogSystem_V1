<?php
@session_start();
require_once('dbconnect.php');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// =============== ADD ===============
if($action==='add'){
  $supplier_id = (int)$_POST['supplier_id'];
  $note = mysqli_real_escape_string($objCon, $_POST['note'] ?? '');
  $clinic_id = (int)($_SESSION['clinic_id'] ?? 1);
  $user_id = (int)($_SESSION['user_id'] ?? 0);

  mysqli_query($objCon, "INSERT INTO purchase_orders (supplier_id, clinic_id, note, status, po_date, created_at)
                         VALUES ($supplier_id, $clinic_id, '$note', 'รออนุมัติ', NOW(), NOW())");
  $po_id = mysqli_insert_id($objCon);

  $total = 0;
  if(!empty($_POST['product_id'])){
    foreach($_POST['product_id'] as $idx=>$pid){
      $pid = (int)$pid;
      $qty = (int)($_POST['quantity'][$idx] ?? 0);
      $cost= (float)($_POST['unit_cost'][$idx] ?? 0);
      if($pid>0 && $qty>0){
        mysqli_query($objCon, "INSERT INTO purchase_order_items (po_id, product_id, quantity, unit_cost)
                               VALUES ($po_id, $pid, $qty, $cost)");
        $total += ($qty * $cost);
      }
    }
  }
  mysqli_query($objCon, "UPDATE purchase_orders SET total_amount = $total WHERE po_id = $po_id");
  echo "✅ บันทึกใบสั่งซื้อเรียบร้อย (PO-$po_id)";
  exit;
}

// =============== VIEW ===============
if($action==='view'){
  $id = (int)($_GET['id'] ?? 0);
  $p = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT p.*, s.supplier_name, s.phone, s.email
    FROM purchase_orders p LEFT JOIN suppliers s ON p.supplier_id=s.supplier_id WHERE p.po_id=$id"));
  $items = mysqli_query($objCon, "SELECT i.*, pr.product_name FROM purchase_order_items i 
    LEFT JOIN products pr ON pr.product_id=i.product_id WHERE i.po_id=$id");
  ?>
  <div class="modal-header bg-primary text-white">
    <h5 class="modal-title">รายละเอียดใบสั่งซื้อ (PO-<?=h($p['po_id'])?>)</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">    
      <strong>ผู้จำหน่าย:</strong> <?=h($p['supplier_name'])?><br>
      <strong>เบอร์โทร:</strong> <?=h($p['phone'])?><br>
      <strong>อีเมล:</strong> <?=h($p['email'])?><br>
      <strong>วันที่สั่งซื้อ:</strong> <?=date('d/m/Y H:i',strtotime($p['po_date']))?><br>
      <strong>สถานะ:</strong> <?=h($p['status'])?><br>
      <strong>หมายเหตุ:</strong> <?=h($p['note'])?>
    <table class="table table-bordered text-center align-middle">
      <thead class="table-light">
        <tr>
          <th>สินค้า</th><th>จำนวน</th><th>ราคาต่อหน่วย</th><th>รวม</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $sum=0;
        while($r=mysqli_fetch_assoc($items)){
          $line = $r['quantity'] * $r['unit_cost'];
          $sum += $line;
          echo "<tr>
            <td>".h($r['product_name'])."</td>
            <td>{$r['quantity']}</td>
            <td>".number_format($r['unit_cost'],2)."</td>
            <td>".number_format($line,2)."</td>
          </tr>";
        }
      ?>
      </tbody>
      <tfoot>
        <tr><th colspan="3" class="text-end">รวมทั้งสิ้น</th><th><?=number_format($sum,2)?></th></tr>
      </tfoot>
    </table>
  </div>
  <div class="modal-footer">
    <a class="btn btn-secondary" target="_blank" href="purchase_order_print.php?po_id=<?=$p['po_id']?>"><i class="fa fa-print"></i> พิมพ์ใบสั่งซื้อ</a>
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
  </div>
  <?php
  exit;
}

// =============== EDIT FORM ===============
if($action==='editform'){
  $id = (int)($_GET['id'] ?? 0);
  $p = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT * FROM purchase_orders WHERE po_id=$id"));
  $items = mysqli_query($objCon, "SELECT i.*, pr.product_name FROM purchase_order_items i 
    LEFT JOIN products pr ON pr.product_id=i.product_id WHERE i.po_id=$id");

  $sup = mysqli_query($objCon, "SELECT * FROM suppliers ORDER BY supplier_name ASC");
  $pro = mysqli_query($objCon, "SELECT * FROM products ORDER BY product_name ASC");
  ?>
  <form id="editForm">
  <div class="modal-header bg-warning">
    <h5 class="modal-title">✏️ แก้ไขใบสั่งซื้อ (PO-<?=h($p['po_id'])?>)</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">
    <input type="hidden" name="po_id" value="<?=$p['po_id']?>">
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">ผู้จำหน่าย</label>
        <select name="supplier_id" class="form-select" required>
          <?php while($s=mysqli_fetch_assoc($sup)){ $sel = ($s['supplier_id']==$p['supplier_id'])?'selected':''; ?>
            <option value="<?=$s['supplier_id']?>" <?=$sel?>><?=$s['supplier_name']?></option>
          <?php } ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">หมายเหตุ</label>
        <input type="text" name="note" class="form-control" value="<?=h($p['note'])?>">
      </div>
    </div>

    <table class="table table-bordered text-center align-middle">
      <thead class="table-light">
        <tr>
          <th>สินค้า</th><th>จำนวน</th><th>ราคาต่อหน่วย</th><th>รวม</th><th></th>
        </tr>
      </thead>
      <tbody id="editItemTable">
        <?php while($it=mysqli_fetch_assoc($items)){ ?>
          <tr>
            <td>
              <select name="product_id[]" class="form-select" required>
                <option value="">-- เลือกสินค้า --</option>
                <?php
                  mysqli_data_seek($pro, 0);
                  while($p2=mysqli_fetch_assoc($pro)){
                    $sel = ($p2['product_id']==$it['product_id'])?'selected':'';
                    echo "<option value='{$p2['product_id']}' {$sel}>{$p2['product_name']}</option>";
                  }
                ?>
              </select>
            </td>
            <td><input type="number" name="quantity[]" class="form-control text-center" value="<?=$it['quantity']?>" min="1"></td>
            <td><input type="number" step="0.01" name="unit_cost[]" class="form-control text-center" value="<?=$it['unit_cost']?>"></td>
            <td><input type="text" class="form-control text-center total" readonly value="<?=number_format($it['quantity']*$it['unit_cost'],2)?>"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="fa fa-trash"></i></button></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    <button type="button" id="editAddRow" class="btn btn-outline-success btn-sm"><i class="fa fa-plus"></i> เพิ่มสินค้า</button>
    <hr>
    <div class="text-end">
      <strong>ยอดรวมทั้งหมด: <span id="editGrandTotal">0.00</span> บาท</strong>
    </div>
  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
  </div>
  </form>

  <script>
  (function(){
    function calc(){
      let t=0;
      $('#editItemTable tr').each(function(){
        let q = parseFloat($(this).find('input[name="quantity[]"]').val())||0;
        let c = parseFloat($(this).find('input[name="unit_cost[]"]').val())||0;
        let s = q*c;
        $(this).find('.total').val(s.toFixed(2));
        t+=s;
      });
      $('#editGrandTotal').text(t.toFixed(2));
    }
    $('#editItemTable').on('input','input[name="quantity[]"],input[name="unit_cost[]"]',calc);
    calc();

    $('#editAddRow').click(function(){
      let row = $('#editItemTable tr:first').clone();
      row.find('select').val('');
      row.find('input').val('');
      row.find('.total').val('0.00');
      $('#editItemTable').append(row);
    });
    $('#editItemTable').on('click','.removeRow',function(){
      if($('#editItemTable tr').length>1){ $(this).closest('tr').remove(); calc(); }
    });

    $('#editForm').submit(function(e){
      e.preventDefault();
      $.ajax({
        url:'purchase_order_action.php?action=update',
        type:'POST',
        data: $(this).serialize(),
        success: function(res){ alert(res); location.reload(); }
      });
    });
  })();
  </script>
  <?php
  exit;
}

// =============== UPDATE (บันทึกแก้ไข + เพิ่มแถวใหม่) ===============
if($action==='update'){
  $po_id = (int)$_POST['po_id'];
  $supplier_id = (int)$_POST['supplier_id'];
  $note = mysqli_real_escape_string($objCon, $_POST['note'] ?? '');

  // ลบ items เก่าทิ้งก่อน แล้ว insert ใหม่ทั้งหมด (วิธีง่าย/ปลอดภัย)
  mysqli_query($objCon, "UPDATE purchase_orders SET supplier_id=$supplier_id, note='$note' WHERE po_id=$po_id");
  mysqli_query($objCon, "DELETE FROM purchase_order_items WHERE po_id=$po_id");

  $total = 0;
  if(!empty($_POST['product_id'])){
    foreach($_POST['product_id'] as $i=>$pid){
      $pid = (int)$pid; $qty=(int)($_POST['quantity'][$i]??0); $cost=(float)($_POST['unit_cost'][$i]??0);
      if($pid>0 && $qty>0){
        mysqli_query($objCon, "INSERT INTO purchase_order_items (po_id, product_id, quantity, unit_cost)
                               VALUES ($po_id, $pid, $qty, $cost)");
        $total += $qty*$cost;
      }
    }
  }
  mysqli_query($objCon, "UPDATE purchase_orders SET total_amount=$total WHERE po_id=$po_id");
  echo "✅ อัปเดตใบสั่งซื้อเรียบร้อย (PO-$po_id)";
  exit;
}

// =============== APPROVE ===============
if($action==='approve'){
  $id = (int)($_POST['id'] ?? 0);
  // อนุญาตเฉพาะรออนุมัติ
  $st = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT status FROM purchase_orders WHERE po_id=$id"))['status'] ?? '';
  if($st!=='รออนุมัติ'){ echo "ไม่สามารถอนุมัติได้ (สถานะปัจจุบัน: $st)"; exit; }

  mysqli_query($objCon, "UPDATE purchase_orders SET status='สั่งซื้อแล้ว' WHERE po_id=$id");
  echo "✅ อนุมัติสำเร็จ (PO-$id → สั่งซื้อแล้ว)";
  exit;
}

// =============== RECEIVE (นำเข้า Stock) ===============
if($action==='receive'){
  $id = (int)($_POST['id'] ?? 0);
  $row = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT * FROM purchase_orders WHERE po_id=$id"));
  if(!$row){ echo "ไม่พบ PO นี้"; exit; }
  if($row['status']!=='สั่งซื้อแล้ว'){ echo "นำเข้าได้เฉพาะสถานะ 'สั่งซื้อแล้ว' เท่านั้น"; exit; }

  $clinic_id = (int)$row['clinic_id'];
  $user_id = (int)($_SESSION['user_id'] ?? 0);

  $items = mysqli_query($objCon, "SELECT * FROM purchase_order_items WHERE po_id=$id");
  while($it = mysqli_fetch_assoc($items)){
    $pid = (int)$it['product_id']; $qty=(int)$it['quantity']; $cost=(float)$it['unit_cost'];
    // อัปเดต stock
    mysqli_query($objCon, "UPDATE products SET stock_qty = stock_qty + $qty WHERE product_id=$pid");
    // สมุดรายวันคลัง
    mysqli_query($objCon, "INSERT INTO stock_transactions (product_id, clinic_id, user_id, trans_type, quantity, reference_no, note, created_at)
                           VALUES ($pid, $clinic_id, $user_id, 'IN', $qty, CONCAT('PO-',$id), 'รับของจากใบสั่งซื้อ', NOW())");
  }
  mysqli_query($objCon, "UPDATE purchase_orders SET status='ได้รับของแล้ว' WHERE po_id=$id");
  echo "📦 รับของเข้าคลังเรียบร้อย (PO-$id)";
  exit;
}

// =============== DELETE ===============
if($action==='delete'){
  $id = (int)($_POST['id'] ?? 0);
  $st = mysqli_fetch_assoc(mysqli_query($objCon, "SELECT status FROM purchase_orders WHERE po_id=$id"))['status'] ?? '';
  if($st==='ได้รับของแล้ว'){ echo "ลบไม่ได้: เอกสารถูกรับเข้าคลังแล้ว"; exit; }

  mysqli_query($objCon, "DELETE FROM purchase_order_items WHERE po_id=$id");
  mysqli_query($objCon, "DELETE FROM purchase_orders WHERE po_id=$id");
  echo "🗑️ ลบใบสั่งซื้อเรียบร้อย (PO-$id)";
  exit;
}

echo "No action.";
