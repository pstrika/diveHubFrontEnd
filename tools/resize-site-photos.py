"""Make web sized copies of the dive site photo library.

Originals in public/assets/img/sites are camera files, many over 10 MB, and
the pages used to serve them at full size. This writes two WebP copies per
photo, leaving the originals untouched:

    public/assets/img/sites/web/<name>.webp        max 1600 px wide, galleries and heroes
    public/assets/img/sites/web/thumb/<name>.webp  max 480 px wide, cards and lists

App\Support\SitePhoto builds URLs for these and falls back to the original
when a copy is missing, so running this script is never required for the
site to work, only for it to be fast.

Idempotent: existing copies newer than their original are skipped.
Requires Python 3 with Pillow (pip install pillow).

Run from the repo root:  python tools/resize-site-photos.py
"""
import sys
from pathlib import Path

try:
    from PIL import Image, ImageOps
except ImportError:
    sys.exit("Pillow is required: pip install pillow")

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "public" / "assets" / "img" / "sites"
SIZES = {"web": 1600, "web/thumb": 480}
EXTS = {".jpg", ".jpeg", ".png", ".webp", ".jfif"}


def convert(src: Path, dest: Path, max_width: int) -> bool:
    if dest.exists() and dest.stat().st_mtime >= src.stat().st_mtime:
        return False
    with Image.open(src) as im:
        im = ImageOps.exif_transpose(im)  # honour camera orientation
        if im.mode not in ("RGB", "RGBA"):
            im = im.convert("RGB")
        if im.width > max_width:
            im = im.resize((max_width, round(im.height * max_width / im.width)), Image.LANCZOS)
        dest.parent.mkdir(parents=True, exist_ok=True)
        im.save(dest, "WEBP", quality=80, method=6)
    return True


def main():
    files = [p for p in SRC.iterdir() if p.is_file() and p.suffix.lower() in EXTS]
    made = skipped = failed = 0
    for i, src in enumerate(sorted(files), 1):
        for sub, width in SIZES.items():
            dest = SRC / sub / (src.stem + ".webp")
            try:
                if convert(src, dest, width):
                    made += 1
                else:
                    skipped += 1
            except Exception as e:  # keep going; one bad file should not stop the batch
                failed += 1
                print(f"FAILED {src.name}: {e}", file=sys.stderr)
        if i % 100 == 0:
            print(f"{i}/{len(files)} photos processed", file=sys.stderr)
    total = sum(p.stat().st_size for p in (SRC / "web").rglob("*.webp"))
    print(f"done: {made} written, {skipped} up to date, {failed} failed, web copies total {total/1e6:.0f} MB")


if __name__ == "__main__":
    main()
