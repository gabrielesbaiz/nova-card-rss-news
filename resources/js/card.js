import RssNewsCard from "./cards/RssNewsCard.vue";
import RssNewsSelectCard from "./cards/RssNewsSelectCard.vue";
import RssNewsStreamCard from "./cards/RssNewsStreamCard.vue";
import "../css/card.css";

Nova.booting((app) => {
    app.component("nova-card-rss-news", RssNewsCard);
    app.component("nova-card-rss-news-select", RssNewsSelectCard);
    app.component("nova-card-rss-news-stream", RssNewsStreamCard);
});
