import fs from 'fs';
const html = fs.readFileSync('/Users/gabriele/Coding/gabrielesbaiz/nova-card-rss-news/docs/index.html','utf8');
const js = html.split('<script>').pop().split('</script>')[0];
const store = {};
const handlers = [];
const mk = (id) => ({ id, innerHTML:'', textContent:'', checked:false, value:'5', hidden:false, dataset:{},
  style:{}, classList:{add(){},remove(){},toggle(){}}, appendChild(){}, setAttribute(){}, getAttribute:()=>null,
  addEventListener(t,f){ handlers.push({id,t,f}); (this._h ||= {})[t]=f; },
  querySelectorAll:()=>[], querySelector:()=>null, focus(){}, setSelectionRange(){}, closest:()=>null, insertAdjacentElement(){} });
const nodes = {};
// segmented buttons the engine queries by selector
const segs = {
  '#pgCard button':   ['fixed','select','stream'].map(v => ({dataset:{card:v}, setAttribute(){}, addEventListener(t,f){this._c=f}})),
  '#pgLayout button': ['hero','compact','grid','ticker'].map(v => ({dataset:{layout:v}, setAttribute(){}, addEventListener(t,f){this._c=f}})),
  '#pgWidth button':  ['1/3','1/2','full'].map(v => ({dataset:{width:v}, setAttribute(){}, addEventListener(t,f){this._c=f}})),
  '#pgState button':  ['fresh','stale','down'].map(v => ({dataset:{state:v}, setAttribute(){}, addEventListener(t,f){this._c=f}})),
};
global.document = { hidden:false, getElementById:(id)=>(nodes[id] ||= mk(id)),
  querySelectorAll:(sel)=>segs[sel]||[], querySelector:()=>null, createElement:()=>mk('new'), addEventListener(){} };
global.localStorage = { getItem:k=>store[k]??null, setItem:(k,v)=>store[k]=v };
Object.defineProperty(globalThis,'navigator',{value:{clipboard:{writeText:async()=>{}},platform:'Mac'},configurable:true});
global.setInterval=()=>0; global.clearInterval=()=>{}; global.setTimeout=(f)=>{f();return 0;};
global.matchMedia=()=>({matches:false,addEventListener(){}}); global.addEventListener=()=>{};
eval(js);

const html_ = () => nodes.pgCardHost.innerHTML;
const code_ = () => nodes.pgCode.innerHTML.replace(/<[^>]+>/g,'');
const click = (sel, i) => segs[sel][i]._c();
const checks = [];
const t = (n, ok) => checks.push([n, ok]);

// layouts
click('#pgLayout button',1); t('compact layout: no hero', !html_().includes('rss-hero') && html_().includes('rss-list'));
click('#pgLayout button',2); t('grid layout', html_().includes('rss-grid') && html_().includes('rss-card'));
click('#pgLayout button',3); t('ticker layout', html_().includes('rss-ticker'));
click('#pgLayout button',0);

// card types
click('#pgCard button',1);
t('select card renders a picker', html_().includes('<select class="rss-select"') && html_().includes('optgroup'));
t('select code uses defaultSource', code_().includes('defaultSource'));
click('#pgCard button',2);
t('stream card badges sources', (html_().match(/class="badge"/g)||[]).length >= 2);
t('stream merges several feeds', new Set([...html_().matchAll(/class="badge">([^<]+)</g)].map(m=>m[1])).size > 1);
t('stream code uses sources([...])', code_().includes("->sources(['bbc_world', 'the_verge', 'laravel_news'])"));
click('#pgCard button',0);

// cache states
click('#pgState button',1); t('stale banner', html_().includes('Showing cached copy'));
click('#pgState button',2); t('cold failure shows error + retry', html_().includes('rss-error') && html_().includes('Retry'));
t('no live dot while down', !html_().includes('rss-live'));
click('#pgState button',0); t('back to fresh', !html_().includes('rss-error') && html_().includes('rss-live'));

// widths
click('#pgWidth button',0); t('width 1/3', html_().includes('w-third'));
click('#pgWidth button',2); t('width full has no clamp', !html_().includes('w-third') && !html_().includes('w-half'));
click('#pgWidth button',1);

// toggles
const toggle = (id, v) => { nodes[id].checked = v; nodes[id]._h.change(); };
toggle('pgImages', false); t('images off removes thumbnails', !html_().includes('data:image/svg+xml'));
t('images(false) in code', code_().includes('->images(false)'));
toggle('pgImages', true);
toggle('pgSearch', true); t('search box opens', html_().includes('rss-search') && html_().includes('Search news'));
t('searchable() in code', code_().includes('->searchable()'));
toggle('pgRead', false); t('readState(false) hides save buttons', !html_().includes('rss-save'));
t('readState(false) in code', code_().includes('->readState(false)'));
toggle('pgRead', true);
toggle('pgAuto', true); t('autoRefresh(10) in code', code_().includes('->autoRefresh(10)'));
toggle('pgAuto', false);
toggle('pgFav', true); t('favicon svg when enabled', html_().includes('rss-ico') && html_().includes('data:image/svg+xml'));
toggle('pgFav', false);

// limit
nodes.pgLimit.value = '2'; nodes.pgLimit._h.input();
t('limit 2 → hero + 1 row', (html_().match(/rss-item/g)||[]).length === 1);
t('limit in code', code_().includes('->limit(2)'));
nodes.pgLimit.value = '8'; nodes.pgLimit._h.input();
t('limit 8 → hero + 7 rows', (html_().match(/rss-item/g)||[]).length === 7);

let bad = 0;
for (const [n, ok] of checks) { if (!ok) bad++; console.log((ok?'  ok  ':'  FAIL') + '  ' + n); }
console.log(bad ? `\n${bad} failing` : `\nall ${checks.length} interaction checks pass`);
process.exit(bad?1:0);
