"""Slide helpers for the defense deck, built on the LSPU institutional template (python-pptx 1.0.2).

The template keeps its photo strip and seal in each slide's own background, not in the layouts,
so every new slide gets a copy of the template content slide's background.
"""
import copy

from pptx import Presentation
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import MSO_ANCHOR, PP_ALIGN
from pptx.opc.constants import RELATIONSHIP_TYPE as RT
from pptx.oxml.ns import qn
from pptx.util import Emu, Inches, Pt

NAVY = RGBColor(0x1D, 0x37, 0x84)   # LSPU template title blue
BLUE = RGBColor(0x31, 0x53, 0x8F)
TINT = RGBColor(0xDD, 0xEA, 0xF6)   # template image-box tint
SOFT = RGBColor(0xF1, 0xF5, 0xFB)
PALE = RGBColor(0xFF, 0xF6, 0xCC)   # template content-box yellow, softened
INK = RGBColor(0x1F, 0x29, 0x37)
MUTED = RGBColor(0x4B, 0x55, 0x63)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
TITLE_FONT, BODY_FONT, MATH_FONT = "Century Gothic", "Calibri", "Cambria Math"
X0, X1, Y0, Y1 = 0.92, 12.42, 1.85, 6.95   # content area, inches
W = X1 - X0


class Deck:
    inch, pt = staticmethod(Inches), staticmethod(Pt)

    def __init__(self, template):
        self.prs = Presentation(template)
        slides = self.prs.slides
        content = slides[1]
        self.bg = copy.deepcopy(content._element.cSld.bg)
        rid = self.bg.find(".//" + qn("a:blip")).get(qn("r:embed"))
        self.bg_img = content.part.related_part(rid)
        self.title_slide, self.thanks = slides[0], slides[6]
        lst = slides._sldIdLst
        for sid in list(lst)[1:6]:   # drop the template's sample slides
            self.prs.part.drop_rel(sid.rId)
            lst.remove(sid)
        self.prs.part.rename_slide_parts([sid.rId for sid in lst])   # else new slides reuse slide7.xml
        self.n = 1

    def new(self, title):
        s = self.prs.slides.add_slide(self.prs.slide_layouts[5])   # TITLE_ONLY
        bg = copy.deepcopy(self.bg)
        bg.find(".//" + qn("a:blip")).set(qn("r:embed"), s.part.relate_to(self.bg_img, RT.IMAGE))
        s._element.cSld.insert(0, bg)
        t = s.shapes.title
        t.left, t.top, t.width, t.height = Inches(X0), Inches(0.72), Inches(W - 0.6), Inches(0.95)
        tf = t.text_frame
        tf.word_wrap, tf.vertical_anchor = True, MSO_ANCHOR.MIDDLE
        tf.text = title
        _font(tf.paragraphs[0].runs[0], 28, NAVY, True, TITLE_FONT)
        self.n += 1
        text(s, 12.15, 7.02, 0.6, 0.35, [str(self.n)], 12, MUTED, align=PP_ALIGN.RIGHT)
        return s

    def finish(self, path):
        lst = self.prs.slides._sldIdLst
        last = next(sid for sid in lst if self.prs.slides.get(int(sid.get("id"))) is self.thanks)
        lst.remove(last)
        lst.append(last)
        self.prs.part.rename_slide_parts([sid.rId for sid in lst])
        self.prs.save(path)


def _font(run, size, color=INK, bold=False, font=BODY_FONT, italic=False):
    f = run.font
    f.size, f.bold, f.italic, f.name = Pt(size), bold, italic, font
    f.color.rgb = color


def _runs(p, content, size, color, bold, font):
    """content: a string, or a list of (text, {bold, italic, color, size, font}) runs."""
    parts = [(content, {})] if isinstance(content, str) else content
    for txt, o in parts:
        r = p.add_run()
        r.text = txt
        _font(r, o.get("size", size), o.get("color", color), o.get("bold", bold), o.get("font", font), o.get("italic", False))


def _frame(shape, anchor=MSO_ANCHOR.TOP, margin=0.08):
    tf = shape.text_frame
    tf.word_wrap, tf.vertical_anchor = True, anchor
    tf.margin_left = tf.margin_right = Inches(margin)
    tf.margin_top = tf.margin_bottom = Inches(margin * 0.6)
    return tf


def text(slide, x, y, w, h, paras, size=18, color=INK, bold=False, align=PP_ALIGN.LEFT,
         anchor=MSO_ANCHOR.TOP, font=BODY_FONT, space=6, shape=None):
    """Paragraphs into a new text box (or into shape). Returns the shape."""
    sh = shape or slide.shapes.add_textbox(Inches(x), Inches(y), Inches(w), Inches(h))
    tf = _frame(sh, anchor)
    for i, para in enumerate(paras):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        p.alignment, p.space_after = align, Pt(space)
        _runs(p, para, size, color, bold, font)
    return sh


def bullets(slide, x, y, w, h, items, size=18, color=INK, space=8, shape=None, anchor=MSO_ANCHOR.TOP):
    """Bulleted list; an item is a string, a run list, or ("sub", item) for a second level."""
    sh = shape or slide.shapes.add_textbox(Inches(x), Inches(y), Inches(w), Inches(h))
    tf = _frame(sh, anchor)
    for i, it in enumerate(items):
        lvl = 1 if isinstance(it, tuple) and it[0] == "sub" else 0
        it = it[1] if lvl else it
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        p.space_after = Pt(space)
        ppr = p._p.get_or_add_pPr()
        ind = Inches(0.28 + 0.3 * lvl)
        ppr.set("marL", str(ind)); ppr.set("indent", str(-Inches(0.24)))
        bf = ppr.makeelement(qn("a:buFont"), {"typeface": "Arial"})
        bc = ppr.makeelement(qn("a:buChar"), {"char": "•" if lvl == 0 else "–"})
        ppr.append(bf); ppr.append(bc)
        _runs(p, it, size - 2 * lvl, color, False, BODY_FONT)
    return sh


def box(slide, x, y, w, h, fill=TINT, line=None, shape=MSO_SHAPE.ROUNDED_RECTANGLE, radius=0.06):
    sh = slide.shapes.add_shape(shape, Inches(x), Inches(y), Inches(w), Inches(h))
    sh.fill.solid(); sh.fill.fore_color.rgb = fill
    if line is None:
        sh.line.fill.background()
    else:
        sh.line.color.rgb = line; sh.line.width = Pt(1.25)
    if shape == MSO_SHAPE.ROUNDED_RECTANGLE:
        sh.adjustments[0] = min(0.5, radius / min(w, h))
    sh.shadow.inherit = False
    return sh


def card(slide, x, y, w, h, head, body, fill=TINT, size=18, head_size=20, items=False, head_color=NAVY):
    """Rounded tinted box: bold heading, then text paragraphs or bullets, all left-aligned."""
    sh = box(slide, x, y, w, h, fill)
    tf = _frame(sh, MSO_ANCHOR.TOP, 0.16)
    p = tf.paragraphs[0]
    p.space_after, p.alignment = Pt(6), PP_ALIGN.LEFT
    _runs(p, head, head_size, head_color, True, BODY_FONT)
    for it in body:
        q = tf.add_paragraph()
        q.space_after, q.alignment = Pt(6), PP_ALIGN.LEFT
        if items:
            ppr = q._p.get_or_add_pPr()
            ppr.set("marL", str(Inches(0.25))); ppr.set("indent", str(-Inches(0.2)))
            ppr.append(ppr.makeelement(qn("a:buFont"), {"typeface": "Arial"}))
            ppr.append(ppr.makeelement(qn("a:buChar"), {"char": "•"}))
        _runs(q, it, size, INK, False, BODY_FONT)
    return sh


def circle(slide, x, y, d, label, fill=NAVY, color=WHITE, size=16):
    sh = slide.shapes.add_shape(MSO_SHAPE.OVAL, Inches(x), Inches(y), Inches(d), Inches(d))
    sh.fill.solid(); sh.fill.fore_color.rgb = fill; sh.line.fill.background(); sh.shadow.inherit = False
    text(slide, 0, 0, 0, 0, [label], size, color, True, PP_ALIGN.CENTER, MSO_ANCHOR.MIDDLE, shape=sh, space=0)
    _frame(sh, MSO_ANCHOR.MIDDLE, 0.0)
    return sh


def arrow(slide, x, y, w, h, fill=BLUE):
    sh = slide.shapes.add_shape(MSO_SHAPE.RIGHT_ARROW, Inches(x), Inches(y), Inches(w), Inches(h))
    sh.fill.solid(); sh.fill.fore_color.rgb = fill; sh.line.fill.background(); sh.shadow.inherit = False
    return sh


def table(slide, x, y, w, rows, widths, size=14, row_h=0.42, head_fill=NAVY, zebra=True, bold_first=False):
    """Native table; rows[0] is the header. widths are relative."""
    n, m = len(rows), len(rows[0])
    gt = slide.shapes.add_table(n, m, Inches(x), Inches(y), Inches(w), Inches(row_h * n))
    t = gt.table
    total = sum(widths)
    for j, wj in enumerate(widths):
        t.columns[j].width = Emu(int(Inches(w) * wj / total))
    for i, row in enumerate(rows):
        t.rows[i].height = Inches(row_h)
        for j, val in enumerate(row):
            c = t.cell(i, j)
            c.margin_left = c.margin_right = Inches(0.07)
            c.margin_top = c.margin_bottom = Inches(0.03)
            c.vertical_anchor = MSO_ANCHOR.MIDDLE
            c.fill.solid()
            c.fill.fore_color.rgb = head_fill if i == 0 else (SOFT if zebra and i % 2 == 0 else WHITE)
            tf = c.text_frame
            tf.word_wrap = True
            p = tf.paragraphs[0]
            if i == 0:
                _runs(p, val, size, WHITE, True, BODY_FONT)
            else:
                _runs(p, val, size, INK, bold_first and j == 0, BODY_FONT)
    return gt


def png_size(path):
    with open(path, "rb") as f:
        h = f.read(24)
    return int.from_bytes(h[16:20], "big"), int.from_bytes(h[20:24], "big")


def picture(slide, path, x, y, w, h, align="center"):
    """Fit the picture inside the box, keeping its aspect ratio."""
    pw, ph = png_size(path)
    s = min(w / pw, h / ph)
    dw, dh = pw * s, ph * s
    dx = {"left": 0, "center": (w - dw) / 2, "right": w - dw}[align]
    pic = slide.shapes.add_picture(path, Inches(x + dx), Inches(y + (h - dh) / 2), Inches(dw), Inches(dh))
    pic.line.color.rgb = RGBColor(0xC9, 0xD3, 0xE3); pic.line.width = Pt(0.75)
    return pic


def notes(slide, words):
    slide.notes_slide.notes_text_frame.text = " ".join(words.split())


def source(slide, txt):
    """Citation line at the foot of the content area."""
    text(slide, X0, 6.62, W, 0.35, [txt], 12, MUTED)
