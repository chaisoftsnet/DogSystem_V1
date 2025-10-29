<?php
@session_start();
require_once('dbconnect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

// ดึงข้อมูลลูกค้า
$user_q = mysqli_query($objCon, "SELECT id, fullname FROM user WHERE role=1 OR role=0 ORDER BY fullname ASC");

// ดึงข้อมูลสุนัขทั้งหมด (พร้อมชื่อเจ้าของ)
$dog_q = mysqli_query($objCon, "
  SELECT d.dog_id, d.dog_name, u.fullname 
  FROM dogs d 
  LEFT JOIN user u ON d.user_id = u.id 
  ORDER BY d.dog_name
");

// ดึงสินค้า/บริการทั้งหมด
$prod_q = mysqli_query($objCon, "SELECT * FROM products ORDER BY category, product_name");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>🧾 ออกใบแจ้งหนี้ใหม่ | ระบบคลินิกรักษาสัตว์</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; transition: 0.3s; }
.dark-mode { background-color: #121212; color: #f1f1f1; }
.card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.btn-add-row { background: linear-gradient(45deg, #00c853, #009624); color: white; border: none; }
.btn-add-row:hover { opacity: 0.9; }
.btn-del-row { color: red; }
.table td, .table th { vertical-align: middle; }
.toggle-dark { cursor: pointer; color: #00c853; font-size: 20px; }
</style>
</head>
<link rel="stylesheet" href="css/theme.css">
<script src="js/theme.js"></script>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🧾 ออกใบแจ้งหนี้ใหม่</h3>
    <div>
      <i class="fa fa-moon toggle-dark me-3" onclick="toggleDarkMode()"></i>
      <a href="invoice_manage.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> กลับ</a>
    </div>
  </div>

  <div class="card p-4">
    <form id="invoiceForm">
      <div class="row g-3">
        <div class="col-md-4">
          <label>เลือกลูกค้า</label>
          <select name="user_id" id="user_id" class="form-select" required>
            <option value="">-- เลือกลูกค้า --</option>
            <?php while($u=mysqli_fetch_assoc($user_q)){ ?>
              <option value="<?=$u['id']?>"><?=$u['fullname']?></option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-4">
          <label>เลือกสุนัข</label>
          <select name="dog_id" id="dog_id" class="form-select" required>
            <option value="">-- เลือกสุนัข --</option>
            <?php while($d=mysqli_fetch_assoc($dog_q)){ ?>
              <option value="<?=$d['dog_id']?>"><?=$d['dog_name']?> (<?=$d['fullname']?>)</option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-4">
          <label>ช่องทางชำระเงิน</label>
          <select name="payment_method" class="form-select">
            <option value="เงินสด">เงินสด</option>
            <option value="โอน">โอน</option>
            <option value="บัตรเครดิต">บัตรเครดิต</option>
            <option value="PromptPay">PromptPay</option>
          </select>
        </div>

        <div class="col-12">
          <label>หมายเหตุ</label>
          <textarea name="note" class="form-control" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
        </div>
      </div>

      <hr>
      <h5>🧩 รายการสินค้า / บริการ</h5>
      <table class="table table-bordered text-center" id="itemTable">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>สินค้า/บริการ</th>
            <th>จำนวน</th>
            <th>ราคาต่อหน่วย</th>
            <th>รวม</th>
            <th>ลบ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>
              <select name="product_id[]" class="form-select product-select">
                <option value="">-- เลือกสินค้า/บริการ --</option>
                <?php
                  mysqli_data_seek($prod_q, 0);
                  while($p=mysqli_fetch_assoc($prod_q)){
                    echo "<option value='{$p['product_id']}' data-price='{$p['unit_price']}'>{$p['product_name']} ({$p['category']})</option>";
                  }
                ?>
              </select>
            </td>
            <td><input type="number" name="qty[]" class="form-control qty" value="1" min="1"></td>
            <td><input type="number" name="price[]" class="form-control price" step="0.01" value="0.00"></td>
            <td class="line-total">0.00</td>
            <td><button type="button" class="btn btn-del-row"><i class="fa fa-trash"></i></button></td>
          </tr>
        </tbody>
      </table>
      <button type="button" class="btn btn-add-row mb-3"><i class="fa fa-plus"></i> เพิ่มรายการ</button>

      <h5 class="text-end">ยอดรวมทั้งหมด: <span id="grandTotal" class="text-success fw-bold">0.00</span> บาท</h5>

      <div class="text-center mt-3">
        <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i> บันทึกใบแจ้งหนี้</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function(){
  // ✅ เพิ่มแถวสินค้า
  $('.btn-add-row').click(function(){
    let rowCount = $('#itemTable tbody tr').length + 1;
    let options = $('.product-select:first').html();
    $('#itemTable tbody').append(`
      <tr>
        <td>${rowCount}</td>
        <td><select name="product_id[]" class="form-select product-select">${options}</select></td>
        <td><input type="number" name="qty[]" class="form-control qty" value="1" min="1"></td>
        <td><input type="number" name="price[]" class="form-control price" step="0.01" value="0.00"></td>
        <td class="line-total">0.00</td>
        <td><button type="button" class="btn btn-del-row"><i class="fa fa-trash"></i></button></td>
      </tr>
    `);
  });

  // ✅ ลบแถว
  $(document).on('click', '.btn-del-row', function(){
    $(this).closest('tr').remove();
    calcTotal();
  });

  // ✅ เมื่อเปลี่ยนสินค้า → ดึงราคา
  $(document).on('change', '.product-select', function(){
    let price = $(this).find(':selected').data('price') || 0;
    $(this).closest('tr').find('.price').val(price);
    calcTotal();
  });

  // ✅ คำนวณยอดรวมทุกครั้งที่ qty หรือ price เปลี่ยน
  $(document).on('input', '.qty, .price', function(){
    calcTotal();
  });

  function calcTotal(){
    let total = 0;
    $('#itemTable tbody tr').each(function(){
      let qty = parseFloat($(this).find('.qty').val()) || 0;
      let price = parseFloat($(this).find('.price').val()) || 0;
      let sum = qty * price;
      $(this).find('.line-total').text(sum.toFixed(2));
      total += sum;
    });
    $('#grandTotal').text(total.toFixed(2));
  }

  // ✅ บันทึกใบแจ้งหนี้
  $('#invoiceForm').submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
      url: 'invoice_action.php?action=add',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(res){
        if(res.includes('Invoice ID:')){
          const id = res.split('Invoice ID:')[1].trim();
          alert('✅ บันทึกใบแจ้งหนี้เรียบร้อยแล้ว');
          window.location.href = 'invoice_print.php?invoice_id=' + id;
        } else {
          alert(res);
        }
      },
      error: function(){
        alert('❌ เกิดข้อผิดพลาดในการบันทึกข้อมูล');
      }
    });
  });
});

// 🌙 Toggle Mode
function toggleDarkMode(){ document.body.classList.toggle('dark-mode'); }
</script>
</body>
</html>
