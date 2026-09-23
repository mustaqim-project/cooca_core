const fs = require('fs');
const content = fs.readFileSync('Index.html', 'utf8');
const lines = content.split('\n');

const escapedSingles = [];
const escapedDoubles = [];

lines.forEach((line, idx) => {
  const lineNum = idx + 1;
  if (line.includes("\\'")) {
    escapedSingles.push({ lineNum, line: line.trim() });
  }
  if (line.includes('\\"')) {
    escapedDoubles.push({ lineNum, line: line.trim() });
  }
});

console.log('Total lines with \\\':', escapedSingles.length);
escapedSingles.forEach(x => console.log('Line ' + x.lineNum + ': ' + x.line.substring(0, 110)));

console.log('\nTotal lines with \\":', escapedDoubles.length);
escapedDoubles.forEach(x => console.log('Line ' + x.lineNum + ': ' + x.line.substring(0, 110)));
