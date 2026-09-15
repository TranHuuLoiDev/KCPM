const fs = require('fs');
const path = require('path');

module.exports = function assignmentValidationCases(module) {
  const text = fs.readFileSync(path.join(__dirname, `../../docs/testing/modules/${module.toLowerCase()}/${module}_BaoCao_Form_Assignment.md`), 'utf8');
  const section = text.match(/## 4\.4\. Bước 3[^\n]*\n([\s\S]*?)(?=## 4\.5\.)/)[1];
  let headers;
  const cases = [];
  for (const line of section.split(/\r?\n/)) {
    const cells = line.trim().slice(1,-1).split('|').map(x=>x.trim());
    if (/^\| STT\s*\|/.test(line)) headers = cells;
    if (!/^\| (LB|BVA|EP)-\d{2}\s*\|/.test(line)) continue;
    const row = Object.fromEntries(headers.map((h,i)=>[h,cells[i]]));
    const value = s => {
      s = s.replace(/`/g,'');
      if (/^'.*'$/.test(s)) return s.slice(1,-1);
      if (/^-?\d+$/.test(s)) return Number(s);
      if (s==='true') return true;
      return s;
    };
    const names = module==='Room' ? ['name','theatre_id','total_seats','is_active'] : ['rating'];
    const body = Object.fromEntries(names.map(n=>[n,value(row[n])]));
    const field = (row['Test case'].match(/`(\w+)/)||[])[1] || (module==='Room'?'name':'rating');
    const serviceExpected = row['Kết quả mong đợi'].startsWith('**Không hợp lệ**')?'error':'success';
    // Room API trims name; whitespace differs from a direct service call.
    const trimsEmpty = module==='Room' && typeof body.name==='string' && body.name.trim()==='';
    const expected = trimsEmpty?'error':serviceExpected;
    let expectedMessage = expected==='success' ? (module==='Room'?'Dữ liệu phòng hợp lệ!':'Dữ liệu đánh giá hợp lệ!')
      : row['Kết quả mong đợi'].replace(/^\*\*Không hợp lệ\*\* – /,'').split(';')[0];
    if (trimsEmpty) expectedMessage='Tên phòng không được để trống!';
    cases.push({id:`${module.toUpperCase()}-VALIDATE-${row.STT}`,serviceMethod:`validate${module}Input`,field,
      value:body[field],body,expected,serviceExpected,expectedMessage,position:row.Tag,boundary:row.Tag});
  }
  const count = module==='Room'?13:8;
  if(cases.length!==count || new Set(cases.map(c=>c.id)).size!==count) throw Error(`Invalid ${module} assignment design`);
  return cases;
};
