let mix = require("laravel-mix");
let NovaExtension = require("laravel-nova-devtool");
let tailwindcss = require("tailwindcss");

mix.extend("nova", new NovaExtension());

mix.setPublicPath("dist")
    .js("resources/js/card.js", "js")
    .vue({ version: 3 })
    .postCss("resources/css/card.css", "css", [
        tailwindcss("tailwind.config.js"),
    ])
    .nova("gabrielesbaiz/nova-card-rss-news")
    .version();
