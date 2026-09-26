# Documentation playground tests

The playground on the documentation site (`docs/index.html`, route `#/play`) is a
faithful re-implementation of the card in the browser, over fixture feeds. These
scripts run its logic in Node against a DOM stub, so a change to the page cannot
silently break the demo.

```bash
node tests/Browser/pgtest.mjs    # renders, sorts, limits, no external assets
node tests/Browser/pgtest2.mjs   # every control: cards, layouts, toggles, states
node tests/Browser/pgtest3.mjs   # search, read state, bookmarks, refresh, retry
```

They read the published HTML directly — no build step, no dependencies.
