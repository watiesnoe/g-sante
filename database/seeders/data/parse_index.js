const fs = require('fs');
const path = require('path');

const indexPath = path.join(__dirname, '../../../public/index.html');
const jsonPath = path.join(__dirname, 'guide_therapeutique.json');

if (!fs.existsSync(indexPath)) {
    console.error("Error: index.html not found at " + indexPath);
    process.exit(1);
}

const html = fs.readFileSync(indexPath, 'utf8');
const regex = /const DATA = \s*([\s\S]*?);\s*\n\s*const chapterOrder/m;
const match = html.match(regex);

if (!match) {
    console.error("Error: DATA array not found in index.html");
    process.exit(1);
}

let data;
try {
    // Safely evaluate the array expression
    data = eval(match[1]);
} catch (e) {
    console.error("Error: Failed to parse DATA array: ", e);
    process.exit(1);
}

fs.writeFileSync(jsonPath, JSON.stringify(data, null, 4), 'utf8');
console.log("Successfully extracted " + data.length + " entries to guide_therapeutique.json");
