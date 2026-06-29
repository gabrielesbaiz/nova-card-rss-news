import Card from "./components/Card";
import CardSelect from "./components/CardSelect";

Nova.booting((app, store) => {
    app.component("nova-card-rss-news", Card);
    app.component("nova-card-rss-news-select", CardSelect);
});
