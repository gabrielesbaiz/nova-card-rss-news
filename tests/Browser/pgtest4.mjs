/* The home page demo: three card tabs x four layout tabs, code + result.
   It once shipped empty after an unrelated edit removed its IIFE — hence this. */
import fs from 'fs';
const html = fs.readFileSync(new URL('../../docs/index.html', import.meta.url), 'utf8');
const js = html.split('<script>')[2].split('</script>')[0];   // the home demo block

const mk = () => ({ innerHTML:'', textContent:'', dataset:{}, setAttribute(k,v){this[k]=v;},
  getAttribute(k){return this[k]??null;}, addEventListener(t,f){ (this._h ||= {})[t]=f; }, focus(){} });
const nodes = {};
const tabs = {
  '#cardTabs button': ['fixed','select','stream'].map(v => { const e = mk(); e.dataset.card = v; return e; }),
  '#layoutTabs button': ['hero','compact','grid','ticker'].map(v => { const e = mk(); e.dataset.layout = v; return e; }),
};
global.document = { getElementById:(id)=>(nodes[id] ||= mk()), querySelectorAll:(s)=>tabs[s]||[] };
eval(js);

// the code panel is HTML: unwrap tags and entities before asserting on it
const C = () => nodes.demoCode.innerHTML
  .replace(/<[^>]+>/g, '').replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&');
const P = () => nodes.demoPreview.innerHTML;
const checks = []; const t = (n, ok) => checks.push([n, ok]);

t('code renders on load (not empty)', C().trim().length > 40);
t('preview renders on load (not empty)', P().includes('class="ph"') && P().includes('class="pb"'));
t('default is RssNewsCard, hero', C().includes('RssNewsCard') && C().includes("'hero'") && P().includes('Featured'));
t('caption and combo label are filled', nodes.codeCap.textContent === 'RssNewsCard' && nodes.comboName.textContent.includes('hero'));
t('note is filled', nodes.demoNote.innerHTML.includes('source()'));

tabs['#layoutTabs button'][1]._h.click();
t('compact drops the hero block', !P().includes('Featured') && P().includes('<ul>'));
tabs['#layoutTabs button'][2]._h.click();
t('grid renders cards', P().includes('grid4') && P().includes('thumb'));
tabs['#layoutTabs button'][3]._h.click();
t('ticker renders one line', P().includes('class="ticker"'));
tabs['#layoutTabs button'][0]._h.click();

tabs['#cardTabs button'][1]._h.click();
t('select card switches the code', C().includes('defaultSource') && C().includes('RssNewsSelectCard'));
tabs['#cardTabs button'][2]._h.click();
t('stream card badges its sources', P().includes('class="badge"') && C().includes('->sources(['));
t('stream card shows the search box', P().includes('class="search"'));
tabs['#cardTabs button'][0]._h.click();
t('back to the fixed card', C().includes("->source('laravel_news')"));

t('layout name is reflected in the code', (() => {
  tabs['#layoutTabs button'][2]._h.click();
  const ok = C().includes("'grid'");
  tabs['#layoutTabs button'][0]._h.click();
  return ok;
})());

let bad = 0;
for (const [n, ok] of checks) { if (!ok) bad++; console.log((ok ? '  ok  ' : '  FAIL') + '  ' + n); }
console.log(bad ? `\n${bad} failing` : `\nall ${checks.length} home-demo checks pass`);
process.exit(bad ? 1 : 0);
