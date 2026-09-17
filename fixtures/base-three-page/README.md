# Base fixture — three pages

Current workflow: `npm run build`, `npm run package -- base-three-page`, then follow
[`delivery-operations.md`](../../delivery-operations.md). The fixture now includes a
small dynamic block to exercise compiled editor JS, frontend CSS, image IDs and page
IDs. It remains internal and cannot be published in the catalogue. The older manual
setup below documents the original theme-only audit; do not bypass the importer when
testing a release. Current results are in [`delivery-verification.md`](../../delivery-verification.md).

**This is not a sellable template and must never reach the catalogue.** It is the smallest
site that can fail if the customer theme base is wrong: Home, About, Contact, a header, a
footer, one image and a menu.

It lives in `fixtures/`, not `plugin/src/templates/`, on purpose — block discovery and the
demo route scan `plugin/src/templates/*`, and a fixture appearing in the catalogue or in
the block inserter is exactly the accident this placement prevents.

## What it is for

Each delivery step gets proved against this before a real design is built on top of it
(customer-runtime.md §0):

| Step | What this fixture proves |
|---|---|
| 1. Theme base | Pages render through the shell. Tokens resolve. One H1 per page. **← this branch** |
| 2. Runtime plugin | Site Settings writes the values the shell reads. Site Owner can edit them. |
| 3. Packager | Theme and plugin ZIPs build from this, with only the assets it needs. |
| 4. Importer | The bundle installs on clean WordPress: pages, media, navigation, settings. |
| 5. Demo routes | The marketing-site preview matches the installed site. |

## Why the page files contain HTML, when a real template's must not

`how-to-work.md` §2.1a says a page file containing a `<div>`, a `<section>` or an `<h1>` is
wrong. **These files contain all three, and are still correct.** The distinction:

- Our blocks are server-rendered (`save: () => null`), so they store **no markup** — a page
  built from them is block comments and nothing else. That is what the rule polices.
- **Core blocks save markup.** `core/group`, `core/heading` and `core/list` legitimately
  store their own HTML, inside their own block comments.

This fixture combines core blocks with one generated-runtime probe. Every saved HTML
element sits inside its core block's comment delimiters — none
of it is hand-written HTML, which is what the rule actually forbids.

A real template is built from Foundations blocks and so has no markup at all. **Do not cite
this fixture as precedent for writing HTML into a sellable template.**

The rule that actually governs both, now in `how-to-work.md` §2.1a:

> Content must parse into supported blocks and survive **save and reload in the editor with
> no validation error**. Raw HTML outside a block's own delimiters is never allowed.

"No `<div>`" was never sufficient in either direction. The first version of this fixture
contained no forbidden element and was still wrong three ways — a `data-*` attribute on a
`core/image`, bare `<li>` outside `wp:list-item`, and an `id`/class on a group the block
comment never declared. `npm run validate:blocks` catches those. Only the editor catches
the rest.

## Import tokens

Page files and `settings.json` never contain a real URL, a real attachment id or a real
page id. Blocks store attachment ids and core blocks store URLs, and neither survives a
move between sites — that is why an install with no media handling renders every image
broken (customer-runtime.md §6). So references are written as tokens and the importer
substitutes them after it has created the pages and sideloaded the files:

| Token | Becomes |
|---|---|
| `{{media:<filename>\|url}}` | the sideloaded attachment's URL |
| `{{media:<filename>\|id}}` | its attachment id |
| `{{page:<slug>\|url}}` | that page's permalink |
| `{{page:<slug>\|id}}` | that page's post id |

The importer resolves these in a **second pass**, after every page exists — a page linked
from the homepage has to have been created before the link can be resolved.

**Inside a block comment's attribute JSON the token is quoted**, because a bare `{{…}}` is
not valid JSON and the block would be unreadable to the editor, the parser and the lint:

```
<!-- wp:image {"id":"{{media:fixture-logo.png|id}}","sizeSlug":"large"} -->
```

The importer replaces the **quoted string including its quotes** with a bare integer.
Substituting inside the quotes leaves `"id":"8"` — a string where the block expects an
integer.

The About page's image uses this canonical id-based form deliberately. An earlier version
carried only a URL: it rendered, and it validated, and it exercised **none** of the
attachment-id remapping that the importer has to get right. A fixture that passes without
testing the risky path is worse than no fixture.

**Status:** the importer consumes these tokens. Use the generated content ZIP through Tools > Foundations Delivery; do not substitute IDs by hand.

## Checking it by hand, before the importer exists

There is no automated install on this branch. The manual path:

1. Clean WordPress, activate **Foundations Customer Base**.
2. Create three pages — Home, About, Contact — matching the slugs in `manifest.json`.
3. For each, open the **Code editor** (`Ctrl+Shift+Alt+M`), paste that page's file, switch
   back to Visual, publish. Strip the `{{…}}` tokens by hand, or leave them visible — they
   are meant to be obvious.
4. **Settings → Reading** → static front page → Home.
5. Site Settings does not exist yet (step 2), so write the option directly:

   ```bash
   wp option patch insert fm_site_settings site_name "Foundations Fixture"
   wp option patch insert fm_site_settings phone "0117 000 0000"
   # navigation rows need real page ids:
   wp option patch insert fm_site_settings nav_primary --format=json \
     '[{"label":"Home","page":12},{"label":"About","page":14},{"label":"Contact","page":16}]'
   ```

6. Work through the acceptance checks in `theme-customer-base/README.md`.

7. **Open each page in the editor, save, and reload.** No validation warnings, no recovery
   prompts. This is the check the lint cannot do for you.
