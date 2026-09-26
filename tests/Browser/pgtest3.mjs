import fs from 'fs';
const html = fs.readFileSync('/Users/gabriele/Coding/gabrielesbaiz/nova-card-rss-news/docs/index.html','utf8');
const js = html.split('<script>').pop().split('</script>')[0];
const store = {};
// A DOM stub that re-parses innerHTML enough to find [data-id] / [data-save] handlers.
class El {
  constructor(id){ this.id=id; this.innerHTML=''; this.textContent=''; this.checked=false; this.value='5';
    this.hidden=false; this.dataset={}; this.style={}; this._h={}; this.children=[]; }
  addEventListener(t,f){ this._h[t]=f; }
  setAttribute(k,v){ this[k]=v; } getAttribute(k){ return this[k]??null; }
  appendChild(c){ this.children.push(c); } insertAdjacentElement(){} focus(){} setSelectionRange(){}
  closest(){ return null; }
  querySelectorAll(sel){
    // Stubs are cached per key so the handlers the engine attaches survive.
    this._reg ||= new Map();
    const attr = sel.includes('data-save') ? 'data-save' : 'data-id';
    const re = new RegExp(attr + '="([^"]+)"','g');
    const out = []; const seen = new Set(); let m;
    while ((m = re.exec(this.innerHTML))) {
      if (seen.has(m[1])) continue; seen.add(m[1]);
      const key = attr + ':' + m[1];
      let e = this._reg.get(key);
      if (!e) { e = new El('x'); e.dataset = attr === 'data-save' ? { save:m[1] } : { id:m[1] }; this._reg.set(key, e); }
      out.push(e);
    }
    return out;
  }
  querySelector(){ return null; }
}
const nodes = {};
const segs = {
  '#pgCard button':   ['fixed','select','stream'].map(v=>({dataset:{card:v},setAttribute(){},addEventListener(t,f){this._c=f}})),
  '#pgLayout button': ['hero','compact','grid','ticker'].map(v=>({dataset:{layout:v},setAttribute(){},addEventListener(t,f){this._c=f}})),
  '#pgWidth button':  ['1/3','1/2','full'].map(v=>({dataset:{width:v},setAttribute(){},addEventListener(t,f){this._c=f}})),
  '#pgState button':  ['fresh','stale','down'].map(v=>({dataset:{state:v},setAttribute(){},addEventListener(t,f){this._c=f}})),
};
global.document = { hidden:false, getElementById:(id)=>(nodes[id] ||= new El(id)),
  querySelectorAll:(s)=>segs[s]||[], querySelector:()=>null, createElement:()=>new El('n'), addEventListener(){} };
global.localStorage = { getItem:k=>store[k]??null, setItem:(k,v)=>store[k]=v };
Object.defineProperty(globalThis,'navigator',{value:{clipboard:{writeText:async()=>{}},platform:'Mac'},configurable:true});
let timers=[]; global.setInterval=()=>0; global.clearInterval=()=>{}; global.setTimeout=(f)=>{timers.push(f);return 0;};
global.matchMedia=()=>({matches:false,addEventListener(){}}); global.addEventListener=()=>{};
eval(js);

const host = nodes.pgCardHost;
const H = () => host.innerHTML;
const checks=[]; const t=(n,ok)=>checks.push([n,ok]);
const idsIn = () => [...H().matchAll(/data-id="([^"]+)"/g)].map(m=>m[1]);

// --- read state ---
const before = (H().match(/ read"/g)||[]).length;
const first = idsIn()[0];
host.querySelectorAll('[data-id]').find(e=>e.dataset.id===first)._h.click({target:{closest:()=>null}});
t('clicking an item marks it read', (H().match(/ read"/g)||[]).length === before+1);
t('read state persisted to localStorage', (store['nova-rss-play:seen']||'').includes(first));
const badge = H().match(/rss-new">(\d+) new/);
t('unread badge decrements', badge && Number(badge[1]) === 4);

// --- bookmarks ---
const saveBtn = host.querySelectorAll('[data-save]')[0];
saveBtn._h.click({ stopPropagation(){} });
t('bookmark toggles on', H().includes('aria-pressed="true"') && (store['nova-rss-play:saved']||'').length > 4);

// --- search ---
nodes.pgSearch.checked = true; nodes.pgSearch._h.change();
const input = nodes.pgSearchInput;
input.value = 'queue'; input._h.input();
t('search filters the list', /queue/i.test(H()) && !/Eloquent strictness/.test(H()));
t('matches are highlighted', H().includes('<mark>'));
input.value = 'zzzzz'; input._h.input();
t('no results state', H().includes('No results'));
input.value = ''; input._h.input();
t('clearing the term restores the list', /Eloquent strictness/.test(H()));

// --- refresh ---
const beforeTitles = idsIn().length;
nodes.pgRefresh._h.click();
timers.forEach(f=>f()); timers=[];
t('refresh adds a fresh item at the top', /Breaking|Just in|Filed moments ago/.test(H()));
t('refresh resets the timestamp', H().includes('updated just now'));

// --- cache state + retry ---
segs['#pgState button'][2]._c();
t('cold failure hides the list', !H().includes('rss-item') && H().includes('rss-error'));
nodes.pgRetry._h.click(); timers.forEach(f=>f()); timers=[];
t('retry recovers to a rendered list', H().includes('rss-body') && !H().includes('rss-error'));

// --- select card switches source ---
segs['#pgCard button'][1]._c();
nodes.pgSourceSelect.value = 'the_verge'; nodes.pgSourceSelect._h.change();
timers.forEach(f=>f()); timers=[];
t('picker switches the feed', H().includes('Chip maker posts record quarter'));
t('selection persisted', (store['nova-rss-play:source']||'').includes('the_verge'));

let bad=0; for(const [n,ok] of checks){ if(!ok) bad++; console.log((ok?'  ok  ':'  FAIL')+'  '+n); }
console.log(bad?`\n${bad} failing`:`\nall ${checks.length} behaviour checks pass`);
process.exit(bad?1:0);
