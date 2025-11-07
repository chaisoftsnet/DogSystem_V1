<?php
@session_start();
require_once('dbconnect.php');

function j($s){ echo $s; exit; }
function esc($c,$s){ return mysqli_real_escape_string($c,$s); }

// ปรับสต๊อก + บันทึกสมุดรายวัน
function adjust_stock($objCon, $product_id, $clinic_id, $user_id, $type, $qty, $ref, $note=''){
  $qty = (int)$qty;
  if($qty<=0) return true;

  // อ่านคงเหลือ
  $r = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT stock_qty,product_name FROM products WHERE product_id=".(int)$product_id));
  if(!$r) return "ไม่พบสินค้า";
  $balance = (int)$r['stock_qty'];

  if($type==='OUT' && $balance < $qty){
    return "สต๊อกไม่พอสำหรับตัดออก (คงเหลือ $balance, ต้องการ $qty)";
  }

  $new = ($type==='IN') ? ($balance+$qty) : max(0, $balance-$qty);
  mysqli_query($objCon,"UPDATE products SET stock_qty=$new WHERE product_id=".(int)$product_id);

  $ref = esc($objCon,$ref);
  $note= esc($objCon,$note);
  $type= esc($objCon,$type);
  $uid = (int)($_SESSION['user_id'] ?? 0);
  $sql = "INSERT INTO stock_transactions(product_id, clinic_id, user_id, trans_type, quantity, reference_no, note, created_at)
          VALUES($product_id, $clinic_id, $uid, '$type', $qty, '$ref', '$note', NOW())";
  mysqli_query($objCon,$sql);

  return true;
}

// ---------------- Router ----------------
$action = $_REQUEST['action'] ?? '';

// 1) สร้างใบแจ้งหนี้ (สถานะเริ่มต้น: รอชำระ)
if($action==='add_invoice'){
  $clinic_id = (int)($_POST['clinic_id'] ?? 0);
  $dog_id    = (int)($_POST['dog_id'] ?? 0);
  $payment   = esc($objCon,$_POST['payment_method'] ?? 'เงินสด');
  $note      = esc($objCon,$_POST['note'] ?? '');
  $user_id   = (int)($_SESSION['user_id'] ?? 0);

  $sql = "INSERT INTO invoices(clinic_id,user_id,dog_id,invoice_date,total_amount,status,payment_method,note)
          VALUES($clinic_id,$user_id,$dog_id,NOW(),0.00,'รอชำระ','$payment','$note')";
  if(!mysqli_query($objCon,$sql)) j("บันทึกบิลไม่สำเร็จ: ".mysqli_error($objCon));
  $id = mysqli_insert_id($objCon);
  j("OK|$id");
}

// 2) เพิ่มรายการเข้าใบแจ้งหนี้ใหม่ (จาก modal “สร้าง”)
if($action==='add_item'){
  $invoice_id = (int)($_POST['invoice_id'] ?? 0);
  $product_id = (int)($_POST['product_id'] ?? 0);
  $desc       = esc($objCon, $_POST['description'] ?? '');
  $qty        = (float)($_POST['quantity'] ?? 1);
  $price      = (float)($_POST['unit_price'] ?? 0);

  $sql = "INSERT INTO invoice_items(invoice_id, description, quantity, unit_price) 
          VALUES($invoice_id,'$desc',$qty,$price)";
  if(!mysqli_query($objCon,$sql)) j("เพิ่มรายการไม่สำเร็จ: ".mysqli_error($objCon));

  // อัพเดทยอดรวม
  mysqli_query($objCon,"UPDATE invoices i
                        JOIN (SELECT SUM(quantity*unit_price) s FROM invoice_items WHERE invoice_id=$invoice_id) t
                        SET i.total_amount=IFNULL(t.s,0)
                        WHERE i.invoice_id=$invoice_id");
  j("OK");
}

// 3) เปิด modal ดู/แก้ไขใบแจ้งหนี้
if($action==='fetch_invoice'){
  $invoice_id = (int)($_GET['invoice_id'] ?? 0);

  $h = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT i.*, u.fullname, d.dog_name, c.clinic_name
      FROM invoices i 
      LEFT JOIN user u ON i.user_id=u.id
      LEFT JOIN dogs d ON i.dog_id=d.dog_id
      LEFT JOIN clinics c ON i.clinic_id=c.clinic_id
      WHERE i.invoice_id=$invoice_id"));
  if(!$h){ j("<div class='p-4'>ไม่พบบิล</div>"); }

  $items = mysqli_query($objCon,"SELECT it.*, p.product_id
        FROM invoice_items it
        LEFT JOIN products p ON p.product_name=it.description
        WHERE it.invoice_id=$invoice_id
        ORDER BY it.item_id ASC");

  // ดึงสินค้าสำหรับเพิ่มรายการใหม่
  $products = mysqli_query($objCon,"SELECT product_id, product_name, unit_price FROM products ORDER BY category, product_name");

  ob_start(); ?>
  <div class="modal-header bg-primary text-white">
    <h5 class="modal-title">แก้ไขใบแจ้งหนี้ #<?=$h['invoice_id']?></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">
    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <p class="mb-1"><b>คลินิก:</b> <?=$h['clinic_name']?></p>
        <p class="mb-1"><b>ผู้ทำบิล:</b> <?=$h['fullname']?></p>
        <p class="mb-1"><b>สุนัข:</b> <?=$h['dog_name']?></p>
      </div>
      <div class="col-md-6 text-md-end">
        <p class="mb-1"><b>วันที่:</b> <?=date('d/m/Y H:i',strtotime($h['invoice_date']))?></p>
        <label class="mb-1"><b>สถานะ:</b></label>
        <select id="invoiceStatus" class="form-select d-inline-block w-auto ms-2" data-invoice="<?=$h['invoice_id']?>">
          <?php
            $ops = ['รอชำระ','ชำระแล้ว','ยกเลิก'];
            foreach($ops as $op){
              $sel = ($op==$h['status'])?'selected':'';
              echo "<option $sel>$op</option>";
            }
          ?>
        </select>
      </div>
    </div>
    <hr>

    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:40%">สินค้า</th>
            <th style="width:12%">จำนวน</th>
            <th style="width:16%">ราคา/หน่วย</th>
            <th style="width:16%">รวม</th>
            <th style="width:16%">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $sum = 0;
          while($it=mysqli_fetch_assoc($items)){ 
            $rowTotal = (float)$it['quantity']*(float)$it['unit_price']; $sum += $rowTotal; ?>
            <tr data-id="<?=$it['item_id']?>" data-invoice="<?=$h['invoice_id']?>">
              <td class="text-start"><?=htmlspecialchars($it['description'])?></td>
              <td><input type="number" class="form-control form-control-sm itemQty" value="<?=$it['quantity']?>" min="1"></td>
              <td><input type="number" step="0.01" class="form-control form-control-sm itemPrice" value="<?=$it['unit_price']?>"></td>
              <td><?=number_format($rowTotal,2)?></td>
              <td>
                <button class="btn btn-danger btn-sm delItemBtn" data-id="<?=$it['item_id']?>" data-invoice="<?=$h['invoice_id']?>"><i class="fa fa-trash"></i></button>
              </td>
            </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">ยอดรวม</th>
            <th class="text-center"><?=number_format($sum,2)?></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <div id="addItemWrap" class="border rounded p-2">
      <div class="row g-2">
        <div class="col-md-6">
          <select class="form-select" name="product_id">
            <option value="">-- เพิ่มสินค้าเข้าใบนี้ --</option>
            <?php while($p=mysqli_fetch_assoc($products)){
              $price = $p['unit_price']+0;
              echo "<option value='{$p['product_id']}' data-price='{$price}'>{$p['product_name']} (".number_format($price,2).")</option>";
            } ?>
          </select>
        </div>
        <div class="col-md-2"><input type="number" class="form-control" name="quantity" value="1" min="1"></div>
        <div class="col-md-2"><input type="number" class="form-control" name="unit_price" step="0.01" value="0.00"></div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-outline-success" id="addItemExisting" type="button"
                  data-invoice="<?=$h['invoice_id']?>" data-clinic="<?=$h['clinic_id']?>">
            <i class="fa fa-plus"></i> เพิ่ม
          </button>
        </div>
      </div>
      <small class="text-muted d-block mt-1">* หากสถานะเป็น “ชำระแล้ว” ระบบจะตัดสต๊อกทันที</small>
    </div>

  </div>
  <div class="modal-footer">
    <a class="btn btn-info" href="invoice_print.php?invoice_id=<?=$h['invoice_id']?>" target="_blank"><i class="fa fa-print"></i> พิมพ์</a>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
  </div>
  <?php
  $html = ob_get_clean();
  j($html);
}

// 4) เพิ่มรายการใหม่เข้าบิลที่มีอยู่ (และถ้าบิล “ชำระแล้ว” ให้ตัดสต๊อกทันที)
if($action==='add_item_existing'){
  $invoice_id = (int)($_POST['invoice_id'] ?? 0);
  $product_id = (int)($_POST['product_id'] ?? 0);
  $qty        = (float)($_POST['quantity'] ?? 1);
  $price      = (float)($_POST['unit_price'] ?? 0);
  $clinic_id  = (int)($_POST['clinic_id'] ?? 0);

  // หาชื่อสินค้า
  $p = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT product_name, unit_price FROM products WHERE product_id=$product_id"));
  if(!$p) j("ไม่พบสินค้า");
  $desc = esc($objCon,$p['product_name']);
  if($price<=0) $price = (float)$p['unit_price'];

  // แทรก
  $sql = "INSERT INTO invoice_items(invoice_id, description, quantity, unit_price) 
          VALUES($invoice_id,'$desc',$qty,$price)";
  if(!mysqli_query($objCon,$sql)) j("เพิ่มรายการไม่สำเร็จ: ".mysqli_error($objCon));

  // อัปเดทยอดรวม
  mysqli_query($objCon,"UPDATE invoices i
                        JOIN (SELECT SUM(quantity*unit_price) s FROM invoice_items WHERE invoice_id=$invoice_id) t
                        SET i.total_amount=IFNULL(t.s,0)
                        WHERE i.invoice_id=$invoice_id");

  // ถ้าสถานะเป็นชำระแล้ว ตัดสต๊อก
  $h = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT status FROM invoices WHERE invoice_id=$invoice_id"));
  if($h && $h['status']=='ชำระแล้ว'){
    $res = adjust_stock($objCon,$product_id,$clinic_id,($_SESSION['user_id']??0),'OUT',$qty,"INV#$invoice_id","ขายเพิ่มภายหลัง");
    if($res!==true) j($res);
  }
  j("OK");
}

// 5) อัปเดตรายการ (qty/price) ของบิลที่มีอยู่
if($action==='update_invoice_item'){
  $item_id = (int)($_POST['item_id'] ?? 0);
  $qty     = (float)($_POST['quantity'] ?? 1);
  $price   = (float)($_POST['unit_price'] ?? 0);

  // อ่านข้อมูลเดิมเพื่อคำนวนส่วนต่างสต๊อก (ถ้าบิลชำระแล้ว)
  $it = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT ii.*, i.status, i.invoice_id
    FROM invoice_items ii JOIN invoices i ON i.invoice_id=ii.invoice_id WHERE ii.item_id=$item_id"));
  if(!$it) j("ไม่พบรายการ");

  // แก้ไข
  mysqli_query($objCon,"UPDATE invoice_items SET quantity=$qty, unit_price=$price WHERE item_id=$item_id");

  // อัพเดทยอดใบ
  $inv = (int)$it['invoice_id'];
  mysqli_query($objCon,"UPDATE invoices i
                        JOIN (SELECT SUM(quantity*unit_price) s FROM invoice_items WHERE invoice_id=$inv) t
                        SET i.total_amount=IFNULL(t.s,0)
                        WHERE i.invoice_id=$inv");

  // หากสถานะเป็นชำระแล้ว → ปรับสต๊อกตามส่วนต่าง qty
  if($it['status']=='ชำระแล้ว'){
    // พยายามจับ product_id จากชื่อ
    $p = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT product_id FROM products WHERE product_name='".esc($objCon,$it['description'])."' LIMIT 1"));
    if($p){
      $old = (float)$it['quantity'];
      $diff = $qty - $old;
      if($diff!=0){
        $type = $diff>0 ? 'OUT' : 'IN';
        $res = adjust_stock($objCon,(int)$p['product_id'],1,($_SESSION['user_id']??0),$type,abs($diff),"INV#$inv","แก้ไขจำนวนหลังชำระ");
        if($res!==true) j($res);
      }
    }
  }
  j("OK");
}

// 6) ลบรายการออกจากบิล (และถ้าชำระแล้ว ให้คืนสต๊อก)
if($action==='delete_item'){
  $item_id   = (int)($_POST['item_id'] ?? 0);
  $clinic_id = (int)($_POST['clinic_id'] ?? 0);

  $it = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT ii.*, i.status 
    FROM invoice_items ii JOIN invoices i ON i.invoice_id=ii.invoice_id WHERE ii.item_id=$item_id"));
  if(!$it) j("ไม่พบรายการ");

  // ถ้าบิลชำระแล้ว → คืนสต๊อกตาม qty
  if($it['status']=='ชำระแล้ว'){
    $p = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT product_id FROM products WHERE product_name='".esc($objCon,$it['description'])."' LIMIT 1"));
    if($p){
      $res = adjust_stock($objCon,(int)$p['product_id'], $clinic_id, ($_SESSION['user_id']??0), 'IN',(float)$it['quantity'],"INV#{$it['invoice_id']}","ลบรายการหลังชำระ");
      if($res!==true) j($res);
    }
  }

  mysqli_query($objCon,"DELETE FROM invoice_items WHERE item_id=$item_id");
  // อัพเดทยอดรวม
  $inv = (int)$it['invoice_id'];
  mysqli_query($objCon,"UPDATE invoices i
                        JOIN (SELECT SUM(quantity*unit_price) s FROM invoice_items WHERE invoice_id=$inv) t
                        SET i.total_amount=IFNULL(t.s,0)
                        WHERE i.invoice_id=$inv");
  j("ลบรายการสำเร็จ");
}

// 7) เปลี่ยนสถานะบิล (รอชำระ ↔ ชำระแล้ว ↔ ยกเลิก) และปรับสต๊อก
if($action==='update_invoice_status'){
  $invoice_id = (int)($_POST['invoice_id'] ?? 0);
  $status     = esc($objCon,$_POST['status'] ?? 'รอชำระ');
  $clinic_id  = (int)($_POST['clinic_id'] ?? 0);

  $h = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT status FROM invoices WHERE invoice_id=$invoice_id"));
  if(!$h) j("ไม่พบบิล");
  $old = $h['status'];

  if($old===$status){ j("สถานะเดิมอยู่แล้ว"); }

  // อ่านรายการทั้งหมด
  $items = mysqli_query($objCon,"SELECT * FROM invoice_items WHERE invoice_id=$invoice_id");

  // กรณีจาก รอชำระ -> ชำระแล้ว : ตัดสต๊อก OUT
  if($old==='รอชำระ' && $status==='ชำระแล้ว'){
    while($it=mysqli_fetch_assoc($items)){
      $p = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT product_id FROM products WHERE product_name='".esc($objCon,$it['description'])."' LIMIT 1"));
      if($p){
        $res = adjust_stock($objCon,(int)$p['product_id'],$clinic_id,($_SESSION['user_id']??0),'OUT',(float)$it['quantity'],"INV#$invoice_id","ชำระแล้ว");
        if($res!==true) j($res);
      }
    }
  }

  // กรณีจาก ชำระแล้ว -> รอชำระ : คืนสต๊อก IN
  if($old==='ชำระแล้ว' && $status==='รอชำระ'){
    while($it=mysqli_fetch_assoc($items)){
      $p = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT product_id FROM products WHERE product_name='".esc($objCon,$it['description'])."' LIMIT 1"));
      if($p){
        $res = adjust_stock($objCon,(int)$p['product_id'],$clinic_id,($_SESSION['user_id']??0),'IN',(float)$it['quantity'],"INV#$invoice_id","เปลี่ยนกลับเป็นรอชำระ");
        if($res!==true) j($res);
      }
    }
  }

  // อัปเดตสถานะ
  mysqli_query($objCon,"UPDATE invoices SET status='$status' WHERE invoice_id=$invoice_id");
  j("เปลี่ยนสถานะเป็น: $status เรียบร้อย");
}

// 8) ลบบิลทั้งใบ (ถ้าชำระแล้ว → คืนสต๊อกทุกรายการ)
if($action==='delete_invoice'){
  $invoice_id = (int)($_POST['invoice_id'] ?? 0);
  $clinic_id  = (int)($_POST['clinic_id'] ?? 0);

  $h = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT status FROM invoices WHERE invoice_id=$invoice_id"));
  if(!$h) j("ไม่พบบิล");

  if($h['status']==='ชำระแล้ว'){
    $items = mysqli_query($objCon,"SELECT * FROM invoice_items WHERE invoice_id=$invoice_id");
    while($it=mysqli_fetch_assoc($items)){
      $p = mysqli_fetch_assoc(mysqli_query($objCon,"SELECT product_id FROM products WHERE product_name='".esc($objCon,$it['description'])."' LIMIT 1"));
      if($p){
        $res = adjust_stock($objCon,(int)$p['product_id'], $clinic_id, ($_SESSION['user_id']??0), 'IN', (float)$it['quantity'],"INV#$invoice_id","ลบบิลที่ชำระแล้ว");
        if($res!==true) j($res);
      }
    }
  }
  mysqli_query($objCon,"DELETE FROM invoice_items WHERE invoice_id=$invoice_id");
  mysqli_query($objCon,"DELETE FROM invoices WHERE invoice_id=$invoice_id");
  j("ลบใบแจ้งหนี้เรียบร้อย");
}

j("ไม่มี action ที่รองรับ");
