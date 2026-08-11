const XLSX = require('xlsx');
const fs = require('fs');
const workbook = XLSX.readFile('tracker konsolidasi.xlsx');
const sheetName = workbook.SheetNames[0];
const sheet = workbook.Sheets[sheetName];
const data = XLSX.utils.sheet_to_json(sheet, {header: 1});
fs.writeFileSync('storage/app/tracker.json', JSON.stringify(data, null, 2));
