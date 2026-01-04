const mix = require('laravel-mix');

mix.js('resources/js/ticket-pdf.js', 'public/js')
   .js('resources/js/app.js', 'public/js')
   .version();