"""Builds the proposal defense deck from the LSPU template.

Run from this folder:
    uv run --with python-pptx==1.0.2 python build_deck.py
Content comes from the accepted paper (panel-review/2026-11-08/preview-all-changes-accepted.pdf);
figures from panel-review/2026-11-08/figures and assets/.
"""
import os

from pptx.util import Pt

import s1_intro
import s2_frame
import s3_method
import s4_system
from lib import Deck, notes

HERE = os.path.dirname(os.path.abspath(__file__))
TEMPLATE = os.path.join(HERE, "..", "guides", "LSPU-Institutional-Slide-Copy.pptx")
OUT = os.path.join(HERE, "DisasterResource+ Proposal Defense.pptx")

d = Deck(TEMPLATE)
for part in (s1_intro, s2_frame, s3_method, s4_system):
    part.add(d)
notes(d.thanks, """Thank you. I am ready for the panel's questions.""")
d.finish(OUT)

# Append each slide's transition line from the Presentation Guide to its speaker notes.
import re

from pptx import Presentation

guide = open(os.path.join(HERE, "..", "presentation-guide.md"), encoding="utf-8").read()
trans = {int(n): t.strip().strip('"“”') for n, t in
         re.findall(r"^### Slide (\d+) [^\n]*\n(?:(?!^### Slide ).*\n)*?\*\*Transition:\*\* ([^\n]+)", guide, re.M)}
prs = Presentation(OUT)
for n, slide in enumerate(prs.slides, 1):
    if n in trans:
        tf = slide.notes_slide.notes_text_frame
        tf.text = tf.text.rstrip() + "\n\nTransition: " + trans[n]
prs.save(OUT)
print("slides:", len(prs.slides), "| transitions added:", len(trans), "->", OUT)
