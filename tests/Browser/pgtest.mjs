import fs from 'fs';
const html = fs.readFileSync('/Users/gabriele/Coding/gabrielesbaiz/nova-card-rss-news/docs/index.html','utf8');
const js = html.split('<script>').pop().split('</script>')[0];

// minimal DOM so the IIFE runs
const store = {};
const mk = () => ({ innerHTML:'', textContent:'', checked:false, value:'5', hidden:false, dataset:{},
  style:{}, classList:{add(){},remove(){},toggle(){}}, appendChild(){}, setAttribute(){}, getAttribute:()=>null,
  addEventListener(t,f){ (this._h ||= {})[t]=f; }, querySelectorAll:()=>[], querySelector:()=>null,
  focus(){}, setSelectionRange(){}, closest:()=>null, insertAdjacentElement(){} });
const nodes = {};
global.document = {
  hidden:false,
  getElementById:(id)=> (nodes[id] ||= mk()),
  querySelectorAll:()=>[],
  querySelector:()=>null,
  createElement:()=>mk(),
  addEventListener(){},
};
global.localStorage = { getItem:k=>store[k]??null, setItem:(k,v)=>store[k]=v };
Object.defineProperty(globalThis,'navigator',{value:{clipboard:{writeText:async()=>{}},platform:'MacIntel'},configurable:true});
global.setInterval = () => 0; global.clearInterval = () => {}; global.setTimeout = (f)=>{ f(); return 0; };
global.matchMedia = () => ({ matches:false, addEventListener(){} });
global.addEventListener = () => {};

eval(js);

const card = nodes.pgCardHost.innerHTML;
const code = nodes.pgCode.innerHTML.replace(/<[^>]+>/g,'');
const readout = nodes.pgReadout.innerHTML.replace(/<[^>]+>/g,' ');

const checks = [
  ['card rendered', card.includes('rss-head') && card.includes('rss-body')],
  ['hero layout default', card.includes('rss-hero') && card.includes('Featured')],
  ['limit respected (5 = 1 hero + 4 rows)', (card.match(/rss-item/g)||[]).length === 4],
  ['newest first', card.indexOf('rewritten queue worker') < card.indexOf('Eloquent strictness')],
  ['thumbnails are inline svg', card.includes('data:image/svg+xml')],
  ['no external asset refs', !/src="https?:/.test(card)],
  ['unread badge present', card.includes('rss-new')],
  ['live dot when fresh', card.includes('rss-live')],
  ['code mirrors config', code.includes("RssNewsCard::make()") && code.includes("->source('laravel_news')") && code.includes("->layout('hero')") && code.includes("->limit(5)")],
  ['readout shows cache state', readout.includes('01') && readout.includes('fresh')],
];
let bad = 0;
for (const [name, ok] of checks) { if (!ok) bad++; console.log((ok?'  ok  ':'  FAIL') + '  ' + name); }
console.log(bad ? `\n${bad} failing` : '\nall playground checks pass');
process.exit(bad?1:0);
