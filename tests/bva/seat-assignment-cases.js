// Read the same versioned assignment as SeatAssignmentTest.php.
const fs = require('fs');
const path = require('path');
const report = fs.readFileSync(path.join(__dirname, '../../docs/testing/modules/seat/083205006374_LuongQuocAn_BaoCao_Seat_Form_Assignment_Chot.md'), 'utf8');
const section = report.split('# 4. METHOD 1')[1].split('# 5. METHOD 2')[0];
const cases = section.split(/\r?\n/).filter(line => /^\| (BVA|EP)-\d{2} \|/.test(line)).map(line => {
  const cells = line.slice(1, -1).split('|').map(cell => cell.trim());
  const body = { room_id: Number(cells[2]), seat_row: cells[3], seat_number: Number(cells[4]), seat_type_id: Number(cells[5]), is_active: cells[6] === 'true' };
  const field = (cells[1].match(/`(\w+)/) || [null, 'seat_number'])[1];
  return {
    id: `SEAT-VALIDATE-${cells[0]}`,
    serviceMethod: 'validateSeatInput',
    field, value: body[field], body,
    position: cells[cells.length - 1], boundary: cells[cells.length - 1],
    expected: cells[cells.length - 2].startsWith('**Không hợp lệ**') ? 'error' : 'success'
  };
});
if (cases.length !== 18 || new Set(cases.map(c => c.id)).size !== 18) {
  throw new Error('Seat assignment must contain 18 distinct validation cases');
}
module.exports = cases;
