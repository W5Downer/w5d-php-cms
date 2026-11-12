# Design system and contrast proof

Status: the palette and rules below are implemented in `assets/_base/css/attention.css`.
Contrast figures are computed with the WCAG 2.x relative-luminance formula. Recompute them with
the snippet at the end of this file whenever a gradient stop changes.

## Tokens

| Token | Use | Text colour on it |
|---|---|---|
| `--g-calm` | F1–F2 background, hero | white (scrim) |
| `--g-warm` | F3 callout | ink |
| `--g-focus` | F4 secondary | white (scrim) |
| `--g-peak` | F5 primary action, one per section | white (scrim) |
| `--g-cultures` … `--g-technology` | macro-work accents | white (scrim) |

## Scrim rule

Every block that places white text on a gradient sits on a 45% black overlay
(`--scrim: linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45))`). Without the scrim, several stops fail
4.5:1 (the worst raw stop is 2.4190955674182155:1 for the gold and green stops). With the scrim every stop
passes. `--g-warm` is never used with white text; it is paired only with ink.

## Computed ratios (WCAG AA body text needs 4.5:1)

| Token | Stops | Text | Ratio | Result |
|---|---|---|---|---|
| `--g-calm` | #0E3B5C → #1B6F8A | white text, raw | 5.70:1 | pass |
| `--g-calm` + scrim | #082033 → #0F3D4C | white text, with 45% scrim | 11.72:1 | pass |
| `--g-warm` | #F7EEDD → #E9C37A | ink `#1D2328` text | 9.49:1 | pass |
| `--g-focus` | #1B6F8A → #2BB3A3 | white text, raw | 2.60:1 | FAIL, scrim required |
| `--g-focus` + scrim | #0F3D4C → #18625A | white text, with 45% scrim | 7.16:1 | pass |
| `--g-peak` | #C8102E → #F05A28 | white text, raw | 3.39:1 | FAIL, scrim required |
| `--g-peak` + scrim | #6E0919 → #843216 | white text, with 45% scrim | 8.58:1 | pass |
| `--g-cultures` | #B5532E → #E39B5B | white text, raw | 2.31:1 | FAIL, scrim required |
| `--g-cultures` + scrim | #642E19 → #7D5532 | white text, with 45% scrim | 6.53:1 | pass |
| `--g-built` | #5E6B3A → #B8A36A | white text, raw | 2.47:1 | FAIL, scrim required |
| `--g-built` + scrim | #343B20 → #655A3A | white text, with 45% scrim | 6.83:1 | pass |
| `--g-sciences` | #2B2F7A → #5A6CF0 | white text, raw | 4.34:1 | FAIL, scrim required |
| `--g-sciences` + scrim | #181A43 → #323B84 | white text, with 45% scrim | 10.03:1 | pass |
| `--g-governance` | #0F4D3A → #C9A227 | white text, raw | 2.42:1 | FAIL, scrim required |
| `--g-governance` + scrim | #082A20 → #6F5915 | white text, with 45% scrim | 6.74:1 | pass |
| `--g-technology` | #0B7FA6 → #7B4DE0 | white text, raw | 4.56:1 | pass |
| `--g-technology` + scrim | #06465B → #442A7B | white text, with 45% scrim | 10.32:1 | pass |

Result: every gradient token meets 4.5:1 for its intended text colour, with the scrim applied.

## Reading tiers and focus levels

- Tiers `E`, `I`, `X` are declared on every block (`tier` is required; `View::validate` throws otherwise).
- Focus `1`–`5` is declared on every block. `F5` is the single main action; `bin/lint.php` and
  `tests/RouteMatrixTest.php` check that no `<section>` holds more than one `.btn-peak`.

## Recompute

```python
def lum(h):
    h=h.lstrip('#'); c=[int(h[i:i+2],16)/255 for i in (0,2,4)]
    c=[x/12.92 if x<=0.03928 else ((x+0.055)/1.055)**2.4 for x in c]
    return 0.2126*c[0]+0.7152*c[1]+0.0722*c[2]
def cr(a,b):
    la,lb=sorted([lum(a),lum(b)],reverse=True); return (la+0.05)/(lb+0.05)
```
