// ClubConnect - ตรวจไฟล์แนบฝั่ง client (ตรวจซ้ำฝั่ง server ใน PHP อีกชั้น)
// กติกา: PDF/JPG/JPEG/PNG และไม่เกิน 10MB
document.addEventListener('DOMContentLoaded', function () {
  var input = document.getElementById('document');
  var form = document.getElementById('applyForm');
  if (!input) return;

  var allowed = ['pdf', 'jpg', 'jpeg', 'png'];
  var maxSize = 10 * 1024 * 1024;

  function checkFile() {
    var msg = document.getElementById('fileMsg');
    if (!input.files || input.files.length === 0) return true;
    var f = input.files[0];
    var ext = (f.name.split('.').pop() || '').toLowerCase();
    if (allowed.indexOf(ext) === -1) {
      if (msg) msg.textContent = 'รองรับเฉพาะไฟล์ PDF, JPG, JPEG, PNG';
      alert('ประเภทไฟล์ไม่รองรับ (รองรับ PDF/JPG/PNG)');
      input.value = '';
      return false;
    }
    if (f.size > maxSize) {
      if (msg) msg.textContent = 'ขนาดไฟล์ต้องไม่เกิน 10MB';
      alert('ขนาดไฟล์ต้องไม่เกิน 10MB');
      input.value = '';
      return false;
    }
    if (msg) msg.textContent = 'ไฟล์: ' + f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
    return true;
  }

  input.addEventListener('change', checkFile);
  if (form) {
    form.addEventListener('submit', function (ev) {
      // ตรวจ required เบื้องต้น (HTML5 required ช่วยแล้ว แต่ตรวจซ้ำเพื่อ highlight)
      var required = form.querySelectorAll('[name="student_name"],[name="student_id"],[name="faculty"],[name="year"],[name="phone"],[name="reason"]');
      var ok = true;
      required.forEach(function (el) {
        if (!el.value.trim()) {
          el.classList.add('invalid');
          ok = false;
        } else {
          el.classList.remove('invalid');
        }
      });
      if (!checkFile()) ok = false;
      if (!ok) {
        ev.preventDefault();
        alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบก่อนส่ง');
      }
    });
  }
});
