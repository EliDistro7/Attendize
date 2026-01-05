const fs = require('fs');
const path = require('path');

// Simple concatenation of JS files for landing page
const files = [
    './node_modules/alpinejs/dist/cdn.min.js',
    './node_modules/aos/dist/aos.js',
    './node_modules/swiper/swiper-bundle.min.js',
    './resources/js/landing.js'
];

let output = '';

files.forEach(file => {
    if (fs.existsSync(file)) {
        output += fs.readFileSync(file, 'utf8') + '\n';
    }
});

fs.writeFileSync('./public/js/landing.js', output);
console.log('Landing JS built successfully!');